<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 25.09.19 22:46
 *
 */


require __DIR__ . '/../../../../init.php';

use WHMCS\Module\Addon\TeamSpeak3\Models\ServiceDomainModel;
use WHMCS\Product\Product;

$products = Product::with(["services" => function ($q) {
    $q->where('domain', '!=', '')->where('domainstatus', '=', 'Active')->orWhere('domainstatus', '=', 'Suspended');
}])->where('servertype', 'teamspeak3')->get();

$domains = [
    '.service-voice.com',
    '.s-v.su',
    '.svoice.ru',
    '.wface.top',
    '.rucf.top',
    '.wot-clan.ru',
    '.ts3.ru',
    '.voic.su',
    '.ts-3.top',
];
foreach ($products as $product) {
    foreach ($product->services as $service) {
        if (empty(trim($service->domain))) {
            continue;
        }
        echo 'begin->' . $service->id . PHP_EOL;
        if (strposa($service->domain, $domains, 1)) {
            $subDomain = $service->domain;
            foreach ($domains as $domain) {
                $subDomain = str_replace($domain, '', $subDomain, $count);
                if ($count > 0) {
                    ServiceDomainModel::create([
                        'service_id' => $service->id,
                        'domain' => substr($domain, 1),
                        'sub_domain' => $subDomain,
                        'anon_pay' => 0,
                    ]);
                    echo 'create->' . $service->id . PHP_EOL;
                    break;
                }
            }
        } else {
            echo 'skipped->' . $service->domain . PHP_EOL;
        }
    }
}


function strposa($haystack, $needles = array(), $offset = 0)
{
    $chr = array();
    foreach ($needles as $needle) {
        $res = strpos($haystack, $needle, $offset);
        if ($res !== false) $chr[$needle] = $res;
    }
    if (empty($chr)) return false;
    return min($chr);
}
