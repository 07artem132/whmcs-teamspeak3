<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 08.09.19 16:03
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Abstracts;


abstract class ApiValidatorAbstract
{
    abstract function rules(): array;

    private $errorMessages = [];

    protected function validate(): array
    {
        foreach ($this->rules() as $key => $rules) {
            foreach ($rules as $rule) {
                if (!empty($error = $this->$rule($key))) {
                    $this->errorMessages[$key][] = $error;
                }
            }
        }

        return $this->errorMessages;
    }

    function required($key): ?string
    {
        if (array_key_exists($key, $_REQUEST)) {
            return null;
        }

        return sprintf('error parameter %s not faund', $key);
    }
}