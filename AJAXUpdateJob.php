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
    exit(0); // Should never get here but just in case...
}
if (! $auth->hasRole('admin')) {
    $auth->forbidden();
    exit(0);
}
if (! Auth::validateCsrfToken()) {
    header('HTTP/1.0 403 Forbidden');
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['result' => 'FAILED', 'error' => 'Invalid CSRF token']) . PHP_EOL;
    exit(0);
}
$id = Tools::param('id');
$primaryContactId = Tools::param('contactId');
$companyId = Tools::param('companyId');
$applicationStatusId = Tools::param('applicationStatusId');
$lastStatusChange = Tools::param('lastStatusChange');
// Default to NOW() if empty so a job with a zero-date stored history
// (e.g., from an API create that didn't set it) can still be saved
// from inline edit. Caught 2026-04-26.
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
$rowId = Tools::param('rowId');
$result = 'OK';
$jobId = '';
$jobListView = new JobListView('html', null);
try {
    $jobController = new JobController();
    if ($url !== '' && $url !== null) {
        $existingJob = $jobController->getByUrl($url);
        if ($existingJob !== null && $existingJob->getId() != $id) {
            $existingId = $existingJob->getId();
            $existingTitle = $existingJob->getPositionTitle();
            $companyName = '';
            $existingCompanyId = $existingJob->getCompanyId();
            if ($existingCompanyId) {
                $cc = new CompanyController('read');
                $cm = $cc->get($existingCompanyId);
                if ($cm) {
                    $companyName = ' at ' . $cm->getCompanyName();
                }
            }
            throw new ControllerException(
                "Duplicate URL. Already exists on job #$existingId"
                . " ($existingTitle$companyName)."
            );
        }
    }
    $jobModel = $jobController->get($id);
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
    $jobModel->setCompRangeLow($compRangeLow !== '' ? $compRangeLow : null);
    $jobModel->setCompRangeHigh($compRangeHigh !== '' ? $compRangeHigh : null);
    $result = $jobController->update($jobModel);
    
    if (! ($result > 0)) {
        throw new ControllerException("Update failed.");
    }
    $jobId = $result;
    $row = $jobListView->displayJobRow($jobModel, 'list');
    $result = 'OK';
} catch (ControllerException $e) {
    $result = 'FAILED';
    $row = $jobListView->displayJobRow($jobModel, 'update', 'Update Job record failed. ' . $e->getMessage());
}

$result = [
    'result' => $result,
    'row' => $row,
    'id' => $jobId
];
header('Content-Type: application/json; charset=utf-8');
echo json_encode($result) . PHP_EOL;
