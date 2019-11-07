<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 20.07.19 16:47
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Models;

use Illuminate\Database\Eloquent\Builder;
use WHMCS\Model\AbstractModel;

/**
 * Class SettingsModel
 * @package WHMCS\Module\Addon\TeamSpeak3\Models
 * @method Builder serverDefaultSettings()
 * @property string $key
 * @property string $val
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @mixin Builder
 */
class SettingsModel extends AbstractModel
{
    protected $table = "mod_addon_teamspeak3_settings";
    protected $booleans = [];
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $fillable = [
        'key',
        'val'
    ];


    /**
     * Выбрать из таблицы настройки виртуального сервера по умолчанию
     * @param Builder $query
     * @return Builder
     */

    public function scopeServerDefaultSettings(Builder $query): Builder
    {
        return $query->orWhere('key', 'bannerlinkurl')
            ->orWhere('key', 'bannerimgurl')
            ->orWhere('key', 'bannermode')
            ->orWhere('key', 'buttonlinkurl')
            ->orWhere('key', 'buttonimgurl')
            ->orWhere('key', 'buttonmsgtooltip')
            ->orWhere('key', 'servermsg')
            ->orWhere('key', 'servermsgmode')
            ->orWhere('key', 'servermsgwelcome')
            ->orWhere('key', 'downloadquota')
            ->orWhere('key', 'uploadquota')
            ->orWhere('key', 'downloadbandwidth')
            ->orWhere('key', 'uploadbandwidth');
    }
}