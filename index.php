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

function main()
{
    $config = new Config();
    try {
        $dbc = new DBConnection();
    } catch (DaoException $ex) {
        echo "Database connection failed. Has this system been set up yet? Have you used resetDb.php?\n";
        exit(255);
    }

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
}

try {
    main();
} catch (\Throwable $e) {
    // Outer safety net: catches anything main() didn't handle (transient DB
    // hiccups, DNS failures, mysqli_sql_exception, etc.). DaoException for
    // "have you run resetDb?" is still caught inside main() with its specific
    // hint message; this catch is the durability fallback.
    error_log(
        'PJS2 index.php fatal: ' . $e->getMessage()
        . ' at ' . $e->getFile() . ':' . $e->getLine()
    );
    http_response_code(503);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>PJS2 - Temporarily Unavailable</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 80px auto; padding: 0 20px; color: #333; line-height: 1.5; }
        h2 { color: #4a4a8a; }
        .hint { color: #888; font-size: 0.9em; margin-top: 24px; }
    </style>
</head>
<body>
    <h2>Temporarily Unavailable</h2>
    <p>PJS2 is having trouble reaching the database right now. This is usually transient &mdash; try refreshing in a few seconds.</p>
    <p class="hint">If the issue persists, check the database service (mysql1.hole) and DNS resolution. Details have been logged for diagnosis.</p>
</body>
</html>
    <?php
    exit;
}
