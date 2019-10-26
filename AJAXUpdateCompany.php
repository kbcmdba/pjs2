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

require_once "vendor/autoload.php";

use com\kbcmdba\pjs2\Libs\Auth;
use com\kbcmdba\pjs2\Libs\Controllers\CompanyController;
use com\kbcmdba\pjs2\Libs\Exceptions\ControllerException;
use com\kbcmdba\pjs2\Libs\Tools;
use com\kbcmdba\pjs2\Libs\Views\CompanyListView;

$auth = new Auth();
if (! $auth->isAuthorized()) {
    $auth->forbidden();
    exit(0); // Should never get here but just in case...
}
$id = Tools::param('id');
$result = 'OK';
$companyId = '';
$agencyCompanyId = Tools::param('agencyCompanyId');
$companyName = Tools::param('companyName');
$companyAddress1 = Tools::param('companyAddress1');
$companyAddress2 = Tools::param('companyAddress2');
$companyCity = Tools::param('companyCity');
$companyState = Tools::param('companyState');
$companyZip = Tools::param('companyZip');
$companyPhone = Tools::param('companyPhone');
$companyUrl = Tools::param('companyUrl');
$lastContacted = Tools::param('lastContacted');
$rowStyle = Tools::param('rowStyle');
$rowId = Tools::param('rowId');
$result = 'OK';
$clv = new CompanyListView('html', null);
try {
    $companyController = new CompanyController();
    $companyModel = $companyController->get($id);
    $companyModel->setAgencyCompanyId($agencyCompanyId);
    $companyModel->setCompanyName($companyName);
    $companyModel->setCompanyAddress1($companyAddress1);
    $companyModel->setCompanyAddress2($companyAddress2);
    $companyModel->setCompanyCity($companyCity);
    $companyModel->setCompanyState($companyState);
    $companyModel->setCompanyZip($companyZip);
    $companyModel->setCompanyPhone($companyPhone);
    $companyModel->setCompanyUrl($companyUrl);
    $companyModel->setLastContacted($lastContacted);
    
    $result = $companyController->update($companyModel);
    
    if (! ($result > 0)) {
        throw new ControllerException("Update failed.");
    }
    // Get it again because the updated column has changed.
    $companyModel = $companyController->get($id);
    $rows = $clv->displayCompanyRow($companyModel, 'list', $rowStyle);
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
