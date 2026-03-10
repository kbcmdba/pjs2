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

$config = new Config();
try {
    $dbc = new DBConnection();
} catch (DaoException $ex) {
    echo "Database connection failed. Has this system been set up yet? Have you used resetDb.php?\n";
    exit(255);
}

$config = new Config();
$page = new PJSWebPage($config->getTitle());
$body = '';

$jobController = new JobController('read');
$now = $jobController->now();
$today = $jobController->today();
$tomorrow = $jobController->tomorrow();
$nextWeek = ControllerBase::datestamp(time() + 7 * 86400);

// Counts
$jobsOverdue = $jobController->countActiveOverdue($now);
$jobsDueToday = $jobController->countActiveDueRange($today, $tomorrow);
$jobsDue7Days = $jobController->countActiveDueRange($today, $nextWeek);
$highUrgency = $jobController->countActiveByUrgency('high');
$mediumUrgency = $jobController->countActiveByUrgency('medium');
$lowUrgency = $jobController->countActiveByUrgency('low');

$searchController = new SearchController('read');
$searches = $searchController->countAll();

$body .= <<<HTML
<h2>Dashboard</h2>
<table id="dashboardSummary">
  <tr>
    <th>Overdue</th><th>Due Today</th><th>Due This Week</th>
    <th>High Urgency</th><th>Medium Urgency</th><th>Low Urgency</th>
    <th>Saved Searches</th>
  </tr>
  <tr>
    <td>$jobsOverdue</td><td>$jobsDueToday</td><td>$jobsDue7Days</td>
    <td>$highUrgency</td><td>$mediumUrgency</td><td>$lowUrgency</td>
    <td>$searches</td>
  </tr>
</table>

HTML;

// Overdue jobs detail
$overdueJobs = $jobController->getActiveOverdue($now);
if (count($overdueJobs) > 0) {
    $overdueView = new JobSummaryView('html', $overdueJobs);
    $overdueView->setLabel('Overdue Jobs');
    $body .= $overdueView->getView();
}

// Due today detail
$dueTodayJobs = $jobController->getActiveDueRange($today, $tomorrow);
if (count($dueTodayJobs) > 0) {
    $dueTodayView = new JobSummaryView('html', $dueTodayJobs);
    $dueTodayView->setLabel('Due Today');
    $body .= $dueTodayView->getView();
}

// Due this week detail
$dueWeekJobs = $jobController->getActiveDueRange($tomorrow, $nextWeek);
if (count($dueWeekJobs) > 0) {
    $dueWeekView = new JobSummaryView('html', $dueWeekJobs);
    $dueWeekView->setLabel('Due This Week');
    $body .= $dueWeekView->getView();
}

// Application status summary
$applicationStatusController = new ApplicationStatusController('read');
$asmList = $applicationStatusController->getAll();
$asv = new ApplicationStatusSummaryView('html', $asmList);
$body .= $asv->getView();

$page->setBody($body);
$page->displayPage();
