<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 08.09.19 13:32
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Models;

use WHMCS\Model\AbstractModel;

/**
 * Class ServiceVirtualServerModel
 * @package WHMCS\Module\Addon\TeamSpeak3\Models
 * @property int $id
 * @property int $service_id
 * @property int $port
 * @property string $uid
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class ServiceVirtualServerModel extends AbstractModel
{
    protected $table = "mod_addon_teamspeak3_service_to_virtual_server";
    protected $booleans = [];
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $fillable = [
        'service_id',
        'port',
        'uid'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function service()
    {
        return $this->hasOne("WHMCS\\Service\\Service", 'id', 'service_id');
    }

}