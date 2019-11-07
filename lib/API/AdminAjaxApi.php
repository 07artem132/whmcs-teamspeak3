<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 20.07.19 15:52
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\API;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use WHMCS\Module\Addon\TeamSpeak3\Models\ServiceVirtualServerModel;
use WHMCS\Module\Addon\TeamSpeak3\Models\SettingsModel;
use WHMCS\Module\Addon\TeamSpeak3\Traits\IsRequestMethodTraits;

class AdminAjaxApi
{
    use IsRequestMethodTraits;

    function boot()
    {
        global $customadminpath;

        if (!$this->isRequestMethod('POST')) {
            return;
        }

        try {
            switch (true) {
                case  array_key_exists('action', $_POST) && $_POST['action'] == 'saveSettings':
                    $inserts = [];
                    foreach ($_POST as $key => $val) {
                        if ($key == 'action') {
                            continue;
                        }
                        $user = SettingsModel::firstOrNew(array('key' => $key));
                        $user->val = $val;
                        $user->save();
                    }
                    $this->response('success', 'Изменения сохранены');
                    break;
                case  array_key_exists('action', $_POST) && $_POST['action'] == 'editServiceAssociation':
                    try {
                        $serviceVirtualServer = ServiceVirtualServerModel::where('service_id', $_POST['service_id'])->firstOrFail();
                        $serviceVirtualServer->{$_POST['field']} = $_POST['value'];
                        $serviceVirtualServer->saveOrFail();
                    } catch (ModelNotFoundException $e) {
                        $this->response('error', 'Сервис не привязан');
                    }
                    $this->response('success', 'Изменения сохранены');
                    break;
                default:
                    $this->response('error', 'Неизвестное действие');
                    break;
            }
        } catch (\Exception $e) {
            $this->response('error', $e->getMessage());
        }
    }

    function response($status, $message = null)
    {
        echo json_encode([
            'status' => $status,
            'message' => $message,
        ]);
        die();
    }
}