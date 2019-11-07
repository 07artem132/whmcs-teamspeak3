<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 25.08.19 22:22
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Controllers;

use TeamSpeak3;
use TeamSpeak3_Adapter_ServerQuery_Exception;
use TeamSpeak3_Node_Server;
use WHMCS\Module\Addon\TeamSpeak3\Configs\ModuleConfig;

class TeamSpeakTemplateController
{
    private static $groupFilter = [
        'type' => 1
    ];

    /**
     * @param TeamSpeak3_Node_Server $server
     * @param bool $guest
     * @return array
     */
    public static function CreateGroupTemplate(TeamSpeak3_Node_Server $server, $guest = false): array
    {
        $guestID = $server->virtualserver_default_server_group;

        foreach ($server->serverGroupList(self::$groupFilter) as $serverGroup) {
            if ($guest == false && $serverGroup->getId() == $guestID) {
                continue;
            }

            $json[$serverGroup->getId()]['permList'] = self::FixPermSid($serverGroup->permList(true));
            $json[$serverGroup->getId()]['name'] = (string)$serverGroup['name'];

            if (!empty($json[$serverGroup->getId()]['permList']['i_icon_id'])) {
                $json[$serverGroup->getId()]['icon'] = $serverGroup->iconDownload()->toBase64();
            }

            if ($serverGroup->getId() == $guestID) {
                $json[$serverGroup->getId()]['defaultServerGroup'] = true;
            }

            $json[$serverGroup->getId()]['permList'] = array_values($json[$serverGroup->getId()]['permList']);
        }

        return array_values($json);
    }

    /**
     * @param TeamSpeak3_Node_Server $server
     * @return array
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public static function CreateChannelTemplate(TeamSpeak3_Node_Server $server): array
    {
        foreach ($server->channelList() as $ts3_Channel) {
            $channel_name = (string)$ts3_Channel['channel_name'];

            $json[$channel_name]['channel_name'] = $channel_name;
            $json[$channel_name]['channel_topic'] = $ts3_Channel['channel_topic'];
            $json[$channel_name]['channel_description'] = $ts3_Channel['channel_description'];
            $json[$channel_name]['channel_needed_talk_power'] = $ts3_Channel['channel_needed_talk_power'];
            $json[$channel_name]['channel_codec_quality'] = $ts3_Channel['channel_codec_quality'];
            $json[$channel_name]['channel_codec'] = $ts3_Channel['channel_codec'];
            $json[$channel_name]['channel_flag_semi_permanent'] = $ts3_Channel['channel_flag_semi_permanent'];
            $json[$channel_name]['channel_flag_permanent'] = $ts3_Channel['channel_flag_permanent'];
            $json[$channel_name]['channel_flag_default'] = $ts3_Channel['channel_flag_default'];
            $json[$channel_name]['channel_name_phonetic'] = $ts3_Channel['channel_name_phonetic'];
            $json[$channel_name]['channel_maxclients'] = $ts3_Channel['channel_maxclients'];
            $json[$channel_name]['channel_maxfamilyclients'] = $ts3_Channel['channel_maxfamilyclients'];
            $json[$channel_name]['total_clients_family'] = $ts3_Channel['total_clients_family'];
            $json[$channel_name]['total_clients'] = $ts3_Channel['total_clients'];

            $permList = self::FixPermSid($ts3_Channel->permList(true));
            $permList = self::DeleteCidForPerm($permList);

            $json[$channel_name]['permList'] = $permList;

            if (!empty($json[$channel_name]['permList']['i_icon_id']['permvalue'])) {
                $json[$channel_name]['icon'] = base64_encode($ts3_Channel->iconDownload());
            }

            if ($ts3_Channel['pid'] != 0) {
                $json[$channel_name]['parentChannel'] = (string)$server->channelGetById($ts3_Channel['pid'])['channel_name'];
            }

            $json[$channel_name]['permList'] = array_values($permList);

        }

        return array_values($json);
    }

    /**
     * @param TeamSpeak3_Node_Server $server
     * @return array
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public static function CreateFullTemplate(TeamSpeak3_Node_Server $server): array
    {
        $json = [];
        $json['group'] = self::CreateGroupTemplate($server);
        $json['channel'] = self::CreateChannelTemplate($server);

        return $json;
    }

    /**
     * @param array $permList
     * @return array
     */
    private static function FixPermSid(array $permList): array
    {
        array_walk($permList, function (&$val) {
            $val['permsid'] = $val['permsid']->toString();
        });

        return $permList;
    }

    /**
     * @param array $permList
     * @return array
     */
    private static function DeleteCidForPerm(array $permList): array
    {
        array_walk($permList, function (&$val) {
            if (array_key_exists('cid', $val)) {
                unset($val['cid']);
            }
        });

        return $permList;
    }

    /**
     * @param array $permList
     * @return string
     */
    private static function PermListToAssignString(array $permList): string
    {
        $arrayPermString = [];

        foreach ($permList as $permInfo) {
            $arrayPermString[] = sprintf('permsid=%s permvalue=%s permnegated=%s permskip=%s',
                $permInfo['permsid'],
                $permInfo['permvalue'],
                $permInfo['permnegated'],
                $permInfo['permskip']
            );
        };

        return implode('|', $arrayPermString);
    }

    /**
     * @param TeamSpeak3_Node_Server $server
     */
    public static function DeleteAllTokens(TeamSpeak3_Node_Server $server): void
    {
        try {
            $privilegeKeyList = $server->privilegeKeyList();
        } catch (\Exception $e) {
            if ($e->getMessage() === 'database empty result set') {

            }
        }

        foreach ($privilegeKeyList as $token) {
            $server->privilegeKeyDelete($token['token']);
        }
    }

    /**
     * @param TeamSpeak3_Node_Server $server
     */
    public static function DeleteAllGroupsWithoutAGuest(TeamSpeak3_Node_Server $server): void
    {
        $guestID = $server->virtualserver_default_server_group;

        foreach ($server->serverGroupList(self::$groupFilter) as $serverGroup) {
            if ($serverGroup->getId() == $guestID) {
                if ((string)$serverGroup['name'] !== 'DeleteAfterApplyTemplate') {
                    $serverGroup->rename('DeleteAfterApplyTemplate');
                }
                continue;
            }

            $serverGroup->delete(true);
        }

    }

    /**
     * @param TeamSpeak3_Node_Server $server
     */
    public static function DeleteAllChannelWithoutADefault(TeamSpeak3_Node_Server $server): void
    {
        foreach ($server->channelList() as $channel) {
            if ($channel['channel_flag_default'] === 1) {
                if ((string)$channel['channel_name'] !== 'delete after apply template') {
                    $channel->modify(['channel_name' => 'delete after apply template']);
                }
                continue;
            }

            if ($channel['pid'] !== 0) {
                continue;
            }

            $channel->delete(true);
        }
    }

    /**
     * @param TeamSpeak3_Node_Server $server
     */
    public static function DeleteAllIcon(TeamSpeak3_Node_Server $server): void
    {
        try {
            $iconList = $server->channelFileList(0, 0, "/icons");
        } catch (\Exception $e) {
            if ($e->getMessage() === 'database empty result set') {

            }
        }

        foreach ($iconList as $icon) {
            $server->channelFileDelete(0, 0, $icon['src']);
        }
    }

    /**
     * @param TeamSpeak3_Node_Server $server
     * @param int $templateID
     * @param bool $createAdminToken
     * @param bool $applyGuestGroup
     * @return string|null
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public static function ApplyGroupTemplate(TeamSpeak3_Node_Server $server, int $templateID, bool $createAdminToken, bool $applyGuestGroup): ?string
    {
        $templatePath = ModuleConfig::getTeamSpeakTemplatePath('group') . '/' . $templateID . '.json';
        $template = json_decode(file_get_contents($templatePath), true);
        $token = null;

        foreach ($template as $group) {
            $groupID = $server->serverGroupCreate($group['name']);

            $server->request(sprintf(
                'servergroupaddperm sgid=%s %s',
                $groupID,
                self::PermListToAssignString($group['permList'])
            ));

            if (array_key_exists('icon', $group)) {
                try {
                    $server->iconUpload(base64_decode($group['icon']));
                } catch (\Exception $e) {
                    if ($e->getMessage() == 'file already exists') {

                    }
                }
            }

            if (array_key_exists('defaultServerGroup', $group) && $applyGuestGroup === true) {
                $server->virtualserver_default_server_group = $groupID;
                $server->serverGroupGetByName('DeleteAfterApplyTemplate')->delete();
            }
        }

        if ($createAdminToken) {
            $AdminGroupID = $server->serverGroupIdentify(TeamSpeak3::GROUP_IDENTIFIY_STRONGEST);
            $token = $AdminGroupID->privilegeKeyCreate('default token');
        }

        return $token;
    }

    /**
     * @param TeamSpeak3_Node_Server $server
     * @param int $templateID
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public static function ApplyChannelTemplate(TeamSpeak3_Node_Server $server, int $templateID): void
    {
        $templatePath = ModuleConfig::getTeamSpeakTemplatePath('channel') . '/' . $templateID . '.json';
        $template = json_decode(file_get_contents($templatePath), true);

        foreach ($template as $channel) {
            $permList = $channel['permList'];
            unset($channel['permList']);

            if (array_key_exists('icon', $channel)) {
                try {
                    $server->iconUpload(base64_decode($channel['icon']));
                } catch (\Exception $e) {
                    if ($e->getMessage() == 'file already exists') {

                    }
                }
                unset($channel['icon']);
            }

            if (array_key_exists('parentChannel', $channel)) {
                $parentChannel = $server->channelGetByName($channel['parentChannel']);
                $channel['cpid'] = $parentChannel->getId();
                unset($channel['parentChannel']);
            }

            $channelID = $server->channelCreate($channel);

            $server->request(sprintf(
                'channeladdperm cid=%s %s',
                $channelID,
                self::PermListToAssignString($permList)
            ));
        }

        $server->channelGetByName('delete after apply template')->delete(true);
    }

    /**
     * @param TeamSpeak3_Node_Server $server
     * @param int $templateID
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public static function ApplyFullTemplate(TeamSpeak3_Node_Server $server, int $templateID): void
    {
        $templatePath = ModuleConfig::getTeamSpeakTemplatePath('full') . '/' . $templateID . '.json';
        $template = json_decode(file_get_contents($templatePath), true);

        foreach ($template['channel'] as $channel) {
            $permList = $channel['permList'];
            unset($channel['permList']);

            if (array_key_exists('icon', $channel)) {
                try {
                    $server->iconUpload(base64_decode($channel['icon']));
                } catch (\Exception $e) {
                    if ($e->getMessage() == 'file already exists') {

                    }
                }
                unset($channel['icon']);
            }

            if (array_key_exists('parentChannel', $channel)) {
                $parentChannel = $server->channelGetByName($channel['parentChannel']);
                $channel['cpid'] = $parentChannel->getId();
                unset($channel['parentChannel']);
            }

            $channelID = $server->channelCreate($channel);

            $server->request(sprintf(
                'channeladdperm cid=%s %s',
                $channelID,
                self::PermListToAssignString($permList)
            ));
        }

        $server->channelGetByName('delete after apply template')->delete(true);

        foreach ($template['group'] as $group) {
            $groupID = $server->serverGroupCreate($group['name']);

            $server->request(sprintf(
                'servergroupaddperm sgid=%s %s',
                $groupID,
                self::PermListToAssignString($group['permList'])
            ));

            if (array_key_exists('icon', $group)) {
                try {
                    $server->iconUpload(base64_decode($group['icon']));
                } catch (\Exception $e) {
                    if ($e->getMessage() == 'file already exists') {

                    }
                }
            }

        }
    }
}
