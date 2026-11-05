<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\GowaService;
use Illuminate\Support\Facades\Http;

class GowaServiceTest extends TestCase
{
    protected GowaService $gowa;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.gowa.url'  => 'http://localhost:3000',
            'services.gowa.user' => 'admin',
            'services.gowa.pass' => 'password',
            'services.gowa.webhook_secret' => 'test-secret',
        ]);
        $this->gowa = new GowaService();
    }

    public function test_create_device_gowa_v9()
    {
        Http::fake([
            'http://localhost:3000/devices' => Http::response([
                'code' => 'SUCCESS',
                'message' => 'Device added',
                'status' => 200,
                'results' => [
                    'id' => 'my-device-id',
                    'state' => 'disconnected',
                    'jid' => '',
                ]
            ], 200)
        ]);

        $res = $this->gowa->createDevice('my-device-id');
        $this->assertEquals('SUCCESS', $res['code']);
        $this->assertEquals('my-device-id', $res['results']['id']);
    }

    public function test_get_devices_gowa_v9()
    {
        Http::fake([
            'http://localhost:3000/devices' => Http::response([
                'code' => 'SUCCESS',
                'message' => 'List devices',
                'status' => 200,
                'results' => [
                    [
                        'id' => 'my-device-id',
                        'phone_number' => '628123456789',
                        'display_name' => 'John Doe',
                        'state' => 'logged_in',
                        'jid' => '628123456789@s.whatsapp.net',
                    ]
                ]
            ], 200)
        ]);

        $res = $this->gowa->getDevices();
        $this->assertEquals('SUCCESS', $res['code']);
        $this->assertCount(1, $res['results']);
        $this->assertEquals('628123456789', $res['results'][0]['phone_number']);
    }

    public function test_get_login_code_gowa_v9()
    {
        Http::fake([
            'http://localhost:3000/devices/test-device/login' => Http::response([
                'code' => 'SUCCESS',
                'message' => 'Login success',
                'status' => 200,
                'results' => [
                    'device_id' => 'test-device',
                    'qr_link' => 'http://localhost:3000/statics/images/qrcode/qr.png',
                    'qr_duration' => 30
                ]
            ], 200)
        ]);

        $res = $this->gowa->getLoginCode('test-device');
        $this->assertEquals('SUCCESS', $res['code']);
        $this->assertEquals('http://localhost:3000/statics/images/qrcode/qr.png', $res['results']['qr_link']);
    }

    public function test_check_device_status_gowa_v9()
    {
        Http::fake([
            'http://localhost:3000/devices/test-device' => Http::response([
                'code' => 'SUCCESS',
                'message' => 'Device info',
                'status' => 200,
                'results' => [
                    'id' => 'test-device',
                    'phone_number' => '628123456789',
                    'state' => 'logged_in',
                    'jid' => '628123456789@s.whatsapp.net',
                ]
            ], 200)
        ]);

        $res = $this->gowa->checkDeviceStatus('test-device');
        $this->assertEquals(200, $res['http_status']);
        $this->assertTrue($res['json_data']['results']['is_logged_in']);
    }

    public function test_send_message_gowa_v9()
    {
        Http::fake([
            'http://localhost:3000/send/message' => Http::response([
                'code' => 'SUCCESS',
                'message' => 'Success',
                'results' => [
                    'message_id' => 'MSG123',
                    'status' => 'success'
                ]
            ], 200)
        ]);

        $res = $this->gowa->sendMessage('test-device', '628123456789@s.whatsapp.net', 'Hello Test');
        $this->assertTrue($res->successful());
        $this->assertEquals('MSG123', $res->json('results.message_id'));

        Http::assertSent(function ($request) {
            return $request->url() === 'http://localhost:3000/send/message' &&
                   $request->header('X-Device-Id')[0] === 'test-device' &&
                   $request['phone'] === '628123456789@s.whatsapp.net' &&
                   $request['message'] === 'Hello Test';
        });
    }

    public function test_reconnect_and_logout_device_gowa_v9()
    {
        Http::fake([
            'http://localhost:3000/devices/test-device/reconnect' => Http::response([
                'code' => 'SUCCESS',
                'message' => 'Success',
                'results' => null
            ], 200),
            'http://localhost:3000/devices/test-device/logout' => Http::response([
                'code' => 'SUCCESS',
                'message' => 'Success',
                'results' => null
            ], 200),
        ]);

        $reconn = $this->gowa->reconnectDevice('test-device');
        $this->assertTrue($reconn->successful());

        $logout = $this->gowa->logoutDevice('test-device');
        $this->assertTrue($logout->successful());
    }

    public function test_get_pairing_code_gowa_v9()
    {
        Http::fake([
            'http://localhost:3000/devices/test-device/login/code?phone=628123456789' => Http::response([
                'code' => 'SUCCESS',
                'message' => 'Login with code started',
                'results' => [
                    'device_id' => 'test-device',
                    'pair_code' => 'ABCD-1234'
                ]
            ], 200)
        ]);

        $res = $this->gowa->getPairingCode('test-device', '628123456789');
        $this->assertEquals('SUCCESS', $res['code']);
        $this->assertEquals('ABCD-1234', $res['results']['pair_code']);
    }

    public function test_passkey_confirm_and_response_gowa_v9()
    {
        Http::fake([
            'http://localhost:3000/app/passkey/confirm' => Http::response([
                'code' => 'SUCCESS',
                'message' => 'Success',
                'results' => null
            ], 200),
            'http://localhost:3000/app/passkey/response' => Http::response([
                'code' => 'SUCCESS',
                'message' => 'Success',
                'results' => null
            ], 200),
        ]);

        $confirm = $this->gowa->confirmPasskey('test-device');
        $this->assertTrue($confirm->successful());

        $assertionRes = $this->gowa->submitPasskeyResponse('test-device', [
            'id' => 'sample-id',
            'rawId' => 'sample-raw-id',
            'type' => 'public-key',
            'response' => [
                'clientDataJSON' => 'abc',
                'authenticatorData' => 'def',
                'signature' => 'ghi',
            ]
        ]);
        $this->assertTrue($assertionRes->successful());
    }
}
