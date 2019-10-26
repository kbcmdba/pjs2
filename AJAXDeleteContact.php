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
use com\kbcmdba\pjs2\Libs\Controllers\ContactController;
use com\kbcmdba\pjs2\Libs\Exceptions\ControllerException;
use com\kbcmdba\pjs2\Libs\Models\ContactModel;
use com\kbcmdba\pjs2\Libs\Tools;
use com\kbcmdba\pjs2\Libs\Views\ContactListView;

$auth = new Auth();
if (! $auth->isAuthorized()) {
    $auth->forbidden();
    exit(0); // Should never get here but just in case...
}
$id = Tools::param('id');
$result = 'OK';
$row = "";
try {
    $contactModel = new ContactModel();
    $contactModel->setId($id);
    $contactController = new ContactController();
    $contactController->delete($contactModel);
} catch (ControllerException $e) {
    $result = "Delete failed. " . $e->getMessage();
    $contactController = new ContactController();
    $contactModel = $contactController->get($id);
    $contactListView = new ContactListView();
    $row = $contactListView->displayContactRow($contactModel, 'list', 'add', $result);
}

echo json_encode([
    'result' => $result,
    'row' => $row
]) . PHP_EOL;
