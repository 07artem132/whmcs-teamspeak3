<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 06.09.19 23:31
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Traits;

trait ResponseTraits
{

    /**
     * Send client message and die
     * @param string $status
     * @param string|null $message
     */
    function response(string $status, string $message = null): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status,
            'message' => $message,
        ]);
        die();
    }

    /**
     * Send client data and die
     * @param string $status
     * @param array $data
     */
    function responseData(string $status, array $data = []): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status,
            'data' => $data,
        ]);
        die();
    }

    /**
     * Send client errors array
     * @param string $status
     * @param array $data
     */
    function responseErrors(string $status, array $data = []): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status,
            'errors' => $data,
        ]);
        die();
    }
}