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
if (! $auth->hasRole('user')) {
    $auth->forbidden();
    exit(0);
}
if (! Auth::validateCsrfToken()) {
    header('HTTP/1.0 403 Forbidden');
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['result' => 'FAILED', 'error' => 'Invalid CSRF token']) . PHP_EOL;
    exit(0);
}
$keywordValue = Tools::post('value');
$sortKey = Tools::post('sortKey');
$rowId = Tools::post('rowId');
$result = 'OK';
$keywordId = '';
try {
    $keywordModel = new KeywordModel();
    $keywordModel->setKeywordValue($keywordValue);
    $keywordModel->setSortKey($sortKey);

    $keywordController = new KeywordController();
    $keywordId = $keywordController->add($keywordModel);

    if (! ($keywordId > 0)) {
        throw new ControllerException("Add failed.");
    }
    $newKeywordModel = $keywordController->get($keywordId);
    $keywordListView = new KeywordListView();
    $row = $keywordListView->displayKeywordRow($newKeywordModel, 'list');
} catch (ControllerException $e) {
    $keywordListView = new KeywordListView('html', null);
    $keywordModel->setId($rowId);
    $row = $keywordListView->displayKeywordRow($keywordModel, 'add', 'Add Keyword record failed. ' . $e->getMessage());
}

$result = [
    'result' => $result,
    'row' => $row,
    'newId' => $keywordId
];
header('Content-Type: application/json; charset=utf-8');
echo json_encode($result) . PHP_EOL;
