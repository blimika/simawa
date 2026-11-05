<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$gowa = new App\Services\GowaService();
$res = $gowa->client('silaq')->get($gowa->getBaseUrl() . '/app/passkey');
echo "Passkey status HTTP: " . $res->status() . "\n";
print_r($res->json());
