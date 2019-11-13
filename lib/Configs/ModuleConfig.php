<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 13.11.2019, 15:34
 *
 */

/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 20.07.19 15:46
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Configs;

use Illuminate\Support\Collection;
use WHMCS\Module\Addon\Setting;
use WHMCS\Module\Addon\TeamSpeak3\Models\SettingsModel;

class ModuleConfig
{
    private static $defaultLanguage = 'russian';
    private static $whmcsRootDir = ROOTDIR;
    private static $moduleName = 'TeamSpeak3';
    private static $keepDaysBackupManual = 10;
    private static $keepDaysBackupAuto = 7;
    private static $keepDaysBackupPreDelete = 360;
    private static $ProductFieldWithAListOfDomains = 'Домен';
    private static $ProductFieldWithASlots = 'Slots';
    private static $ProductFieldWithASubDomain = 'Субдомен';
    private static $powerDnsUrl = 'http://ns01.service-voice.com/api/v1/';
    private static $powerDnsApiKey = '8FVofCuKHICIrC700xCTi4RRb';
    private static $powerDnsTtl = 60;
    private static $backupSyncFtp = true;
    private static $backupFtpIp = "37.187.1.239";
    private static $backupFtpPort = 21;
    private static $backupFtpLogin = 'sv_bill_ts_backaup';
    private static $backupFtpPassword = 'AxMnqQZTM2zK';
    private static $teamSpeak3MinPortRange = 50000;
    private static $teamSpeak3MaxPortRange = 53000;
    private static $domainBlackList = [
        'admin',
        'my',
        'clientarea',
        'pay',
        'public',
        'static',
        'rds',
        'jts3',
        'music',
        'ts3po',
        'ts3',
        'files',
        'promo',
        'www',
        's1',
        's2',
        's3',
        's4',
        's5',
        's6',
        's7',
        's8',
        's9',
        's10',
        's11',
        's12',
        's13',
        's14',
        's15',
        's16',
        's17',
        's18',
        's19',
        's20',
        's21',
        's22',
        's23',
        's24',
        's25',
        's26',
        's27',
        's28',
        's29',
        's30',
        's31',
        's32',
        's33',
        's34',
        's35',
        's36',
        's37',
        's38',
        's39',
        's40',
        's41',
        's42',
        's43',
        's44',
        's45',
        's46',
        's47',
        's48',
        's49',
        's50',
        's51',
        's52',
        's53',
        's54',
        's55',
        's56',
        's57',
        's58',
        's59',
        's60',
        's61',
        's62',
        's63',
        's64',
        's65',
        's66',
        's67',
        's68',
        's69',
        's70',
        's71',
        's72',
        's73',
        's74',
        's75',
        's76',
        's77',
        's78',
        's79',
        's80',
        's81',
        's82',
        's83',
        's84',
        's85',
        's86',
        's87',
        's88',
        's89',
        's90',
        's91',
        's92',
        's93',
        's94',
        's95',
        's96',
        's97',
        's98',
        's99',
        's100',
        'old',
    ];
    private static $subDomainRegex = '/^[a-zA-Z0-9-]+$/';

    /**
     * @return int
     */
    public static function getKeepDaysBackupPreDelete(): int
    {
        return self::$keepDaysBackupPreDelete;
    }

    /**
     * @return int
     */
    public static function getKeepDaysBackupAuto(): int
    {
        return self::$keepDaysBackupAuto;
    }
    /**
     * @return bool
     */
    public static function isBackupSyncFtp(): bool
    {
        return self::$backupSyncFtp;
    }

    /**
     * @return string
     */
    public static function getBackupFtpIp(): string
    {
        return self::$backupFtpIp;
    }

    /**
     * @return int
     */
    public static function getBackupFtpPort(): int
    {
        return self::$backupFtpPort;
    }

    /**
     * @return string
     */
    public static function getBackupFtpLogin(): string
    {
        return self::$backupFtpLogin;
    }

    /**
     * @return string
     */
    public static function getBackupFtpPassword(): string
    {
        return self::$backupFtpPassword;
    }

    /**
     * @return int
     */
    public static function getPowerDnsTtl(): int
    {
        return self::$powerDnsTtl;
    }

    /**
     * @return int
     */
    public static function getTeamSpeak3MinPortRange(): int
    {
        return self::$teamSpeak3MinPortRange;
    }

    /**
     * @return int
     */
    public static function getTeamSpeak3MaxPortRange(): int
    {
        return self::$teamSpeak3MaxPortRange;
    }

    /**
     * @return string
     */
    public static function getProductFieldWithASlots(): string
    {
        return self::$ProductFieldWithASlots;
    }

    /**
     * @return string
     */
    public static function getProductFieldWithASubDomain(): string
    {
        return self::$ProductFieldWithASubDomain;
    }

    /**
     * @return string
     */
    public static function getSubDomainRegex(): string
    {
        return self::$subDomainRegex;
    }

    /**
     * @return array
     */
    public static function getDomainBlackList(): array
    {
        return self::$domainBlackList;
    }

    /**
     * @return string
     */
    public static function getProductFieldWithAListOfDomains(): string
    {
        return self::$ProductFieldWithAListOfDomains;
    }

    /**
     * @return string
     */
    public static function getPowerDnsUrl(): string
    {
        return self::$powerDnsUrl;
    }

    /**
     * @return string
     */
    public static function getPowerDnsApiKey(): string
    {
        return self::$powerDnsApiKey;
    }

    /**
     * @return int
     */
    public static function getKeepDaysBackupManual(): int
    {
        return self::$keepDaysBackupManual;
    }

    /**
     * @return mixed
     */
    public static function getWhmcsRootDir(): string
    {
        return self::$whmcsRootDir;
    }

    /**
     * @return string
     */
    public static function getDefaultLanguage(): string
    {
        return self::$defaultLanguage;
    }

    /**
     * @return string
     */
    public static function getModuleName(): string
    {
        return self::$moduleName;
    }

    public static function getModuleLink(): string
    {
        global $module, $customadminpath;

        return '/' . $customadminpath . '/addonmodules.php?module=' . $module;
    }

    public static function getBaseFullPath(): string
    {
        return self::getWhmcsRootDir() . '/modules/addons/' . self::getModuleName();
    }

    public static function getBackupPath(): string
    {
        return self::getWhmcsRootDir() . '/modules/addons/' . self::getModuleName() . '/backups';
    }

    public static function getStatsPath(): string
    {
        return self::getWhmcsRootDir() . '/modules/addons/' . self::getModuleName() . '/stats';
    }

    public static function getBaseRelativePath(): string
    {
        return '/modules/addons/' . self::getModuleName();
    }

    public static function getTeamSpeakTemplatePath(string $type): ?string
    {
        switch ($type) {
            case 'channel':
                return self::getBaseFullPath() . '/TeamSpeak3Template/channels';
            case 'full':
                return self::getBaseFullPath() . '/TeamSpeak3Template/full';
            case 'group':
                return self::getBaseFullPath() . '/TeamSpeak3Template/groups';
            default:
                return null;
        }
    }

    public static function getSecret()
    {
        global $cc_encryption_hash;

        return sha1($cc_encryption_hash);
    }

    public static function getModuleSetting($setting)
    {
        return Setting::Module(self::getModuleName())
            ->where('setting', '=', $setting)
            ->first()->value;
    }

    /**
     * @return Collection
     */
    public static function getTeamSpeak3ServerDefaultSettings(): Collection
    {
        $settings = SettingsModel::serverDefaultSettings()->get()->keyBy('key');

        return $settings->transform(function ($item) {
            return $item->val;
        });
    }
}