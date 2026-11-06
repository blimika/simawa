<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BroadcastCampaign;
use App\Services\BroadcastService;

class ProcessBroadcastCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'broadcast:process {campaign_id? : ID Campaign yang ingin diproses}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memproses pengiriman pesan massal (Broadcast) dengan jeda anti-blokir';

    /**
     * Execute the console command.
     */
    public function handle(BroadcastService $broadcastService): int
    {
        $campaignId = $this->argument('campaign_id');

        if ($campaignId) {
            $campaign = BroadcastCampaign::find($campaignId);
            if (!$campaign) {
                $this->error("Broadcast Campaign ID #{$campaignId} tidak ditemukan.");
                return Command::FAILURE;
            }

            $this->info("Memulai pemrosesan Broadcast #{$campaign->id} ({$campaign->name})...");
            $broadcastService->processCampaign($campaign);
            $this->info("Pemrosesan Broadcast #{$campaign->id} selesai.");
            return Command::SUCCESS;
        }

        // Jika tidak menyertakan ID, proses semua broadcast terjadwal
        $this->info("Memeriksa broadcast terjadwal...");
        $processed = $broadcastService->processScheduledCampaigns();
        $this->info("Selesai memproses {$processed} broadcast terjadwal.");

        return Command::SUCCESS;
    }
}
