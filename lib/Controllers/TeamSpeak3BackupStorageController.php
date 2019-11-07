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
use Throwable;
use WHMCS\Module\Addon\TeamSpeak3\Abstracts\CompressManagerFactoryAbstract;
use WHMCS\Module\Addon\TeamSpeak3\Abstracts\CompressMethodAbstract;
use WHMCS\Module\Addon\TeamSpeak3\Configs\ModuleConfig;
use WHMCS\Module\Addon\TeamSpeak3\Exceptions\NotExistDirException;

class TeamSpeak3BackupStorageController
{
    private $localStorage;

    /**
     * TeamSpeak3BackupStorageController constructor.
     */
    function __construct()
    {
        $this->localStorage = new LocalStorageController(ModuleConfig::getBackupPath());

        if (!$this->localStorage->isExitsDir('/icons')) {
            $this->localStorage->mkdir('/icons');
        }

        if (!$this->localStorage->isExitsDir('/backups')) {
            $this->localStorage->mkdir('/backups');
        }

        if (!$this->localStorage->isExits('/icons.json')) {
            $this->localStorage->put('/icons.json', '{}');
        }
    }

    /**
     * @return string
     */
    public function getIconCacheFile(): string
    {
        return $this->localStorage->get('/icons.json');
    }

    /**
     * @return Collection
     */
    public function getUidList()
    {
        try {
            return collect($this->localStorage->getFileList('/backups'));
        } catch (NotExistDirException $e) {
            return collect([]);
        }
    }

    /**
     * @param array $cacheList
     * @throws Throwable
     */
    public function putIconCacheFile(array $cacheList): void
    {
        try {
            $this->localStorage->put('/icons.json', json_encode($cacheList));
        } catch (Throwable $e) {
            echo 'error->' . $e->getMessage();
            throw  $e;
        }
    }

    /**
     * @param string $icon
     * @throws Throwable
     */
    public function putIcon(string $icon): void
    {
        try {
            $this->localStorage->put('/icons/' . crc32($icon), $icon);
        } catch (Throwable $e) {
            echo 'error->' . $e->getMessage() . PHP_EOL;
            throw $e;
        }
    }

    /**
     * @param string $uid
     * @param string $backup
     * @param string $tag
     * @param int $keepDays
     * @param string $compressMethod
     * @throws Throwable
     */
    public function putBackup(string $uid, string $backup, string $tag, int $keepDays, string $compressMethod): void
    {
        $path = '/backups/' . base64_encode($uid) . '/' . $tag;
        $date = date('Y-m-d_H-i');
        $compressManager = CompressManagerFactoryAbstract::create($compressMethod);


        if (!$this->localStorage->isExitsDir($path)) {
            $this->localStorage->mkdir($path);
        }

        try {
            $this->localStorage->put(
                $path . '/' . $date . '.json' . $compressManager->getFileExtension(),
                $compressManager->compressString($backup)
            );
            $this->localStorage->put(
                $path . '/' . $date . '.json.keep', $keepDays
            );
        } catch (Throwable $e) {
            echo 'error->' . $e->getMessage() . PHP_EOL;
            throw $e;
        }
    }

    /**
     * @param string $uid
     * @param string $tag
     * @param string $backupDate
     * @return string
     * @throws Exception
     */
    public function getBackup(string $uid, string $tag, string $backupDate): string
    {
        $path = '/backups/' . base64_encode($uid) . '/' . $tag . '/';
        try {
            $fileList = collect($this->localStorage->getFileList($path));

            $backupFile = $fileList->filter(function ($fileName) use ($backupDate) {
                return strpos($fileName, $backupDate) !== false
                    && strpos($fileName, 'keep') == false;
            })->first();

            $fileExtension = pathinfo($backupFile, PATHINFO_EXTENSION);

            $backup = $this->localStorage->get($path . $backupFile);

            if (CompressMethodAbstract::isValidExtension($fileExtension)) {
                $compressManager = CompressManagerFactoryAbstract::create(
                    CompressMethodAbstract::getMethodForExtension($fileExtension)
                );
                $backup = $compressManager->decompressString($backup);
            }

            return $backup;
        } catch (Exception $e) {
            echo 'error->' . $e->getMessage() . PHP_EOL;
            throw $e;
        }
    }

    /**
     * @param int $crc32
     * @return string
     * @throws Exception
     */
    public function getIcon(int $crc32): string
    {
        try {
            return $this->localStorage->get('/icons/' . $crc32);
        } catch (Exception $e) {
            echo 'error->' . $e->getMessage() . PHP_EOL;
            throw $e;
        }
    }

    /**
     * @param $uid
     * @return Collection
     */
    public function getBackupList(string $uid): Collection
    {
        $path = '/backups/' . base64_encode($uid);
        $backupList = collect();
        try {
            $ListPrefix = collect($this->localStorage->getFileList($path));
        } catch (NotExistDirException $e) {
            return $backupList;
        }

        $ListPrefix->each(function (string $pathPrefix) use ($path, $backupList) {
            $prefix = str_replace($path . '/', '', $pathPrefix);
            $backupList->put($prefix, collect());
        });

        $backupList->transform(function (Collection $list, string $prefix) use ($path) {
            $fileList = collect($this->localStorage->getFileList($path . '/' . $prefix));

            return collect($fileList->filter(function (string $value) {
                return strpos($value, '.json.keep') === false;
            })->transform(function ($backupPath) use ($path, $prefix) {
                $fileExtension = pathinfo($backupPath, PATHINFO_EXTENSION);

                if (CompressMethodAbstract::isValidExtension($fileExtension)) {
                    $backupPath = str_replace('.' . $fileExtension, '', $backupPath);
                }

                return Carbon::createFromFormat('Y-m-d_H-i', str_replace(
                    '.json',
                    '',
                    str_replace($path . '/' . $prefix . '/', '', $backupPath),
                    ));
            })->values());
        });

        return $backupList;
    }

    /**
     * @param string $uid
     */
    public function removeEmptyDirTag(string $uid): void
    {
        $path = '/backups/' . base64_encode($uid);

        try {
            $ListPrefix = collect($this->localStorage->getFileList($path));
        } catch (NotExistDirException $e) {
            return;
        }

        $ListPrefix->each(function (string $pathPrefix) {
            if ($this->localStorage->count($pathPrefix) === 0) {
                $this->localStorage->rmdir($pathPrefix);
            }
        });
    }

    /**
     * @param string $uid
     * @param string $tag
     * @param string $backupDate
     * @return Carbon
     * @throws Throwable
     */
    public function getExpireBackupInfo(string $uid, string $tag, string $backupDate): Carbon
    {
        try {
            $path = '/backups/' . base64_encode($uid) . '/' . $tag . '/' . $backupDate . '.json.keep';

            $keepDay = (int)$this->localStorage->get($path);

            return Carbon::createFromFormat('Y-m-d_H-i', $backupDate)->addDays($keepDay);
        } catch (Throwable $e) {
            echo 'error->' . $e->getMessage() . PHP_EOL;
            throw $e;
        }
    }

    /**
     * @param string $uid
     * @param string $tag
     * @param string $backupDate
     * @throws Throwable
     */
    public function deleteBackup(string $uid, string $tag, string $backupDate): void
    {
        $path = '/backups/' . base64_encode($uid) . '/' . $tag . '/';
        try {
            $pathKeep = $path . $backupDate . '.json.keep';

            $fileList = collect($this->localStorage->getFileList($path));

            $backupFile = $path . $fileList->filter(function ($fileName) use ($backupDate) {
                    return strpos($fileName, $backupDate) !== false
                        && strpos($fileName, 'keep') == false;
                })->first();

            $this->localStorage->delete($backupFile);
            $this->localStorage->delete($pathKeep);
        } catch (Throwable $e) {
            echo 'error->' . $e->getMessage() . PHP_EOL;
            throw $e;
        }
    }
}