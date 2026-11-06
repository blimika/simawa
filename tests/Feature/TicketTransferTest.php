<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_transfer_ticket_to_another_agent(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test@simawa.test'],
            [
                'name' => 'Admin Test',
                'username' => 'admin_test',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $agent1 = User::firstOrCreate(
            ['email' => 'agent1_test@simawa.test'],
            [
                'name' => 'Agent One',
                'username' => 'agent1_test',
                'password' => bcrypt('password'),
                'role' => 'agent',
                'parent_id' => $admin->id,
                'is_active' => true,
            ]
        );

        $agent2 = User::firstOrCreate(
            ['email' => 'agent2_test@simawa.test'],
            [
                'name' => 'Agent Two',
                'username' => 'agent2_test',
                'password' => bcrypt('password'),
                'role' => 'agent',
                'parent_id' => $admin->id,
                'is_active' => true,
            ]
        );

        $device = Device::firstOrCreate(
            ['phone_number' => '628999999999'],
            [
                'name' => 'Device Test',
                'user_id' => $admin->id,
                'gowa_session_id' => 'device_test',
                'status' => 'connected',
            ]
        );

        $device->agents()->syncWithoutDetaching([$agent1->id, $agent2->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'LYN-TEST-' . rand(1000, 9999),
            'device_id' => $device->id,
            'number' => '628123456789',
            'agent_id' => $agent1->id,
            'status' => 'open',
            'tag' => 'umum',
        ]);

        // 1. Agent 1 transfers ticket to Agent 2 using target_agent_id
        $response = $this->actingAs($agent1)
            ->post(route('messages.transfer', $ticket->id), [
                'target_agent_id' => $agent2->id,
            ]);

        $response->assertRedirect(route('messages.index'));
        $response->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals($agent2->id, $ticket->agent_id);

        // 2. Admin transfers ticket back to Agent 1 using input 'agent_id'
        $response2 = $this->actingAs($admin)
            ->post(route('messages.transfer', $ticket->id), [
                'agent_id' => $agent1->id,
            ]);

        $response2->assertRedirect(route('messages.index', ['ticket_id' => $ticket->id]));
        $ticket->refresh();
        $this->assertEquals($agent1->id, $ticket->agent_id);

        // 3. Agent 2 takes over the ticket
        $response3 = $this->actingAs($agent2)
            ->post(route('messages.transfer', $ticket->id), [
                'action' => 'take_over',
            ]);

        $response3->assertRedirect(route('messages.index', ['ticket_id' => $ticket->id]));
        $ticket->refresh();
        $this->assertEquals($agent2->id, $ticket->agent_id);

        // 4. Closed ticket cannot be transferred
        $ticket->update(['status' => 'closed']);
        $response4 = $this->actingAs($admin)
            ->post(route('messages.transfer', $ticket->id), [
                'target_agent_id' => $agent1->id,
            ]);
        $response4->assertSessionHas('error');
    }

    public function test_livechat_index_renders_with_agents_for_ticket(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test@simawa.test'],
            [
                'name' => 'Admin Test',
                'username' => 'admin_test',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $agent1 = User::firstOrCreate(
            ['email' => 'agent1_test@simawa.test'],
            [
                'name' => 'Agent One',
                'username' => 'agent1_test',
                'password' => bcrypt('password'),
                'role' => 'agent',
                'parent_id' => $admin->id,
                'is_active' => true,
            ]
        );

        $agent2 = User::firstOrCreate(
            ['email' => 'agent2_test@simawa.test'],
            [
                'name' => 'Agent Two',
                'username' => 'agent2_test',
                'password' => bcrypt('password'),
                'role' => 'agent',
                'parent_id' => $admin->id,
                'is_active' => true,
            ]
        );

        $device = Device::firstOrCreate(
            ['phone_number' => '628999999999'],
            [
                'name' => 'Device Test',
                'user_id' => $admin->id,
                'gowa_session_id' => 'device_test',
                'status' => 'connected',
            ]
        );

        $device->agents()->syncWithoutDetaching([$agent1->id, $agent2->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'LYN-TEST-' . rand(1000, 9999),
            'device_id' => $device->id,
            'number' => '628123456789',
            'agent_id' => $agent1->id,
            'status' => 'open',
            'tag' => 'umum',
        ]);

        $response = $this->actingAs($agent1)
            ->get(route('messages.index', ['ticket_id' => $ticket->id]));

        $response->assertStatus(200);
        $response->assertSee('target_agent_id');
        $response->assertSee($agent2->name);
    }
}
