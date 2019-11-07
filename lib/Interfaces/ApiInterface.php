<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 06.09.19 23:19
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Interfaces;

interface  ApiInterface
{
    /**
     * @return string
     */
    function validateRequestParameters(): ?array;

    function run(): void;

    function isAuth(): bool;
}