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

function companyToArray($c)
{
    return [
        'id' => $c->getId(),
        'agencyCompanyId' => $c->getAgencyCompanyId(),
        'companyName' => $c->getCompanyName(),
        'companyAddress1' => $c->getCompanyAddress1(),
        'companyAddress2' => $c->getCompanyAddress2(),
        'companyCity' => $c->getCompanyCity(),
        'companyState' => $c->getCompanyState(),
        'companyZip' => $c->getCompanyZip(),
        'companyPhone' => $c->getCompanyPhone(),
        'companyUrl' => $c->getCompanyUrl(),
        'lastContacted' => $c->getLastContacted(),
        'created' => $c->getCreated(),
        'updated' => $c->getUpdated(),
    ];
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $id = Tools::param('id');
        $name = Tools::param('name');
        try {
            $companyController = new CompanyController('read');
            if ($id !== '') {
                // Get by ID: GET api/companies.php?id=X
                $company = $companyController->get($id);
                if ($company === null) {
                    http_response_code(404);
                    echo json_encode(['result' => 'FAILED', 'error' => 'Company not found']) . PHP_EOL;
                    exit(0);
                }
                echo json_encode([
                    'result' => 'OK',
                    'company' => companyToArray($company),
                ]) . PHP_EOL;
            } elseif ($name !== '') {
                // Find by name: GET api/companies.php?name=X
                $companies = $companyController->getByName($name);
                $results = [];
                foreach ($companies as $c) {
                    $results[] = companyToArray($c);
                }
                echo json_encode([
                    'result' => 'OK',
                    'count' => count($results),
                    'companies' => $results,
                ]) . PHP_EOL;
            } else {
                // List all: GET api/companies.php
                $companies = $companyController->getAll();
                $results = [];
                foreach ($companies as $c) {
                    $results[] = companyToArray($c);
                }
                echo json_encode([
                    'result' => 'OK',
                    'count' => count($results),
                    'companies' => $results,
                ]) . PHP_EOL;
            }
        } catch (ControllerException $e) {
            http_response_code(500);
            echo json_encode(['result' => 'FAILED', 'error' => $e->getMessage()]) . PHP_EOL;
        }
        break;

    case 'POST':
        // Create company: POST api/companies.php with JSON body
        ApiAuth::populateRequestFromJson();
        $companyName = Tools::param('companyName');

        if ($companyName === '') {
            http_response_code(400);
            echo json_encode(['result' => 'FAILED', 'error' => 'companyName is required']) . PHP_EOL;
            exit(0);
        }

        try {
            $companyModel = new CompanyModel();
            $companyModel->setCompanyName($companyName);
            $companyModel->setAgencyCompanyId(Tools::param('agencyCompanyId') ?: null);
            $companyModel->setCompanyAddress1(Tools::param('companyAddress1'));
            $companyModel->setCompanyAddress2(Tools::param('companyAddress2'));
            $companyModel->setCompanyCity(Tools::param('companyCity'));
            $companyModel->setCompanyState(Tools::param('companyState'));
            $companyModel->setCompanyZip(Tools::param('companyZip'));
            $companyModel->setCompanyPhone(Tools::param('companyPhone'));
            $companyModel->setCompanyUrl(Tools::param('companyUrl'));

            $companyController = new CompanyController();
            $newId = $companyController->add($companyModel);

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

    case 'PUT':
        // Update company: PUT api/companies.php with JSON body (id required)
        ApiAuth::populateRequestFromJson();
        $id = Tools::param('id');
        if ($id === '') {
            http_response_code(400);
            echo json_encode(['result' => 'FAILED', 'error' => 'id is required']) . PHP_EOL;
            exit(0);
        }
        try {
            $companyController = new CompanyController();
            $companyModel = $companyController->get($id);
            if ($companyModel === null) {
                http_response_code(404);
                echo json_encode(['result' => 'FAILED', 'error' => 'Company not found']) . PHP_EOL;
                exit(0);
            }
            $companyModel->setAgencyCompanyId(Tools::param('agencyCompanyId') ?: null);
            $companyModel->setCompanyName(Tools::param('companyName'));
            $companyModel->setCompanyAddress1(Tools::param('companyAddress1'));
            $companyModel->setCompanyAddress2(Tools::param('companyAddress2'));
            $companyModel->setCompanyCity(Tools::param('companyCity'));
            $companyModel->setCompanyState(Tools::param('companyState'));
            $companyModel->setCompanyZip(Tools::param('companyZip'));
            $companyModel->setCompanyPhone(Tools::param('companyPhone'));
            $companyModel->setCompanyUrl(Tools::param('companyUrl'));
            $companyModel->setLastContacted(Tools::param('lastContacted'));
            $result = $companyController->update($companyModel);
            if (! ($result > 0)) {
                throw new ControllerException("Update failed.");
            }
            $companyModel = $companyController->get($id);
            echo json_encode(['result' => 'OK', 'company' => companyToArray($companyModel)]) . PHP_EOL;
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
