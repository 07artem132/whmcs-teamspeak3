<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 08.09.19 13:17
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Controllers;

use Exception;
use WHMCS\Module\Addon\TeamSpeak3\Abstracts\ApiAccessController;
use WHMCS\Module\Addon\TeamSpeak3\Models\ServiceVirtualServerModel;

class UserApiAccessController extends ApiAccessController
{
    /**
     * @param int $user_id
     * @param int $service_id
     * @return bool
     * @throws Exception
     */
    public static function AllowActionService(int $user_id, int $service_id): bool
    {
        $serviceVirtualServer = ServiceVirtualServerModel::where('service_id', $service_id)->first();

        if (empty($serviceVirtualServer)) {
            return false;
        }

        $service = $serviceVirtualServer->service()->first();

        if (empty($service)) {
            throw new Exception('relationship error');
        }

        if ($service->userid !== $user_id) {
            return false;
        }

        return true;
    }
}