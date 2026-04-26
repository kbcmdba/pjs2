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

require_once "Libs/autoload.php";

$auth = new Auth();
if (! $auth->isAuthorized()) {
    $auth->forbidden();
    exit(0);
}

$id = Tools::param('id');
$jobController = new JobController('read');
$jobModel = $jobController->get($id);

if (! $jobModel) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['result' => 'FAILED', 'error' => 'Job not found']) . PHP_EOL;
    exit(0);
}

$companyName = '---';
$companyId = $jobModel->getCompanyId();
if ($companyId >= 1) {
    $companyController = new CompanyController('read');
    $companyModel = $companyController->get($companyId);
    if ($companyModel) {
        $companyName = $companyModel->getCompanyName();
    }
}

$applicationStatusController = new ApplicationStatusController('read');
$statuses = $applicationStatusController->getAll();
$statusOptions = [];
foreach ($statuses as $status) {
    $statusOptions[] = [
        'id' => $status->getId(),
        'value' => $status->getStatusValue(),
        'isActive' => $status->getIsActive(),
        'style' => $status->getStyle()
    ];
}

$result = [
    'result' => 'OK',
    'job' => [
        'id' => $jobModel->getId(),
        'positionTitle' => $jobModel->getPositionTitle(),
        'companyName' => $companyName,
        'applicationStatusId' => $jobModel->getApplicationStatusId(),
        'urgency' => $jobModel->getUrgency(),
        'nextAction' => $jobModel->getNextAction(),
        'nextActionDue' => $jobModel->getNextActionDue(),
        'lastStatusChange' => $jobModel->getLastStatusChange(),
        'location' => $jobModel->getLocation(),
        'url' => $jobModel->getUrl()
    ],
    'statuses' => $statusOptions
];
header('Content-Type: application/json; charset=utf-8');
echo json_encode($result) . PHP_EOL;
