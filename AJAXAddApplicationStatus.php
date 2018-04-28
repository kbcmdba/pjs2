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

require_once "Libs/autoload.php" ;

$auth = new Auth() ;
if (! $auth->isAuthorized()) {
    $auth->forbidden() ;
    exit(0) ; // Should never get here but just in case...
}
$statusValue               = Tools::post('statusValue') ;
$style                     = Tools::post('style') ;
$isActive                  = Tools::post('isActive') ;
$sortKey                   = Tools::post('sortKey') ;
$rowId                     = Tools::post('rowId') ;
$rowStyle                  = Tools::post('rowStyle') ;
$result                    = 'OK' ;
$applicationStatusId       = '' ;
$newApplicationStatusModel = null ;
try {
    $applicationStatusModel = new ApplicationStatusModel() ;
    $applicationStatusModel->setStatusValue($statusValue) ;
    $applicationStatusModel->setStyle($style) ;
    $applicationStatusModel->setIsActive($isActive) ;
    $applicationStatusModel->setSortKey($sortKey) ;

    $applicationStatusController = new ApplicationStatusController() ;
    $applicationStatusId = $applicationStatusController->add($applicationStatusModel) ;

    if (! ($applicationStatusId > 0)) {
        throw new ControllerException("Add failed.") ;
    }
    $newApplicationStatusModel = $applicationStatusController->get($applicationStatusId) ;
    $applicationStatusRowView = new ApplicationStatusListView() ;
    $row = $applicationStatusRowView->displayApplicationStatusRow($newApplicationStatusModel, 'list') ;
} catch (ControllerException $e) {
    $applicationStatusRowView = new ApplicationStatusListView('html', null) ;
    $applicationStatusModel->setId($rowId) ;
    $row = $applicationStatusRowView->displayApplicationStatusRow(
        $applicationStatusModel,
        'list',
        'Add Application Status record failed. '
                                           . $e->getMessage()
                                           ) ;
}

$result = [ 'result' => $result, 'row' => $row, 'newId' => $applicationStatusId ] ;
echo json_encode($result) . PHP_EOL ;
