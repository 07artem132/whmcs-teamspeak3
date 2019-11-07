<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 27.09.19 18:03
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use TeamSpeak3;
use Throwable;

class TeamSpeak3BackupController
{
    /**
     * @var TeamSpeak3BackupStorageController
     */
    private $storageController;

    /**
     * @var int[]
     */
    private $iconCacheFile;

    /**
     * @var string
     */
    private $compressMethod;

    // Available compression methods
    const GZIP = 'Gzip';
    const BZIP2 = 'Bzip2';
    const NONE = 'None';

    function __construct($compressMethod = self::NONE)
    {
        $this->compressMethod = $compressMethod;
        $this->storageController = new TeamSpeak3BackupStorageController();
    }

    function loadIconCacheFile(): void
    {
        if (empty($this->iconCacheFile)) {
            $this->iconCacheFile = json_decode(
                $this->storageController->getIconCacheFile(),
                true,
                JSON_THROW_ON_ERROR
            );
        }
    }

    /**
     * @return string[]
     */
    public static function getAllowCompressionMethods(): array
    {
        return [
            self::BZIP2,
            self::GZIP,
            self::NONE
        ];
    }

    /**
     * @param TeamSpeak3VirtualServerController $server
     * @param int $keepDays
     * @param string $tag
     * @throws Throwable
     */
    public function createBackup(TeamSpeak3VirtualServerController $server, int $keepDays, string $tag)
    {
        $uid = $server->getUid();

        $this->loadIconCacheFile();
        $iconList = $server->getIconList();

        $diff = $iconList->keys()->diff($this->iconCacheFile);

        if (!$diff->isEmpty()) {
            $downloadIcons = $server->getIconsFromServer($diff);
            foreach ($downloadIcons as $crc => $icon) {
                $this->storageController->putIcon($icon);
                $this->iconCacheFile[] = $crc;
            }
            $this->storageController->putIconCacheFile($this->iconCacheFile);
        }

        $snapshot = $server->snapshotCreate(TeamSpeak3::SNAPSHOT_HEXDEC);
        $icons = $iconList->keys()->toArray();

        $backup = $this->buildBackup($uid, $snapshot, $icons);

        $this->storageController->putBackup($uid, $backup, $tag, $keepDays, $this->compressMethod);
    }

    /**
     * @param string $uid
     * @param string $tag
     * @param string $backupDate
     * @param bool $downloadAllIcon
     * @return array [
     *  'snapshot' => strong,
     *  'icons' => [
     *     string,
     *      ...
     *   ]
     * ]
     * @throws Exception
     */
    public function getBackup(string $uid, string $tag, string $backupDate, $downloadAllIcon = false): array
    {
        $backup = json_decode(
            $this->storageController->getBackup($uid, $tag, $backupDate),
            true,
            JSON_THROW_ON_ERROR
        );

        if ($downloadAllIcon) {
            foreach ($backup['icons'] as &$icon) {
                $icon = $this->getIcon($icon);
            }
        }

        return $backup;
    }

    /**
     * @param string $uid
     * @param string $tag
     * @param string $backupDate
     * @throws Throwable
     */
    public function removeBackup(string $uid, string $tag, string $backupDate): void
    {
        $this->storageController->deleteBackup($uid, $tag, $backupDate);
    }

    /**
     * @param string $uid
     * @param string $snapshot
     * @param array $icons
     * @return string
     */
    private function buildBackup(string $uid, string $snapshot, array $icons): string
    {
        return json_encode([
            'uid' => $uid,
            'snapshot' => $snapshot,
            'icons' => $icons,
            'created_at' => time()
        ], JSON_THROW_ON_ERROR
        );
    }

    /**
     * @param int $crc32
     * @return string
     * @throws Exception
     */
    public function getIcon(int $crc32): string
    {
        return $this->storageController->getIcon($crc32);
    }


    /**
     * @param string $uid
     * @return Collection
     */
    public function getBackupList(string $uid): Collection
    {
        $rawBackupList = $this->storageController->getBackupList($uid);

        $backupList = collect([]);
        foreach ($rawBackupList as $tag => $backupDates) {
            foreach ($backupDates as $backupDate) {
                $backupList->push(collect([
                    'create_at' => $backupDate,
                    'uid' => $uid,
                    'tag' => $tag,
                    'backupDate' => $backupDate->format('Y-m-d_H-i')
                ]));
            }
        }

        return $backupList;
    }

    /**
     * @param string $uid
     * @param bool $removeLastBackup
     */
    public function removeOldBackup(string $uid, bool $removeLastBackup = false): void
    {
        $this->storageController->removeEmptyDirTag($uid);

        $backupList = $this->getBackupList($uid);

        if ($backupList->count() == 1 && !$removeLastBackup) {
            return;
        }

        if (!$removeLastBackup) {
            $backupList = $backupList->sortBy('create_at')->take($backupList->count() - 1);
        }

        $diffDate = Carbon::now();

        $backupList->each(function (Collection $backupInfo) use ($diffDate) {
            $expire_at = $this->storageController->getExpireBackupInfo(
                $backupInfo->get('uid'),
                $backupInfo->get('tag'),
                $backupInfo->get('backupDate'),
                );
            if ($expire_at->diffInSeconds($diffDate, false) > 0) {
                $this->storageController->deleteBackup(
                    $backupInfo->get('uid'),
                    $backupInfo->get('tag'),
                    $backupInfo->get('backupDate'),
                    );
            }
        });
    }

    /**
     * @param bool $removeLastBackup
     */
    public function removeAllOldBackup(bool $removeLastBackup = false): void
    {
        $uidList = $this->storageController->getUidList();
        foreach ($uidList as $uid) {
            $this->storageController->removeEmptyDirTag($uid);

            $backupList = $this->getBackupList($uid);

            if ($backupList->count() == 1 && !$removeLastBackup) {
                return;
            }

            if (!$removeLastBackup) {
                $backupList = $backupList->sortBy('create_at')->take($backupList->count() - 1);
            }

            $diffDate = Carbon::now();

            $backupList->each(function (Collection $backupInfo) use ($diffDate) {
                $expire_at = $this->storageController->getExpireBackupInfo(
                    $backupInfo->get('uid'),
                    $backupInfo->get('tag'),
                    $backupInfo->get('backupDate'),
                    );
                if ($expire_at->diffInSeconds($diffDate, false) > 0) {
                    $this->storageController->deleteBackup(
                        $backupInfo->get('uid'),
                        $backupInfo->get('tag'),
                        $backupInfo->get('backupDate'),
                        );
                }
            });
        }
    }
}