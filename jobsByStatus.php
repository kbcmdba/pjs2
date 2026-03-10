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

require_once 'Libs/autoload.php';

$statusId = Tools::param('statusId');
if (! Tools::isNumeric($statusId)) {
    header('Location: index.php');
    exit();
}

$config = new Config();
$appStatusController = new ApplicationStatusController('read');
$statusModel = $appStatusController->get($statusId);
if (! $statusModel) {
    header('Location: index.php');
    exit();
}

$statusLabel = $statusModel->getStatusValue();
$page = new PJSWebPage($config->getTitle() . " - Jobs: $statusLabel");

$jobController = new JobController('read');
$jobs = $jobController->getByApplicationStatus($statusId);

$body = '';
if (count($jobs) > 0) {
    $view = new JobSummaryView('html', $jobs);
    $view->setLabel("Jobs: $statusLabel");
    $body .= $view->getView();
} else {
    $safeLabel = Tools::htmlOut($statusLabel);
    $body .= "<h2>Jobs: $safeLabel</h2>\n<p>No jobs with this status.</p>\n";
}

$body .= "<p><a href=\"index.php\">&larr; Back to Dashboard</a></p>\n";

$page->setBody($body);
$page->displayPage();
