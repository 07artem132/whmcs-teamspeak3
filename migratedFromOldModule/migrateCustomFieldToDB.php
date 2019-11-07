<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 25.09.19 22:46
 *
 */

require __DIR__ . '/../../../../init.php';

use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3Controller;
use WHMCS\Module\Addon\TeamSpeak3\Models\ServiceVirtualServerModel;
use WHMCS\Product\Product;

$products = Product::with(["services" => function ($q) {
    $q->where('domainstatus', '=', 'Active');
}])->where('servertype', 'teamspeak3')->get();

foreach ($products as $product) {
    foreach ($product->services as $service) {
        echo 'begin->' . $service->id . PHP_EOL;
        $virtualServerPort = $service->customFieldValues()->whereHas('CustomField', function ($q) {
            $q->where('fieldname', 'Port');
        })->first()->value;
        $ts3 = new TeamSpeak3Controller($service->server);
        $server = $ts3->getInstance()->serverGetByPort($virtualServerPort);
        ServiceVirtualServerModel::create([
            'service_id' => $service->id,
            'port' => $virtualServerPort,
            'uid' => $server->virtualserver_unique_identifier,
        ]);
        echo 'create->' . $service->id . PHP_EOL;
    }
}