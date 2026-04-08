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
        // Duplicate check by URL: GET api/jobs.php?url=X
        $url = Tools::param('url');
        if ($url === '') {
            http_response_code(400);
            echo json_encode(['result' => 'FAILED', 'error' => 'url parameter is required']) . PHP_EOL;
            exit(0);
        }
        try {
            $jobController = new JobController('read');
            $job = $jobController->getByUrl($url);
            if ($job !== null) {
                $companyName = '';
                if ($job->getCompanyId()) {
                    $cc = new CompanyController('read');
                    $cm = $cc->get($job->getCompanyId());
                    if ($cm) {
                        $companyName = $cm->getCompanyName();
                    }
                }
                echo json_encode([
                    'result' => 'OK',
                    'found' => true,
                    'job' => [
                        'id' => $job->getId(),
                        'positionTitle' => $job->getPositionTitle(),
                        'companyId' => $job->getCompanyId(),
                        'companyName' => $companyName,
                        'url' => $job->getUrl(),
                    ],
                ]) . PHP_EOL;
            } else {
                echo json_encode(['result' => 'OK', 'found' => false]) . PHP_EOL;
            }
        } catch (ControllerException $e) {
            http_response_code(500);
            echo json_encode(['result' => 'FAILED', 'error' => $e->getMessage()]) . PHP_EOL;
        }
        break;

    case 'POST':
        // Create job: POST api/jobs.php with JSON body
        ApiAuth::populateRequestFromJson();
        $primaryContactId = Tools::param('primaryContactId');
        $companyId = Tools::param('companyId');
        $applicationStatusId = Tools::param('applicationStatusId');
        $lastStatusChange = Tools::param('lastStatusChange');
        $urgency = Tools::param('urgency');
        $nextActionDue = Tools::param('nextActionDue');
        $nextAction = Tools::param('nextAction');
        $positionTitle = Tools::param('positionTitle');
        $location = Tools::param('location');
        $url = Tools::param('url');

        try {
            $jobModel = new JobModel();
            $jobModel->setPrimaryContactId($primaryContactId ?: null);
            $jobModel->setCompanyId($companyId ?: null);
            $jobModel->setApplicationStatusId($applicationStatusId);
            $jobModel->setLastStatusChange($lastStatusChange);
            $jobModel->setUrgency($urgency);
            $jobModel->setNextActionDue($nextActionDue);
            $jobModel->setNextAction($nextAction);
            $jobModel->setPositionTitle($positionTitle);
            $jobModel->setLocation($location);
            $jobModel->setUrl($url);

            $jobController = new JobController();
            if ($url !== '' && $url !== null) {
                $existingJob = $jobController->getByUrl($url);
                if ($existingJob !== null) {
                    http_response_code(409);
                    echo json_encode([
                        'result' => 'FAILED',
                        'error' => 'Duplicate URL. Already exists as job #' . $existingJob->getId(),
                        'existingJobId' => $existingJob->getId(),
                    ]) . PHP_EOL;
                    exit(0);
                }
            }
            $jobId = $jobController->add($jobModel);

            if (! ($jobId >= 1)) {
                throw new ControllerException("Add failed.");
            }
            http_response_code(201);
            echo json_encode(['result' => 'OK', 'id' => $jobId]) . PHP_EOL;
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
