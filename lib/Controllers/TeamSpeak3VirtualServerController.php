<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 08.09.19 14:39
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Controllers;

use Exception;
use Illuminate\Support\Collection;
use TeamSpeak3;
use TeamSpeak3_Adapter_FileTransfer;
use TeamSpeak3_Adapter_FileTransfer_Exception;
use TeamSpeak3_Adapter_ServerQuery_Exception;
use TeamSpeak3_Node_Server;
use TeamSpeak3_Transport_Exception;

/**
 * Class TeamSpeak3VirtualServerController
 * @package WHMCS\Module\Addon\TeamSpeak3\Controllers
 */
class TeamSpeak3VirtualServerController extends TeamSpeak3_Node_Server
{
    /**
     * Returns a list of privilege keys (tokens) available. If $resolve is set to TRUE the values
     * of token_id1 and token_id2 will be translated into the appropriate group and/or channel
     * names.
     *
     * @param bool $resolve
     * @return array
     * @throws Exception
     */
    public function privilegeKeyList($resolve = FALSE): Collection
    {
        try {
            $privilegeKeys = collect(parent::privilegeKeyList($resolve));
        } catch (\TeamSpeak3_Adapter_ServerQuery_Exception $e) {
            if ($e->getMessage() !== 'database empty result set') {
                throw  $e;
            }
            $privilegeKeys = collect();
        }

        $privilegeKeys->transform(function ($val) {
            $val['token'] = (string)$val['token'];
            $val['token_description'] = (string)$val['token_description'];

            if (!is_int($val['token_id1'])) {
                $val['token_id1'] = (string)$val['token_id1'];
            }

            if (!is_int($val['token_id2'])) {
                $val['token_id2'] = (string)$val['token_id2'];
            }

            return $val;
        });

        return $privilegeKeys;
    }

    /**
     * Returns a list of active bans on the selected virtual server.
     * @return Collection
     * @throws \TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public function banList(): Collection
    {
        try {
            $bans = collect(parent::banList());
        } catch (\TeamSpeak3_Adapter_ServerQuery_Exception $e) {
            if ($e->getMessage() !== 'database empty result set') {
                throw  $e;
            }
            $bans = collect();
        }

        return $bans;
    }

    /**
     * Stops the virtual server.
     *
     * @param string $msg
     * @return $this
     */
    public function stop($msg = null): TeamSpeak3VirtualServerController
    {
        if ($this->isOnline())
            parent::stop($msg);

        return $this;
    }

    /**
     * Starts the virtual server.
     *
     * @return $this
     */
    public function start(): TeamSpeak3VirtualServerController
    {
        if (!$this->isOnline())
            parent::start();
        return $this;
    }

    /**
     * @param $slots
     * @return $this
     */
    public function changeSlots(int $slots): TeamSpeak3VirtualServerController
    {
        $this->modify(['virtualserver_maxclients' => $slots]);
        return $this;
    }

    /**
     * @return TeamSpeak3VirtualServerController
     */
    public function enableAutoStart(): TeamSpeak3VirtualServerController
    {
        $this->modify(['virtualserver_autostart' => 1]);
        return $this;
    }

    /**
     * @return TeamSpeak3VirtualServerController
     */
    public function disableAutoStart(): TeamSpeak3VirtualServerController
    {
        $this->modify(['virtualserver_autostart' => 0]);
        return $this;
    }

    /**
     * @return Collection
     * @throws Exception
     */
    public function getIconList(): Collection
    {
        try {
            $iconList = collect($this->channelFileList(0, '', '/icons'))->keyBy(function ($item) {
                return substr($item['name'], 5);
            });

            return $iconList;
        } catch (Exception $e) {
            if ($e->getMessage() === 'database empty result set') {
                return collect([]);
            }
            throw  $e;
        }
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        return (string)$this->virtualserver_unique_identifier;
    }

    /**
     * @param $iconIdList
     * @return Collection
     * @throws TeamSpeak3_Adapter_FileTransfer_Exception
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public function getIconsFromServer(Collection $iconIdList): Collection
    {
        /**
         * @var $transfer TeamSpeak3_Adapter_FileTransfer
         */
        $transfer = null;
        $iconIdList = $iconIdList->flip();
        $iconIdList->transform(function ($icon, $crc) use ($transfer) {
            $download = $this->transferInitDownload(rand(0x0000, 0xFFFF), 0, '/icon_' . $crc);

            if ($transfer === null) {
                $transfer = TeamSpeak3::factory("filetransfer://" . $download["host"] . ":" . $download["port"]);
            }

            try {
                return $transfer->download($download["ftkey"], $download["size"])->toString();
            } catch (TeamSpeak3_Transport_Exception $e) {
                //todo реализовать свой класс file transfer с возможностью повторить загрузку файла в случае ошибки
                echo 'error->' . $e->getMessage() . PHP_EOL;
                sleep(5);
                $transfer->syn();
                return $transfer->download($download["ftkey"], $download["size"])->toString();
            }
        });

        return $iconIdList;
    }

    /**
     * Uploads a given icon file content to the server and returns the ID of the icon.
     *
     * @param string $data
     * @return integer
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public function iconUpload($data)
    {
        $crc = crc32($data);
        $size = strlen($data);

        $upload = $this->transferInitUpload(rand(0x0000, 0xFFFF), 0, "/icon_" . $crc, $size);
        $transfer = TeamSpeak3::factory("filetransfer://" . (strstr($upload["host"], ":") !== FALSE ? "[" . $upload["host"] . "]" : $upload["host"]) . ":" . $upload["port"]);

        $transfer->upload($upload["ftkey"], $upload["seekpos"], $data);
        $transfer->getTransport()->disconnect();

        return $crc;
    }

    public function getSlots(): int
    {
        return $this->virtualserver_maxclients;
    }

    public function getOnline(): int
    {
        return $this->virtualserver_clientsonline;
    }
}