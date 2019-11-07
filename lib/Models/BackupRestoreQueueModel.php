<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 11.09.19 16:54
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Models;

use WHMCS\Model\AbstractModel;

/**
 * Class BackupRestoreQueueModel
 * @package WHMCS\Module\Addon\TeamSpeak3\Models
 * @property int $id
 * @property int $service_id
 * @property int $is_running
 * @property string $date
 * @property string $tag
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class BackupRestoreQueueModel extends AbstractModel
{
    protected $table = "mod_addon_teamspeak3_backup_restore_queue";
    protected $booleans = [];
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $fillable = [
        'service_id',
        'date',
        'tag'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function service()
    {
        return $this->hasOne("WHMCS\\Service\\Service", 'id', 'service_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function serviceToVirtualServer()
    {
        return $this->hasOne("WHMCS\\Module\\Addon\\TeamSpeak3\\Models\\ServiceVirtualServerModel", 'service_id', 'service_id');
    }

}