<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 27.09.19 21:25
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Controllers;


use WHMCS\Module\Addon\TeamSpeak3\Exceptions\FileNotRemoveException;
use WHMCS\Module\Addon\TeamSpeak3\Exceptions\NotExistDirException;

/**
 * Class LocalStorageController
 * @package WHMCS\Module\Addon\TeamSpeak3\Controllers
 */
class LocalStorageController
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * LocalStorageController constructor.
     * @param string $basePath
     */
    function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @param string $path
     * @return bool
     */
    function isExits(string $path): bool
    {
        return file_exists($this->basePath . $path);
    }

    /**
     * @param string $path
     * @return bool
     */
    function isExitsDir(string $path): bool
    {
        if (file_exists($this->basePath . $path)) {
            if (is_dir($this->basePath . $path)) {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param string $dir_name
     */
    function mkdir(string $dir_name): void
    {
        mkdir($this->basePath . $dir_name, 0777, true);
    }

    /**
     * @param string $path
     * @return array
     * @throws NotExistDirException
     */
    function getFileList(string $path): array
    {
        if (!$this->isExitsDir($path)) {
            throw new NotExistDirException();
        }

        $files = scandir($this->basePath . $path);

        return array_diff($files, array('.', '..'));
    }

    /**
     * @param string $path
     * @return int
     * @throws NotExistDirException
     */
    function count(string $path): int
    {
        return count($this->getFileList($path));
    }

    /**
     * @param string $path
     * @param string $data
     */
    function put(string $path, string $data): void
    {
        if (file_put_contents($this->basePath . $path, $data) === false) {
            dump('error save->' . $this->basePath . $path);
            //todo throw new error save
        }
    }

    /**
     * @param string $path
     * @return string
     */
    function get(string $path): string
    {
        return file_get_contents($this->basePath . $path);
    }

    /**
     * @param string $path
     * @throws FileNotRemoveException
     */
    function delete(string $path): void
    {
        if (!unlink($this->basePath . $path)) {
            throw  new FileNotRemoveException('error delete file ' . $path);
        }
    }

    /**
     * @param string $path
     */
    function rmdir(string $path): void
    {
        rmdir($path);
    }
}