<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 20.07.19 16:08
 *
 */

use Illuminate\Database\Eloquent\ModelNotFoundException;
use WHMCS\Module\Addon\TeamSpeak3\Configs\ModuleConfig;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3Controller;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3DomainController;
use WHMCS\Module\Addon\TeamSpeak3\Models\ServiceDomainModel;
use WHMCS\Module\Addon\TeamSpeak3\Models\ServiceVirtualServerModel;
use WHMCS\Product\Product;
use WHMCS\Service\Service;

add_hook('AdminAreaHeaderOutput', 99999999, function ($vars) {
    if (!isset($_GET['module']) || $_GET['module'] != ModuleConfig::getModuleName()) {
        return null;
    }

    $css['admin'] = getFile('css', 'admin');
    $returnStr = '';

    foreach ($css as $typeUsing => $files) {
        foreach ($files as $file) {
            $returnStr .= formattingIncludeCssFile($typeUsing, $file, true);
        }
    }

    return $returnStr;
});

function getFile($typeResource, $typeUsing)
{
    return array_diff(scandir(ModuleConfig::getBaseFullPath() . "/templates/$typeResource/$typeUsing"), ['..', '.']);
}

function formattingIncludeCssFile($typeUsing, $name, $timestampVersion = false)
{
    $path = ModuleConfig::getBaseRelativePath() . '/templates/css/' . $typeUsing . '/' . $name;

    if ($timestampVersion) {
        $path .= '?v=' . time();
    }

    return '<link rel="stylesheet" type="text/css" href="' . $path . '">' . PHP_EOL;
}


add_hook('EmailPreSend', 1, function ($vars) {
    $service_id = $vars['relid'];

    $serviceVirtualServer = ServiceVirtualServerModel::where('service_id', $service_id)->first();

    if (empty($serviceVirtualServer)) {
        return [];
    }

    $service = $serviceVirtualServer->service()->first();

    if (empty($service)) {
        throw new Exception('relationship error');
    }

    $ts3 = new TeamSpeak3Controller($service->server);
    $server = $ts3->getInstance()->serverGetByPort($serviceVirtualServer->port);

    $custom_fields['TeamSpeak3'] = [
        'virtualServerPort' => (string)$serviceVirtualServer->port,
        'privilegeKey' => (string)$server->privilegeKeyList()->keys()->first(),
    ];

    return $custom_fields;
});


add_hook('ShoppingCartValidateProductUpdate', 1, function ($vars) {
    $pid = $_SESSION['cart']['products'][$vars['i']]['pid'];
    $product = Product::findOrFail($pid);

    if ($product->servertype !== 'teamspeak3') {
        return [];
    }

    $productCustomFieldSubDomain = $product->customFields()->where(
        'fieldname', '=', ModuleConfig::getProductFieldWithASubDomain()
    )->firstOrFail();

    $productCustomFieldDomain = $product->customFields()->where(
        'fieldname', '=', ModuleConfig::getProductFieldWithAListOfDomains()
    )->firstOrFail();

    if (!array_key_exists('customfield', $vars)) {
        return;
    }

    if (!array_key_exists($productCustomFieldSubDomain->id, $vars['customfield'])) {
        return;
    }

    if (!array_key_exists($productCustomFieldSubDomain->id, $vars['customfield'])) {
        return;
    }

    $subDomain = $vars['customfield'][$productCustomFieldSubDomain->id];
    $domain = $vars['customfield'][$productCustomFieldDomain->id];

    $teamSpeak3DomainController = new TeamSpeak3DomainController();

    if (!$teamSpeak3DomainController->subDomainRegexValidation($subDomain)) {
        return 'Допускаются только символы a-Z, 0-9 а так же -';
    }
    if (!$teamSpeak3DomainController->subDomainIsNotBlacklist($subDomain)) {
        return 'Данный суб домен занесен в черный список администратором';
    }

    try {
        if (!$teamSpeak3DomainController->subDomainIsNotExits($subDomain, $domain)) {
            return 'Данный адрес уже занят другим клиентом';
        }
    } catch (Exception $e) {
        return 'Произошла ошибка при обращении к серверу буквенных адресов';
    }

    return '';
});


add_hook('AdminClientServicesTabFields', 1, function ($vars) {
    $service_id = $vars['id'];

    $service = Service::findOrFail($service_id);
    $product = Product::findOrFail($service->packageid);

    if ($product->servertype !== 'teamspeak3') {
        return [];
    }

    if ($service->domainstatus === 'Terminated') {
        return [
            'Сервер удален' => 'Вся информация об удаленном сервере удалена'
        ];
    }

    $server = $service->serverModel()->firstOrFail();

    try {
        $serviceVirtualServer = ServiceVirtualServerModel::where('service_id', $service_id)->firstOrFail();
    } catch (ModelNotFoundException $e) {
        return [
            'не привязан' => ''
        ];
    }

    try {
        $serviceDomain = ServiceDomainModel::where('service_id', $service_id)->firstOrFail();
    } catch (ModelNotFoundException $e) {
        $serviceDomain = null;
    }

    $result = [];
    $result['port'] = buildJsEditable($service_id, $serviceVirtualServer->port, 'port');
    $result['uid'] = buildJsEditable($service_id, $serviceVirtualServer->uid, 'uid');
    $result['Полный адрес'] = $server->hostname . ':' . $serviceVirtualServer->port;

    if ($serviceDomain === null) {
        $result['Буквенный адрес'] = 'Отсутствует информация о привязке домена';
    } else {
        $teamSpeak3DomainController = new TeamSpeak3DomainController();
        if ($teamSpeak3DomainController->subDomainVerifyForServer(
            strtolower($serviceDomain->sub_domain),
            $serviceDomain->domain,
            $serviceVirtualServer->port,
            $server->hostname
        )) {
            $result['Буквенный адрес'] = 'Присвоен (Запись в DNS менеджере указывает на этот сервер)';
        } else {
            $result['Буквенный адрес'] = 'Информация о привязке есть, однако домен не присвоен либо присвоен другому серверу (ошибка)';
        }
    }

    $result['Публичная оплата'] = 'Не реализована';
    echo getJsEditableScript();
    return $result;
});

function getJsEditableScript()
{
    return '<script>
function editServiceAssociation($element,$service_id, $field) {
    var newValue = prompt("Введите новое значение");
    $element= $($element).parent().find(\'span#\'+$field);
    console.log($element,$service_id,$field,newValue);
    if(newValue !== null){
     $.ajax({
        type: "POST",
        url: "addonmodules.php?module=TeamSpeak3",
        data: Object.assign({action: "editServiceAssociation"}, {service_id: $service_id,value: newValue,field: $field}),
        dataType: \'json\',
        success: function (data) {
            if (data.status === \'error\') {
                this.fail(data);
                return;
            }
            $element.text(newValue);
            alert(\'ok\');
        },
        fail: function (data) {
            alert("error->"+data.message); 
        }
    });
            }
}
</script>';

}

function buildJsEditable($service_id, $value, $field)
{
    return sprintf("<span id='$field'>$value</span> <i style='padding-left: 10px' class='fa fa-pencil' onclick=\"editServiceAssociation(this,%s,'%s')\"></i>", $service_id, $field);
}

add_hook('IntelligentSearch', 1, function ($vars) {
    $result = [];

    if (strpos($vars['searchTerm'], 'port') !== false) {
        $port = str_replace('port ', '', $vars['searchTerm']);

        $serviceVirtualServers = ServiceVirtualServerModel::where('port', $port)->get();

        if ($serviceVirtualServers->isEmpty()) {
            return [];
        }

        foreach ($serviceVirtualServers as $serviceVirtualServer) {
            $service = $serviceVirtualServer->service()->firstOrFail();
            $server = $service->serverModel()->firstOrFail();
            $result[] = [
                'port' => $port,
                'uid' => $serviceVirtualServer->uid,
                'service' => $service,
                'server' => $server
            ];
        }
    }

    if (strpos($vars['searchTerm'], 'uid') !== false) {
        $uid = str_replace('uid ', '', $vars['searchTerm']);

        $serviceVirtualServers = ServiceVirtualServerModel::where('uid', $uid)->get();

        if ($serviceVirtualServers->isEmpty()) {
            return [];
        }

        foreach ($serviceVirtualServers as $serviceVirtualServer) {
            $service = $serviceVirtualServer->service()->firstOrFail();
            $server = $service->serverModel()->firstOrFail();
            $result[] = [
                'port' => $serviceVirtualServer->port,
                'uid' => $uid,
                'service' => $service,
                'server' => $server
            ];
        }
    }

    if (preg_match('/(\S+):(\d+)/', $vars['searchTerm'], $matches, PREG_OFFSET_CAPTURE, 0) === 1) {
        $serverHostname = $matches[1][0];
        $virtualServerPort = $matches[2][0];

        $serviceVirtualServer = ServiceVirtualServerModel::with('service.serverModel')
            ->whereHas('service.serverModel', function ($query) use ($serverHostname) {
                $query->where('hostname', '=', $serverHostname);
            })->where('port', $virtualServerPort)->first();

        $result[] = [
            'port' => $virtualServerPort,
            'uid' => $serviceVirtualServer->uid,
            'service' => $serviceVirtualServer->service,
            'server' => $serviceVirtualServer->service->serverModel
        ];
    }


    foreach ($result as $teamSpeak3Server) {
        $searchResults[] = [
            'title' => $teamSpeak3Server['service']->domain, // The title of the search result. This is required.
            'href' => 'clientsservices.php?productselect=' . $teamSpeak3Server['service']->id,
            'subTitle' => 'TeamSpeak 3 virtual server' . '<br/>' .
                'server->' . $teamSpeak3Server['server']->hostname . '<br/>' .
                'port->' . $teamSpeak3Server['port'] . '<br/>' .
                'uid->' . $teamSpeak3Server['uid'] . '<br/>',
            'icon' => 'fab fa-teamspeak', // A font-awesome icon for the search result. Defaults to 'fal fa-star' if not defined.
        ];
    }

    return $searchResults;
});