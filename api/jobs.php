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

function jobToArray($job)
{
    return [
        'id' => $job->getId(),
        'primaryContactId' => $job->getPrimaryContactId(),
        'companyId' => $job->getCompanyId(),
        'applicationStatusId' => $job->getApplicationStatusId(),
        'lastStatusChange' => $job->getLastStatusChange(),
        'urgency' => $job->getUrgency(),
        'nextActionDue' => $job->getNextActionDue(),
        'nextAction' => $job->getNextAction(),
        'positionTitle' => $job->getPositionTitle(),
        'location' => $job->getLocation(),
        'url' => $job->getUrl(),
        'compRangeLow' => $job->getCompRangeLow(),
        'compRangeHigh' => $job->getCompRangeHigh(),
        'created' => $job->getCreated(),
        'updated' => $job->getUpdated(),
    ];
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $id = Tools::param('id');
        $url = Tools::param('url');
        try {
            $jobController = new JobController('read');
            if ($id !== '') {
                // Get by ID: GET api/jobs.php?id=X
                $job = $jobController->get($id);
                if ($job === null) {
                    http_response_code(404);
                    echo json_encode(['result' => 'FAILED', 'error' => 'Job not found']) . PHP_EOL;
                    exit(0);
                }
                echo json_encode([
                    'result' => 'OK',
                    'job' => jobToArray($job),
                ]) . PHP_EOL;
            } elseif ($url !== '') {
                // Duplicate check by URL: GET api/jobs.php?url=X
                $job = $jobController->getByUrl($url);
                if ($job !== null) {
                    echo json_encode([
                        'result' => 'OK',
                        'found' => true,
                        'job' => jobToArray($job),
                    ]) . PHP_EOL;
                } else {
                    echo json_encode(['result' => 'OK', 'found' => false]) . PHP_EOL;
                }
            } else {
                // List all: GET api/jobs.php
                $jobs = $jobController->getAll();
                $results = [];
                foreach ($jobs as $job) {
                    $results[] = jobToArray($job);
                }
                echo json_encode([
                    'result' => 'OK',
                    'count' => count($results),
                    'jobs' => $results,
                ]) . PHP_EOL;
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
        // Default to NOW() if caller didn't provide. Empty string would
        // otherwise become a 0000-00-00 00:00:00 zero-date in the DB and
        // block subsequent inline-edit saves (validator rejects empty
        // dates). Caught 2026-04-26 after JobImporter-style API creates
        // produced un-editable jobs.
        $lastStatusChange = Tools::param('lastStatusChange');
        if ($lastStatusChange === '' || $lastStatusChange === null) {
            $lastStatusChange = date('Y-m-d H:i:s');
        }
        $urgency = Tools::param('urgency');
        $nextActionDue = Tools::param('nextActionDue');
        $nextAction = Tools::param('nextAction');
        $positionTitle = Tools::param('positionTitle');
        $location = Tools::param('location');
        $url = Tools::param('url');
        $compRangeLow = Tools::param('compRangeLow');
        $compRangeHigh = Tools::param('compRangeHigh');

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
            $jobModel->setCompRangeLow($compRangeLow);
            $jobModel->setCompRangeHigh($compRangeHigh);

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

    case 'PUT':
        // Update job: PUT api/jobs.php with JSON body (id required)
        ApiAuth::populateRequestFromJson();
        $id = Tools::param('id');
        if ($id === '') {
            http_response_code(400);
            echo json_encode(['result' => 'FAILED', 'error' => 'id is required']) . PHP_EOL;
            exit(0);
        }
        try {
            $jobController = new JobController();
            $jobModel = $jobController->get($id);
            if ($jobModel === null) {
                http_response_code(404);
                echo json_encode(['result' => 'FAILED', 'error' => 'Job not found']) . PHP_EOL;
                exit(0);
            }
            $url = Tools::param('url');
            if ($url !== '' && $url !== null) {
                $existingJob = $jobController->getByUrl($url);
                if ($existingJob !== null && $existingJob->getId() != $id) {
                    http_response_code(409);
                    echo json_encode([
                        'result' => 'FAILED',
                        'error' => 'Duplicate URL. Already exists as job #' . $existingJob->getId(),
                    ]) . PHP_EOL;
                    exit(0);
                }
            }
            $jobModel->setPrimaryContactId(Tools::param('primaryContactId') ?: null);
            $jobModel->setCompanyId(Tools::param('companyId') ?: null);
            $jobModel->setApplicationStatusId(Tools::param('applicationStatusId'));
            $jobModel->setLastStatusChange(Tools::param('lastStatusChange'));
            $jobModel->setUrgency(Tools::param('urgency'));
            $jobModel->setNextActionDue(Tools::param('nextActionDue'));
            $jobModel->setNextAction(Tools::param('nextAction'));
            $jobModel->setPositionTitle(Tools::param('positionTitle'));
            $jobModel->setLocation(Tools::param('location'));
            $jobModel->setUrl($url);
            $jobModel->setCompRangeLow(Tools::param('compRangeLow') !== '' ? Tools::param('compRangeLow') : null);
            $jobModel->setCompRangeHigh(Tools::param('compRangeHigh') !== '' ? Tools::param('compRangeHigh') : null);
            $result = $jobController->update($jobModel);
            if (! ($result > 0)) {
                throw new ControllerException("Update failed.");
            }
            echo json_encode(['result' => 'OK', 'job' => jobToArray($jobModel)]) . PHP_EOL;
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
