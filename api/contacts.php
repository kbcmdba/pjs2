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

function contactToArray($c)
{
    return [
        'id' => $c->getId(),
        'contactCompanyId' => $c->getContactCompanyId(),
        'contactName' => $c->getContactName(),
        'contactEmail' => $c->getContactEmail(),
        'contactPhone' => $c->getContactPhone(),
        'contactAlternatePhone' => $c->getContactAlternatePhone(),
        'lastContacted' => $c->getLastContacted(),
        'created' => $c->getCreated(),
        'updated' => $c->getUpdated(),
    ];
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $id = Tools::param('id');
        $email = Tools::param('email');
        try {
            $contactController = new ContactController('read');
            if ($id !== '') {
                // Get by ID: GET api/contacts.php?id=X
                $contact = $contactController->get($id);
                if ($contact === null) {
                    http_response_code(404);
                    echo json_encode(['result' => 'FAILED', 'error' => 'Contact not found']) . PHP_EOL;
                    exit(0);
                }
                echo json_encode([
                    'result' => 'OK',
                    'contact' => contactToArray($contact),
                ]) . PHP_EOL;
            } elseif ($email !== '') {
                // Find by email: GET api/contacts.php?email=X
                $contact = $contactController->getByEmail($email);
                if ($contact !== null) {
                    echo json_encode([
                        'result' => 'OK',
                        'found' => true,
                        'contact' => contactToArray($contact),
                    ]) . PHP_EOL;
                } else {
                    echo json_encode(['result' => 'OK', 'found' => false]) . PHP_EOL;
                }
            } else {
                // List all: GET api/contacts.php
                $contacts = $contactController->getAll();
                $results = [];
                foreach ($contacts as $c) {
                    $results[] = contactToArray($c);
                }
                echo json_encode([
                    'result' => 'OK',
                    'count' => count($results),
                    'contacts' => $results,
                ]) . PHP_EOL;
            }
        } catch (ControllerException $e) {
            http_response_code(500);
            echo json_encode(['result' => 'FAILED', 'error' => $e->getMessage()]) . PHP_EOL;
        }
        break;

    case 'POST':
        // Create contact (recruiter): POST api/contacts.php with JSON body
        ApiAuth::populateRequestFromJson();
        $contactName = Tools::param('contactName');
        $contactEmail = Tools::param('contactEmail');

        if ($contactName === '') {
            http_response_code(400);
            echo json_encode(['result' => 'FAILED', 'error' => 'contactName is required']) . PHP_EOL;
            exit(0);
        }

        try {
            $contactModel = new ContactModel();
            $contactModel->setContactName($contactName);
            $contactModel->setContactEmail($contactEmail);
            $contactModel->setContactCompanyId(Tools::param('companyId') ?: null);
            $contactModel->setContactPhone(Tools::param('contactPhone'));
            $contactModel->setContactAlternatePhone(Tools::param('contactAlternatePhone'));

            $contactController = new ContactController();
            $newId = $contactController->add($contactModel);

            if (! ($newId >= 1)) {
                throw new ControllerException("Add failed.");
            }
            http_response_code(201);
            echo json_encode(['result' => 'OK', 'id' => $newId]) . PHP_EOL;
        } catch (ControllerException $e) {
            http_response_code(400);
            echo json_encode(['result' => 'FAILED', 'error' => $e->getMessage()]) . PHP_EOL;
        }
        break;

    default:
        http_response_code(405);
        header('Allow: GET, POST');
        echo json_encode(['result' => 'FAILED', 'error' => 'Method not allowed']) . PHP_EOL;
        break;
}
