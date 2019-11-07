<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 27.09.19 17:56
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Interfaces;

interface CompressInterface
{
    /**
     * @param string $filename
     * @param string $mode
     */
    public function open(string $filename, string $mode);

    /**
     * @param string $data
     * @param int $level
     * @return string
     */
    public function compressString(string $data, int $level = 9): string;

    /**
     * @param string $data
     * @return string
     */
    public function decompressString(string $data): string;

    /**
     * @param string $str
     * @return mixed
     */
    public function write(string $str);

    /**
     * @return mixed
     */
    public function close();

    /**
     * @return string
     */
    public function getFileExtension(): string;

}