<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'device_id',
        'sender_number',
        'message',
        'payload'
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Relasi ke Device
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Ambil data payload terurai dengan aman (baik jika berbentuk string maupun array)
     */
    public function getPayloadDataAttribute(): array
    {
        if (is_array($this->payload)) {
            return $this->payload;
        }

        if (is_string($this->payload)) {
            $decoded = json_decode($this->payload, true);
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Ambil nama event GOWA (contoh: message, device.logged_out)
     */
    public function getEventAttribute(): string
    {
        return data_get($this->payload_data, 'event', 'message');
    }

    /**
     * Ambil session ID GOWA
     */
    public function getSessionIdAttribute(): ?string
    {
        return data_get($this->payload_data, 'session_id')
            ?: ($this->device?->gowa_session_id ?? null);
    }

    /**
     * Ambil nama pengirim dari GOWA v9 (sender_display_name atau from_name)
     */
    public function getSenderDisplayNameAttribute(): ?string
    {
        return data_get($this->payload_data, 'payload.sender_display_name')
            ?: data_get($this->payload_data, 'payload.from_name')
            ?: null;
    }

    /**
     * Ambil Message ID unik dari WhatsApp
     */
    public function getMessageIdAttribute(): ?string
    {
        return data_get($this->payload_data, 'payload.id');
    }

    /**
     * Ambil Chat ID tujuan/sumber
     */
    public function getChatIdAttribute(): ?string
    {
        return data_get($this->payload_data, 'payload.chat_id');
    }

    /**
     * Ambil isi kutipan pesan (quoted reply)
     */
    public function getQuotedBodyAttribute(): ?string
    {
        return data_get($this->payload_data, 'payload.quoted_body');
    }

    /**
     * Deteksi tipe media dari payload GOWA v9
     */
    public function getMediaTypeAttribute(): string
    {
        $payload = data_get($this->payload_data, 'payload', []);
        if (!empty($payload['image'])) return 'image';
        if (!empty($payload['document'])) return 'document';
        if (!empty($payload['video'])) return 'video';
        if (!empty($payload['audio'])) return 'audio';
        if (!empty($payload['sticker'])) return 'sticker';
        return 'text';
    }

    /**
     * Ambil detail media jika ada (url, caption, filename, mimetype)
     */
    public function getMediaInfoAttribute(): ?array
    {
        $payload = data_get($this->payload_data, 'payload', []);
        $type = $this->media_type;
        if ($type === 'text') {
            return null;
        }

        $media = $payload[$type] ?? null;
        if (is_array($media)) {
            return [
                'type' => $type,
                'url' => $media['url'] ?? $media['path'] ?? null,
                'caption' => $media['caption'] ?? null,
                'filename' => $media['file_name'] ?? $media['filename'] ?? null,
                'mimetype' => $media['mimetype'] ?? $media['mime_type'] ?? null,
            ];
        }

        if (is_string($media)) {
            return [
                'type' => $type,
                'url' => $media,
                'caption' => null,
                'filename' => basename($media),
                'mimetype' => null,
            ];
        }

        return null;
    }

    /**
     * Cek apakah chat berasal dari grup WhatsApp (@g.us)
     */
    public function getIsGroupAttribute(): bool
    {
        $from = (string) data_get($this->payload_data, 'payload.from', '');
        $chatId = (string) data_get($this->payload_data, 'payload.chat_id', '');
        return str_contains($from, '@g.us') || str_contains($chatId, '@g.us');
    }

    /**
     * Cek apakah pesan dikirim oleh diri sendiri (outbound)
     */
    public function getIsFromMeAttribute(): bool
    {
        return (bool) data_get($this->payload_data, 'payload.is_from_me', false);
    }

    /**
     * Ambil nomor tujuan pelanggan jika pesan keluar (outbound / from me)
     */
    public function getTargetNumberAttribute(): ?string
    {
        if ($this->is_from_me && $this->chat_id) {
            $clean = explode('@', explode(':', $this->chat_id)[0])[0];
            return preg_replace('/[^0-9]/', '', $clean);
        }
        return null;
    }
}
