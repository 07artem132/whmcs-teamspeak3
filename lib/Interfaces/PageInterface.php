<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 20.07.19 15:47
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Interfaces;

interface  PageInterface
{
    /**
     * @return string
     */
    public function getTemplateName();

    /**
     * @return array
     */
    public function getVars();

    public function getSubMenu();
}