<?php

/**
 * phpjobseeker
 *
 * Copyright (C) 2009, 2015, 2017, 2026 Kevin Benton - kbenton at bentonfam dot org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 */
namespace com\kbcmdba\pjs2;

/**
 * API authentication helper for REST API endpoints.
 *
 * Validates requests using an API key passed in the X-API-Key header,
 * compared against the apiKey value in config.xml.
 */
class ApiAuth
{

    /**
     * Validate the API key from the request header.
     *
     * @return boolean
     */
    public static function validate(): bool
    {
        $config = new Config();
        $apiKey = $config->getApiKey();
        if ($apiKey === '' || $apiKey === null) {
            return false;
        }
        $provided = $_SERVER['HTTP_X_API_KEY'] ?? '';
        return hash_equals($apiKey, $provided);
    }

    /**
     * Require valid API key or return 401 and exit.
     *
     * @return void
     */
    public static function requireAuth(): void
    {
        if (! self::validate()) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['result' => 'FAILED', 'error' => 'Invalid or missing API key']) . PHP_EOL;
            exit(0);
        }
    }

    /**
     * Populate $_REQUEST and $_POST from a JSON request body.
     *
     * This allows existing model validateForAdd()/validateForUpdate() methods
     * that read from Tools::param() (which reads $_REQUEST) to work with
     * JSON API requests without modification.
     *
     * @return void
     */
    public static function populateRequestFromJson(): void
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $body = json_decode(file_get_contents('php://input'), true);
            if (is_array($body)) {
                foreach ($body as $key => $value) {
                    $_REQUEST[$key] = $value;
                    $_POST[$key] = $value;
                }
            }
        }
    }
}
