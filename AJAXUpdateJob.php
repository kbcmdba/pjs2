<?php

/**
 * phpjobseeker
 *
 * Copyright (C) 2009, 2015, 2017 Kevin Benton - kbenton at bentonfam dot org
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
$id = Tools::param('id');
$primaryContactId = Tools::param('contactId');
$companyId = Tools::param('companyId');
$applicationStatusId = Tools::param('applicationStatusId');
$lastStatusChange = Tools::param('lastStatusChange');
$urgency = Tools::param('urgency');
$nextActionDue = Tools::param('nextActionDue');
$nextAction = Tools::param('nextAction');
$positionTitle = Tools::param('positionTitle');
$location = Tools::param('location');
$url = Tools::param('url');
$rowId = Tools::param('rowId');
$result = 'OK';
$jobId = '';
$jobListView = new JobListView('html', null);
try {
    $jobController = new JobController();
    $jobModel = $jobController->get($id);
    $jobModel->setPrimaryContactId($primaryContactId);
    $jobModel->setCompanyId($companyId);
    $jobModel->setApplicationStatusId($applicationStatusId);
    $jobModel->setLastStatusChange($lastStatusChange);
    $jobModel->setUrgency($urgency);
    $jobModel->setNextActionDue($nextActionDue);
    $jobModel->setNextAction($nextAction);
    $jobModel->setPositionTitle($positionTitle);
    $jobModel->setLocation($location);
    $jobModel->setUrl($url);
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
echo json_encode($result) . PHP_EOL;
