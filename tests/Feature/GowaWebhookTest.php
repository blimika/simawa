<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class GowaWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_handles_gowa_v9_message_by_session_id()
    {
        // Disable middleware if needed or hit api endpoint
        $user = User::factory()->create([
            'username' => 'testuser',
        ]);

        $device = Device::create([
            'name' => 'Device Test',
            'phone_number' => '628999999999',
            'gowa_session_id' => 'simawa-test-slot',
            'status' => 'connected',
            'is_livechat_enabled' => false, // test logging and ignoring livechat ticket creation
            'user_id' => $user->id,
        ]);

        $payload = [
            'event' => 'message',
            'device_id' => '628999999999@s.whatsapp.net',
            'session_id' => 'simawa-test-slot',
            'payload' => [
                'id' => 'MSG-001',
                'chat_id' => '628111111111@s.whatsapp.net',
                'from' => '628111111111@s.whatsapp.net',
                'sender_display_name' => 'Budi Santoso',
                'from_name' => 'Budi',
                'body' => 'Halo, ini uji coba GOWA v9',
                'is_from_me' => false,
            ]
        ];

        $response = $this->postJson('/api/webhook/gowa', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('webhook_logs', [
            'device_id' => $device->id,
            'sender_number' => '628111111111',
            'message' => 'Halo, ini uji coba GOWA v9',
        ]);
    }

    public function test_webhook_handles_device_logged_out_event()
    {
        $user = User::factory()->create([
            'username' => 'testuser2',
        ]);

        $device = Device::create([
            'name' => 'Device Test 2',
            'phone_number' => '628888888888',
            'gowa_session_id' => 'slot-to-logout',
            'status' => 'connected',
            'is_livechat_enabled' => false,
            'user_id' => $user->id,
        ]);

        $payload = [
            'event' => 'device.logged_out',
            'device_id' => '628888888888@s.whatsapp.net',
            'session_id' => 'slot-to-logout',
            'payload' => [
                'reason' => 'User logged out from phone'
            ]
        ];

        $response = $this->postJson('/api/webhook/gowa', $payload);

        $response->assertStatus(200);
        $device->refresh();
        $this->assertEquals('disconnected', $device->status);
    }
}
