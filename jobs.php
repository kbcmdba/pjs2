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

require_once 'Libs/autoload.php';

$config = new Config();
$controller = new JobController('read');

// Filter params from the dashboard click-throughs. All "active-only" filters
// inherently exclude closed-state statuses (CLOSED, INVALID, MISMATCH, etc.)
// because the controller methods JOIN on applicationStatus.isActive = 1.
$filter  = Tools::param('filter');
$urgency = Tools::param('urgency');

$pageTitle = 'Jobs';
$heading   = 'Jobs';
$modelList = null;

if ($filter !== '') {
    $now      = $controller->now();
    $today    = $controller->today();
    $tomorrow = $controller->tomorrow();
    $nextWeek = ControllerBase::datestamp(time() + 7 * 86400);

    if ($filter === 'overdue') {
        $modelList = $controller->getActiveOverdue($now);
        $pageTitle = 'Overdue Jobs';
        $heading   = 'Overdue (active jobs whose next action is past due)';
    } elseif ($filter === 'dueToday') {
        $modelList = $controller->getActiveDueRange($today, $tomorrow);
        $pageTitle = 'Due Today';
        $heading   = 'Due Today (active jobs)';
    } elseif ($filter === 'dueWeek') {
        $modelList = $controller->getActiveDueRange($today, $nextWeek);
        $pageTitle = 'Due This Week';
        $heading   = 'Due This Week (active jobs)';
    }
} elseif ($urgency !== '' && in_array($urgency, ['high', 'medium', 'low'], true)) {
    $modelList = $controller->getActiveByUrgency($urgency);
    $pageTitle = ucfirst($urgency) . ' Urgency';
    $heading   = ucfirst($urgency) . ' Urgency (active jobs)';
}

if ($modelList === null) {
    // No filter (or unrecognized one) — show everything.
    $modelList = $controller->getAll();
}

$page = new PJSWebPage($config->getTitle() . " - $pageTitle");
$body = "<h2>" . Tools::htmlOut($heading) . "</h2>\n";
if ($filter !== '' || $urgency !== '') {
    $body .= '<p style="margin: 0 12px 12px;"><a href="jobs.php">&larr; Show all jobs</a></p>' . "\n";
}
$modelListView = new JobListView('html', $modelList);
$body .= $modelListView->getView();
$page->setBody($body);
$page->displayPage();
