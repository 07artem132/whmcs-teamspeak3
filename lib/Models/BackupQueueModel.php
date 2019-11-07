<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 11.09.19 16:45
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Models;

use WHMCS\Model\AbstractModel;

/**
 * Class BackupQueueModel
 * @package WHMCS\Module\Addon\TeamSpeak3\Models
 * @property int $id
 * @property int $service_id
 * @property int $is_running
 * @property string $tag
 * @property int $keep_days
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class BackupQueueModel extends AbstractModel
{
    protected $table = "mod_addon_teamspeak3_backup_queue";
    protected $booleans = [];
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $fillable = [
        'service_id',
        'tag',
        'keep_days'
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