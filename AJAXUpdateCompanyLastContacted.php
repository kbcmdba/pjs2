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
$rowStyle = Tools::param('rowStyle');
$companyId = '';
$result = 'OK';
$clv = new CompanyListView('html', null);
try {
    $companyController = new CompanyController();
    $companyModel = $companyController->get($id);
    $companyModel->setLastContacted(Tools::currentTimestamp());
    
    $result = $companyController->update($companyModel);
    
    if (! ($result > 0)) {
        throw new ControllerException("Update failed.");
    }
    // Get it again because the updated column has changed.
    $companyModel = $companyController->get($id);
    $rows = $clv->displayCompanyRow($companyModel, 'list', $rowStyle, '');
    $result = 'OK';
} catch (ControllerException $e) {
    $result = 'FAILED';
    $rows = $clv->displayCompanyRow($companyModel, 'update', $rowStyle, 'Update Company record failed. ' . $e->getMessage());
}

$result = [
    'result' => $result,
    'rows' => $rows
];
echo json_encode($result) . PHP_EOL;
