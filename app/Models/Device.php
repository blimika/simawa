<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    /*
    protected $fillable = [
        'user_id', 'name', 'phone_number', 'gowa_session_id', 'status', 'webhook_url', 'is_livechat_enabled', 'api_key'
    ]; */
    protected $fillable = [
        'name', 'phone_number', 'gowa_session_id', 'status', 'webhook_url', 'user_id', 'is_livechat_enabled', 'api_key',
        'greeting_message', 'claim_message', 'closing_message',
        'whatsapp_name', 'whatsapp_avatar'
    ];

    public const DEFAULT_GREETING_MESSAGE = "Halo Bapak/Ibu {nama},\n"
        . "Terima kasih telah menghubungi layanan WhatsApp {device}.\n\n"
        . "Pesan Bapak/Ibu telah kami terima dengan\n"
        . "nomor tiket: {nomor_tiket}.\n\n"
        . "Operator kami akan menindaklanjuti pesan Bapak/Ibu pada jam layanan.\n\n"
        . "Terima kasih.";

    public const DEFAULT_CLAIM_MESSAGE = "Permintaan/tiket Bapak/Ibu saat ini sedang dilayani oleh Operator Kami *({agen})*.";

    public const DEFAULT_CLOSING_MESSAGE = "Terima kasih Bapak/Ibu {nama}.\n"
        . "Nomor tiket {nomor_tiket} telah kami tandai selesai.\n\n"
        . "Semoga informasi/layanan yang diberikan bermanfaat.\n"
        . "Apabila Bapak/Ibu {nama} membutuhkan layanan kembali, silakan mengirim pesan baru melalui WhatsApp ini.\n\n"
        . "Salam hangat,\n"
        . "Tim Layanan WhatsApp {device}.";

    /**
     * Format Pesan Pembuka (Salam Pembuka saat Tiket Baru dibuat)
     */
    public function getFormattedGreetingMessage($customerName = null, $ticketNumber = null): string
    {
        $template = !empty(trim($this->greeting_message ?? '')) ? $this->greeting_message : self::DEFAULT_GREETING_MESSAGE;
        return $this->replacePlaceholders($template, [
            'nama' => $customerName ?: 'Bapak/Ibu',
            'customer_name' => $customerName ?: 'Bapak/Ibu',
            'device' => $this->name ?: 'SIMAWA',
            'device_name' => $this->name ?: 'SIMAWA',
            'nomor_tiket' => $ticketNumber ?? '',
            'ticket_number' => $ticketNumber ?? '',
            'tiket' => $ticketNumber ?? '',
        ]);
    }

    /**
     * Format Pesan saat Tiket Diambil oleh Agen
     */
    public function getFormattedClaimMessage($agentName = null, $customerName = null, $ticketNumber = null): string
    {
        $template = !empty(trim($this->claim_message ?? '')) ? $this->claim_message : self::DEFAULT_CLAIM_MESSAGE;
        return $this->replacePlaceholders($template, [
            'agen' => $agentName ?: 'Operator',
            'agent_name' => $agentName ?: 'Operator',
            'nama' => $customerName ?: 'Bapak/Ibu',
            'customer_name' => $customerName ?: 'Bapak/Ibu',
            'device' => $this->name ?: 'SIMAWA',
            'device_name' => $this->name ?: 'SIMAWA',
            'nomor_tiket' => $ticketNumber ?? '',
            'ticket_number' => $ticketNumber ?? '',
            'tiket' => $ticketNumber ?? '',
        ]);
    }

    /**
     * Format Pesan Penutup (Salam Penutup saat Tiket Diselesaikan)
     */
    public function getFormattedClosingMessage($customerName = null, $ticketNumber = null, $agentName = null): string
    {
        $template = !empty(trim($this->closing_message ?? '')) ? $this->closing_message : self::DEFAULT_CLOSING_MESSAGE;
        return $this->replacePlaceholders($template, [
            'nama' => $customerName ?: 'Bapak/Ibu',
            'customer_name' => $customerName ?: 'Bapak/Ibu',
            'device' => $this->name ?: 'SIMAWA',
            'device_name' => $this->name ?: 'SIMAWA',
            'nomor_tiket' => $ticketNumber ?? '',
            'ticket_number' => $ticketNumber ?? '',
            'tiket' => $ticketNumber ?? '',
            'agen' => $agentName ?: 'Operator',
            'agent_name' => $agentName ?: 'Operator',
        ]);
    }

    /**
     * Ganti variabel/placeholder secara fleksibel (baik lowercase maupun uppercase)
     */
    protected function replacePlaceholders(string $text, array $replacements): string
    {
        $map = [];
        foreach ($replacements as $key => $val) {
            $map['{' . $key . '}'] = $val;
            $map['{' . strtoupper($key) . '}'] = $val;
        }
        return strtr($text, $map);
    }
    // Tambahkan baris ini agar selalu dibaca sebagai true/false
    protected $casts = [
        'is_livechat_enabled' => 'boolean',
    ];
    // Admin pemilik device ini
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Agent yang ditugaskan memegang device ini
    public function agents()
    {
        return $this->belongsToMany(User::class, 'agent_device', 'device_id', 'user_id')->withTimestamps();
    }

    // Relasi ke histori chat
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    // Di dalam Model Device
    public function scopeForUser($query)
    {
        $user = auth()->user();
        if ($user->role === 'superadmin') return $query;

        if ($user->role === 'admin') {
            return $query->where('user_id', $user->id);
        }

        if ($user->role === 'agent') {
            // Agent melihat device milik Admin-nya
            return $query->where('user_id', $user->parent_id);
        }
    }
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($device) {
            if (empty($device->api_key)) {
                $device->api_key = \Illuminate\Support\Str::random(32);
            }
        });
    }
}
