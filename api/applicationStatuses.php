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

chdir(__DIR__ . '/..');
require_once "Libs/autoload.php";

ApiAuth::requireAuth();
header('Content-Type: application/json; charset=utf-8');

function applicationStatusToArray($s)
{
    return [
        'id' => (int) $s->getId(),
        'statusValue' => $s->getStatusValue(),
        'isActive' => (bool) $s->getIsActive(),
        'sortKey' => (int) $s->getSortKey(),
        'style' => $s->getStyle(),
        'summaryCount' => (int) $s->getSummaryCount(),
        'created' => $s->getCreated(),
        'updated' => $s->getUpdated(),
    ];
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $id = Tools::param('id');
        try {
            $controller = new ApplicationStatusController('read');
            if ($id !== '') {
                // Get by ID: GET api/applicationStatuses.php?id=X
                $status = $controller->get($id);
                if ($status === null) {
                    http_response_code(404);
                    echo json_encode(['result' => 'FAILED', 'error' => 'Application status not found']) . PHP_EOL;
                    exit(0);
                }
                echo json_encode([
                    'result' => 'OK',
                    'applicationStatus' => applicationStatusToArray($status),
                ]) . PHP_EOL;
            } else {
                // List all: GET api/applicationStatuses.php
                $statuses = $controller->getAll();
                $results = [];
                foreach ($statuses as $s) {
                    $results[] = applicationStatusToArray($s);
                }
                echo json_encode([
                    'result' => 'OK',
                    'count' => count($results),
                    'applicationStatuses' => $results,
                ]) . PHP_EOL;
            }
        } catch (ControllerException $e) {
            http_response_code(500);
            echo json_encode(['result' => 'FAILED', 'error' => $e->getMessage()]) . PHP_EOL;
        }
        break;

    default:
        // Read-only endpoint: applicationStatus rows are reference data managed
        // via SQL/migrations (per applicationStatuses.php admin page),
        // not arbitrary user CRUD.
        http_response_code(405);
        header('Allow: GET');
        echo json_encode(['result' => 'FAILED', 'error' => 'Method not allowed (read-only endpoint)']) . PHP_EOL;
        break;
}
