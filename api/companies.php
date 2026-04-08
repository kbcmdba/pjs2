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
    case 'GET':
        // Find companies by name: GET api/companies.php?name=X
        // Returns all matches (a name may map to multiple locations).
        $name = Tools::param('name');
        if ($name === '') {
            http_response_code(400);
            echo json_encode(['result' => 'FAILED', 'error' => 'name parameter is required']) . PHP_EOL;
            exit(0);
        }
        try {
            $companyController = new CompanyController('read');
            $companies = $companyController->getByName($name);
            $results = [];
            foreach ($companies as $c) {
                $results[] = [
                    'id' => $c->getId(),
                    'companyName' => $c->getCompanyName(),
                    'companyCity' => $c->getCompanyCity(),
                    'companyState' => $c->getCompanyState(),
                    'companyUrl' => $c->getCompanyUrl(),
                ];
            }
            echo json_encode([
                'result' => 'OK',
                'count' => count($results),
                'companies' => $results,
            ]) . PHP_EOL;
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

    default:
        http_response_code(405);
        header('Allow: GET, POST');
        echo json_encode(['result' => 'FAILED', 'error' => 'Method not allowed']) . PHP_EOL;
        break;
}
