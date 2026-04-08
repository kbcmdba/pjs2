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

try {
    $noteModel = new NoteModel();
    $noteModel->setId($id);
    $noteController = new NoteController();
    $noteController->delete($noteModel);
    $result = ['result' => 'OK'];
} catch (ControllerException $e) {
    $result = ['result' => 'FAILED', 'error' => $e->getMessage()];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result) . PHP_EOL;
