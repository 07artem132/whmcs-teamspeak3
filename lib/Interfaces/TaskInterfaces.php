<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 11.09.19 21:25
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Interfaces;


interface TaskInterfaces
{
    public function getName(): string;

    public function run(): void;

    public function getFrequency(): string;

}