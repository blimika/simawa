<?php

namespace App\Services;

use DOMDocument;
use Shuchkin\SimpleXLS;
use Shuchkin\SimpleXLSX;

class ExcelSpreadsheetService
{
    /**
     * Membaca baris data (2D array) dari file .xls (atau .xlsx / .csv jika $strictXlsOnly = false)
     *
     * @return array<int, array<int, string>>
     */
    public function parseRows(string $filePath, ?string $extension = null, bool $strictXlsOnly = false): array
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException('File tidak dapat dibaca.');
        }

        $rawHeader = file_get_contents($filePath, false, null, 0, 512) ?: '';
        $ext = strtolower((string) $extension);

        // 1. Deteksi file Excel biner OLE2 (.xls)
        if (str_starts_with($rawHeader, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1")) {
            $xls = SimpleXLS::parseFile($filePath);
            if ($xls) {
                return $this->normalizeRows($this->extractXlsRows($xls));
            }
            throw new \RuntimeException('Gagal membaca file Excel .xls: ' . (SimpleXLS::parseError() ?: 'Format tidak valid'));
        }

        // 2. Deteksi file Excel OOXML / ZIP (.xlsx)
        if (str_starts_with($rawHeader, "PK\x03\x04")) {
            if ($strictXlsOnly) {
                throw new \RuntimeException('File berformat .xlsx. Harap gunakan format Excel .xls saja.');
            }
            $xlsx = SimpleXLSX::parseFile($filePath);
            if ($xlsx) {
                return $this->normalizeRows($xlsx->rows());
            }
            throw new \RuntimeException('Gagal membaca file Excel .xlsx: ' . (SimpleXLSX::parseError() ?: 'Format tidak valid'));
        }

        $trimmedHead = ltrim($rawHeader, "\xEF\xBB\xBF \t\r\n");

        // 3. Deteksi Excel XML 2003 (SpreadsheetML disimpan dengan ekstensi .xls)
        if (str_starts_with($trimmedHead, '<?xml') || stripos($trimmedHead, '<Workbook') !== false) {
            $rows = $this->parseSpreadsheetMl($filePath);
            if (!empty($rows)) {
                return $this->normalizeRows($rows);
            }
        }

        // 4. Deteksi HTML Table (.xls hasil export HTML)
        if (stripos($trimmedHead, '<html') !== false || stripos($trimmedHead, '<table') !== false || stripos($trimmedHead, '<!doctype') !== false) {
            $rows = $this->parseHtmlTable($filePath);
            if (!empty($rows)) {
                return $this->normalizeRows($rows);
            }
        }

        if ($strictXlsOnly) {
            throw new \RuntimeException('Format file bukan .xls yang valid. Silakan unduh dan gunakan Template XLS.');
        }

        // 5. Jika ekstensi eksplisit .xlsx tapi bukan format Excel yang valid
        if ($ext === 'xlsx') {
            $xlsx = SimpleXLSX::parseFile($filePath);
            if ($xlsx) {
                return $this->normalizeRows($xlsx->rows());
            }
            throw new \RuntimeException('Format file .xlsx tidak valid atau rusak.');
        }

        // 6. Fallback CSV / TSV (mendukung koma, titik koma, dan tab, serta BOM UTF-8)
        return $this->normalizeRows($this->parseDelimitedText($filePath));
    }

    /**
     * Ekstrak baris dari SimpleXLS, mencegah angka panjang (mis. nomor telepon) berubah ke notasi ilmiah E+
     *
     * @return array<int, array<int, string>>
     */
    private function extractXlsRows(SimpleXLS $xls): array
    {
        $rows = [];
        foreach ($xls->rowsEx() as $row) {
            $line = [];
            foreach ($row as $cell) {
                $val = (string) ($cell['value'] ?? '');
                $raw = $cell['raw'] ?? null;
                if (is_numeric($raw) && (stripos($val, 'E+') !== false || ($val === '' && $raw !== ''))) {
                    $val = number_format((float) $raw, 0, '', '');
                }
                $line[] = $val;
            }
            $rows[] = $line;
        }
        return $rows;
    }

    /**
     * Menghasilkan file Excel BIFF8 (.xls) biner asli (OLE2 Compound Document)
     * agar terbuka rapi di Microsoft Excel tanpa peringatan dan dapat diimpor kembali langsung.
     *
     * @param array<int, array<int, string|int|float|null>> $rows
     */
    public function generateBiff8Xls(array $rows, string $sheetName = 'Agen'): string
    {
        $cleanSheetName = preg_replace('/[:\\\\\/?*\[\]]/', '', $sheetName) ?: 'Sheet1';
        $cleanSheetName = mb_substr($cleanSheetName, 0, 31, 'UTF-8');

        // Kumpulkan string unik ke Shared String Table (SST)
        $sstStrings = [];
        $sstIndexMap = [];
        $totalStrings = 0;

        foreach ($rows as $row) {
            foreach ($row as $cell) {
                $val = (string) ($cell ?? '');
                if ($val === '') {
                    continue;
                }
                $totalStrings++;
                if (!array_key_exists($val, $sstIndexMap)) {
                    $sstIndexMap[$val] = count($sstStrings);
                    $sstStrings[] = $val;
                }
            }
        }

        // 1. Susun Worksheet Substream dulu untuk mengetahui panjang Workbook Globals -> offset BOUNDSHEET
        $wsBof = $this->biffRecord(0x0809, pack('vvvvVV', 0x0600, 0x0010, 0x0DBB, 0x07CC, 0x00000000, 0x00000006));
        $maxRow = count($rows);
        $maxCol = 0;
        foreach ($rows as $r) {
            $maxCol = max($maxCol, count($r));
        }
        $wsDim = $this->biffRecord(0x0200, pack('VVvvv', 0, $maxRow, 0, $maxCol, 0));

        $wsCells = '';
        foreach (array_values($rows) as $rIdx => $row) {
            foreach (array_values($row) as $cIdx => $cell) {
                $val = (string) ($cell ?? '');
                if ($val === '') {
                    continue;
                }
                $sstIdx = $sstIndexMap[$val];
                // LABELSST (0x00FD): row(v), col(v), xf(v), sstIndex(V)
                $wsCells .= $this->biffRecord(0x00FD, pack('vvvV', $rIdx, $cIdx, 15, $sstIdx));
            }
        }
        $wsEof = $this->biffRecord(0x000A, '');
        $worksheetStream = $wsBof . $wsDim . $wsCells . $wsEof;

        // 2. Susun Workbook Globals Substream
        $wbBof = $this->biffRecord(0x0809, pack('vvvvVV', 0x0600, 0x0005, 0x0DBB, 0x07CC, 0x00000000, 0x00000006));
        $wbCodepage = $this->biffRecord(0x0042, pack('v', 0x04B0)); // 1200 = UTF-16LE
        $wbWindow1 = $this->biffRecord(0x003D, pack('vvvvvvvvv', 0x01E0, 0x005A, 0x3FCF, 0x2A4E, 0x0038, 0x0000, 0x0000, 0x0001, 0x0258));

        // 5 FONT standar minimal agar Excel tidak menampilkan warning style
        $wbFonts = str_repeat($this->buildBiff8Font('Arial', 200, 400), 4)
            . $this->buildBiff8Font('Arial', 200, 700); // Font index 4 (unused) / header opsional

        // Minimal 16 XF records (index 0..15, masing-masing payload tepat 20 byte)
        $xfCell = $this->biffRecord(0x00E0, pack('vvvCCCCvvVv', 0, 0, 0x0000, 0x20, 0x00, 0x00, 0x00, 0x0000, 0x0000, 0x00000000, 0x20C0));
        $xfStyle = $this->biffRecord(0x00E0, pack('vvvCCCCvvVv', 0, 0, 0xFFF5, 0x20, 0x00, 0x00, 0x00, 0x0000, 0x0000, 0x00000000, 0x20C0));
        $wbXfs = str_repeat($xfStyle, 15) . $xfCell;

        // SST (0x00FC)
        $wbSst = $this->buildSstRecord($totalStrings, $sstStrings);

        $wbEof = $this->biffRecord(0x000A, '');

        // Hitung ukuran BOUNDSHEET dulu untuk mengetahui offset awal Worksheet Substream
        $sheetNameUtf16 = mb_convert_encoding($cleanSheetName, 'UTF-16LE', 'UTF-8');
        $sheetCharCount = mb_strlen($cleanSheetName, 'UTF-8');
        $boundsheetHeaderLen = 4 + 2 + 2 + strlen($sheetNameUtf16); // offset(V) + flags/type(v) + cch(C) + flags(C) + utf16
        $boundsheetTotalLen = 4 + $boundsheetHeaderLen;

        $preBoundsheet = $wbBof . $wbCodepage . $wbWindow1 . $wbFonts . $wbXfs;
        $postBoundsheet = $wbSst . $wbEof;
        $wsOffset = strlen($preBoundsheet) + $boundsheetTotalLen + strlen($postBoundsheet);

        $wbBoundsheet = $this->biffRecord(
            0x0085,
            pack('VvCC', $wsOffset, 0x0000, $sheetCharCount, 0x01) . $sheetNameUtf16
        );

        $workbookStream = $preBoundsheet . $wbBoundsheet . $postBoundsheet . $worksheetStream;

        // 3. Bungkus ke dalam OLE2 Compound Document (minimum stream >= 4096 byte agar pakai FAT reguler)
        return $this->wrapInOle2($workbookStream);
    }

    private function biffRecord(int $opcode, string $payload): string
    {
        return pack('vv', $opcode, strlen($payload)) . $payload;
    }

    private function buildBiff8Font(string $name, int $heightTwips, int $weight): string
    {
        $len = strlen($name);
        // dyHeight(v), grbit(v), icv(v), bls(v), sss(v), uls(C), bFamily(C), bCharSet(C), reserved(C), cch(C), fHighByte(C), name
        $payload = pack('vvvvvCCCCCC', $heightTwips, 0x0000, 0x7FFF, $weight, 0x0000, 0, 0, 0, 0, $len, 0) . $name;
        return $this->biffRecord(0x0031, $payload);
    }

    /**
     * Membangun record SST (Shared String Table), otomatis memecah ke CONTINUE (0x003C) jika melebihi batas BIFF8
     *
     * @param array<int, string> $strings
     */
    private function buildSstRecord(int $totalStrings, array $strings): string
    {
        $maxRecordBytes = 8220;
        $chunks = [];
        $currentOpcode = 0x00FC; // SST
        $currentBuf = pack('VV', $totalStrings, count($strings));

        foreach ($strings as $str) {
            $utf16 = mb_convert_encoding($str, 'UTF-16LE', 'UTF-8');
            $cch = mb_strlen($str, 'UTF-8');
            $entry = pack('vC', $cch, 0x01) . $utf16;

            if (strlen($currentBuf) + strlen($entry) > $maxRecordBytes) {
                $chunks[] = $this->biffRecord($currentOpcode, $currentBuf);
                $currentOpcode = 0x003C; // CONTINUE
                $currentBuf = '';
            }
            $currentBuf .= $entry;
        }
        $chunks[] = $this->biffRecord($currentOpcode, $currentBuf);

        return implode('', $chunks);
    }

    /**
     * Membungkus Workbook stream ke format kontainer OLE2 Compound File
     */
    private function wrapInOle2(string $workbookStream): string
    {
        $sectorSize = 512;
        $streamRealLen = strlen($workbookStream);

        // Pastikan stream minimal 4096 byte (8 sektor) agar tidak memakai MiniFAT
        $paddedLen = max(4096, (int) (ceil($streamRealLen / $sectorSize) * $sectorSize));
        $workbookData = str_pad($workbookStream, $paddedLen, "\x00");
        $workbookSectors = (int) ($paddedLen / $sectorSize);

        // Susunan sektor:
        // Sector 0 .. $workbookSectors - 1 : Workbook stream
        // Sector $dirSector                : Directory entries (1 sektor = 4 entri x 128 byte)
        // Sector $fatSector                : FAT table (1 sektor = 128 uint32)
        $dirSector = $workbookSectors;
        $fatSector = $workbookSectors + 1;

        // 1. OLE2 Header (512 bytes)
        $header = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1" // Magic
            . str_repeat("\x00", 16)               // CLSID
            . pack('vv', 0x003E, 0x0003)           // Minor version 3.62
            . pack('v', 0xFFFE)                    // Little-endian
            . pack('vv', 0x0009, 0x0006)           // Sector shift (512B), Mini sector shift (64B)
            . str_repeat("\x00", 6)                // Reserved
            . pack('V', 0)                         // Total dir sectors (0 for v3)
            . pack('V', 1)                         // Total FAT sectors = 1
            . pack('V', $dirSector)                // First directory sector
            . pack('V', 0)                         // Transaction signature
            . pack('V', 4096)                      // Mini stream cutoff size
            . pack('V', 0xFFFFFFFE)                // First MiniFAT sector = ENDOFCHAIN
            . pack('V', 0)                         // Total MiniFAT sectors = 0
            . pack('V', 0xFFFFFFFE)                // First DIFAT sector = ENDOFCHAIN
            . pack('V', 0);                        // Total DIFAT sectors = 0

        // 109 DIFAT entries (436 bytes): entry 0 = $fatSector, sisanya FREESECT (0xFFFFFFFF)
        $header .= pack('V', $fatSector) . str_repeat(pack('V', 0xFFFFFFFF), 108);

        // 2. Directory Sector (512 bytes = 4 entries x 128 bytes)
        $rootEntry = $this->oleDirectoryEntry('Root Entry', 5, 1, -1, -1, -1, 0xFFFFFFFE, 0);
        $wbEntry = $this->oleDirectoryEntry('Workbook', 2, -1, -1, -1, -1, 0, $paddedLen);
        $emptyEntry = str_repeat("\x00", 128);
        $dirData = $rootEntry . $wbEntry . $emptyEntry . $emptyEntry;

        // 3. FAT Sector (512 bytes = 128 uint32 entries)
        $fatEntries = [];
        for ($i = 0; $i < $workbookSectors; $i++) {
            $fatEntries[] = ($i === $workbookSectors - 1) ? 0xFFFFFFFE : ($i + 1);
        }
        $fatEntries[] = 0xFFFFFFFE; // $dirSector
        $fatEntries[] = 0xFFFFFFFD; // $fatSector (FATSECT)
        while (count($fatEntries) < 128) {
            $fatEntries[] = 0xFFFFFFFF; // FREESECT
        }
        $fatData = pack('V*', ...$fatEntries);

        return $header . $workbookData . $dirData . $fatData;
    }

    private function oleDirectoryEntry(
        string $name,
        int $type,
        int $childId,
        int $leftSibling,
        int $rightSibling,
        int $color,
        int $startSector,
        int $size
    ): string {
        $utf16Name = mb_convert_encoding($name . "\x00", 'UTF-16LE', 'UTF-8');
        $nameByteLen = strlen($utf16Name);
        $paddedName = str_pad(substr($utf16Name, 0, 64), 64, "\x00");

        return $paddedName
            . pack('v', $nameByteLen)
            . pack('CC', $type, $color === -1 ? 1 : $color)
            . pack('VVV', $leftSibling & 0xFFFFFFFF, $rightSibling & 0xFFFFFFFF, $childId & 0xFFFFFFFF)
            . str_repeat("\x00", 16) // CLSID
            . pack('V', 0)           // State bits
            . str_repeat("\x00", 16) // Creation & modification timestamps
            . pack('V', $startSector & 0xFFFFFFFF)
            . pack('V', $size)
            . pack('V', 0);          // High 32-bit size
    }

    private function parseSpreadsheetMl(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return [];
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        libxml_clear_errors();
        if (!$xml) {
            return [];
        }

        $rows = [];
        $namespaces = $xml->getNamespaces(true);
        $ssNs = $namespaces['ss'] ?? 'urn:schemas-microsoft-com:office:spreadsheet';

        $worksheets = $xml->children($ssNs)->Worksheet ?? $xml->Worksheet ?? [];
        foreach ($worksheets as $ws) {
            $table = $ws->children($ssNs)->Table ?? $ws->Table ?? null;
            if (!$table) {
                continue;
            }
            $rowNodes = $table->children($ssNs)->Row ?? $table->Row ?? [];
            foreach ($rowNodes as $rowNode) {
                $row = [];
                $colIdx = 0;
                $cellNodes = $rowNode->children($ssNs)->Cell ?? $rowNode->Cell ?? [];
                foreach ($cellNodes as $cellNode) {
                    $attrs = $cellNode->attributes($ssNs);
                    if (isset($attrs['Index'])) {
                        $targetIdx = max(0, ((int) $attrs['Index']) - 1);
                        while ($colIdx < $targetIdx) {
                            $row[] = '';
                            $colIdx++;
                        }
                    }
                    $dataNode = $cellNode->children($ssNs)->Data ?? $cellNode->Data ?? null;
                    $row[] = $dataNode !== null ? (string) $dataNode : trim((string) $cellNode);
                    $colIdx++;
                }
                $rows[] = $row;
            }
            break; // Sheet pertama saja
        }

        return $rows;
    }

    private function parseHtmlTable(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return [];
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content);
        libxml_clear_errors();

        $rows = [];
        foreach ($dom->getElementsByTagName('tr') as $tr) {
            $row = [];
            foreach ($tr->childNodes as $cell) {
                if (!in_array(strtolower($cell->nodeName), ['td', 'th'], true)) {
                    continue;
                }
                $row[] = trim($cell->textContent);
            }
            if (!empty($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function parseDelimitedText(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [];
        }

        $firstLine = fgets($handle) ?: '';
        rewind($handle);

        $firstLineClean = ltrim($firstLine, "\xEF\xBB\xBF");
        $delimiter = ',';
        if (substr_count($firstLineClean, ';') > substr_count($firstLineClean, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLineClean, "\t") > substr_count($firstLineClean, ',')) {
            $delimiter = "\t";
        }

        $rows = [];
        $lineNum = 0;
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($lineNum === 0 && isset($row[0])) {
                $row[0] = ltrim($row[0], "\xEF\xBB\xBF");
            }
            $rows[] = $row;
            $lineNum++;
        }
        fclose($handle);

        return $rows;
    }

    /**
     * @param array<int, array<int, mixed>> $rows
     * @return array<int, array<int, string>>
     */
    private function normalizeRows(array $rows): array
    {
        $normalized = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $cleanRow = array_map(fn ($v) => trim((string) ($v ?? '')), array_values($row));
            // Lewati baris yang seluruh selnya kosong
            if (count(array_filter($cleanRow, fn ($v) => $v !== '')) === 0) {
                continue;
            }
            $normalized[] = $cleanRow;
        }
        return $normalized;
    }
}
