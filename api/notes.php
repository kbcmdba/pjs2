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

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Create note: POST api/notes.php with JSON body
        ApiAuth::populateRequestFromJson();
        $appliesToTable = Tools::param('appliesToTable');
        $appliesToId = Tools::param('appliesToId');
        $noteText = Tools::param('noteText');

        try {
            $noteModel = new NoteModel();
            $noteModel->setAppliesToTable($appliesToTable);
            $noteModel->setAppliesToId($appliesToId);
            $noteModel->setNoteText($noteText);

            $noteController = new NoteController();
            $newId = $noteController->add($noteModel);

            http_response_code(201);
            echo json_encode(['result' => 'OK', 'id' => $newId]) . PHP_EOL;
        } catch (ControllerException $e) {
            http_response_code(400);
            echo json_encode(['result' => 'FAILED', 'error' => $e->getMessage()]) . PHP_EOL;
        }
        break;

    default:
        http_response_code(405);
        header('Allow: POST');
        echo json_encode(['result' => 'FAILED', 'error' => 'Method not allowed']) . PHP_EOL;
        break;
}
