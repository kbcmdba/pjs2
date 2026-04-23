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

function noteToArray($n)
{
    return [
        'id' => $n->getId(),
        'appliesToTable' => $n->getAppliesToTable(),
        'appliesToId' => $n->getAppliesToId(),
        'noteText' => $n->getNoteText(),
        'created' => $n->getCreated(),
        'updated' => $n->getUpdated(),
    ];
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $id = Tools::param('id');
        $appliesToTable = Tools::param('appliesToTable');
        $appliesToId = Tools::param('appliesToId');
        try {
            $noteController = new NoteController('read');
            if ($id !== '') {
                // Get by ID: GET api/notes.php?id=X
                $note = $noteController->get($id);
                if ($note === null) {
                    http_response_code(404);
                    echo json_encode(['result' => 'FAILED', 'error' => 'Note not found']) . PHP_EOL;
                    exit(0);
                }
                echo json_encode([
                    'result' => 'OK',
                    'note' => noteToArray($note),
                ]) . PHP_EOL;
            } elseif ($appliesToTable !== '' && $appliesToId !== '') {
                // List by entity: GET api/notes.php?appliesToTable=job&appliesToId=123
                $notes = $noteController->getByTableAndId($appliesToTable, $appliesToId);
                $results = [];
                foreach ($notes as $n) {
                    $results[] = noteToArray($n);
                }
                echo json_encode([
                    'result' => 'OK',
                    'count' => count($results),
                    'notes' => $results,
                ]) . PHP_EOL;
            } else {
                http_response_code(400);
                echo json_encode(['result' => 'FAILED', 'error' => 'Provide id or appliesToTable+appliesToId']) . PHP_EOL;
            }
        } catch (ControllerException $e) {
            http_response_code(500);
            echo json_encode(['result' => 'FAILED', 'error' => $e->getMessage()]) . PHP_EOL;
        }
        break;

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

    case 'PUT':
        // Update note: PUT api/notes.php with JSON body (id required)
        ApiAuth::populateRequestFromJson();
        $id = Tools::param('id');
        if ($id === '') {
            http_response_code(400);
            echo json_encode(['result' => 'FAILED', 'error' => 'id is required']) . PHP_EOL;
            exit(0);
        }
        try {
            $noteController = new NoteController();
            $noteModel = $noteController->get($id);
            if ($noteModel === null) {
                http_response_code(404);
                echo json_encode(['result' => 'FAILED', 'error' => 'Note not found']) . PHP_EOL;
                exit(0);
            }
            $noteModel->setNoteText(Tools::param('noteText'));
            // validateForUpdate requires appliesToTable and appliesToId in $_REQUEST
            // even though they're immutable. Populate from the existing model.
            if (! isset($_REQUEST['appliesToTable'])) {
                $_REQUEST['appliesToTable'] = $noteModel->getAppliesToTable();
            }
            if (! isset($_REQUEST['appliesToId'])) {
                $_REQUEST['appliesToId'] = $noteModel->getAppliesToId();
            }
            $noteController->update($noteModel);
            echo json_encode(['result' => 'OK', 'note' => noteToArray($noteModel)]) . PHP_EOL;
        } catch (ControllerException $e) {
            http_response_code(400);
            echo json_encode(['result' => 'FAILED', 'error' => $e->getMessage()]) . PHP_EOL;
        }
        break;

    default:
        http_response_code(405);
        header('Allow: GET, POST, PUT');
        echo json_encode(['result' => 'FAILED', 'error' => 'Method not allowed']) . PHP_EOL;
        break;
}
