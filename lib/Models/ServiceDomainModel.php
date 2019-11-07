<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 21.09.19 22:13
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Models;

use WHMCS\Model\AbstractModel;

/**
 * Class ServiceVirtualServerModel
 * @package WHMCS\Module\Addon\TeamSpeak3\Models
 * @property int $id
 * @property int $service_id
 * @property string $domain
 * @property string $sub_domain
 * @property bool $anon_pay
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class ServiceDomainModel extends AbstractModel
{
    protected $table = "mod_addon_teamspeak3_service_to_domain";
    protected $booleans = [];
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $fillable = [
        'service_id',
        'domain',
        'sub_domain',
        'anon_pay'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function service()
    {
        return $this->hasOne("WHMCS\\Service\\Service", 'id', 'service_id');
    }

}