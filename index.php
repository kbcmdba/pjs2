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
    //
    // PJS2 is single-user on a private network (web1.hole) — surface the
    // actual error message and location so the operator (KB) can diagnose
    // without digging into PHP error logs. Also written to error_log() for
    // historical record.
    error_log(
        'PJS2 index.php fatal: ' . $e->getMessage()
        . ' at ' . $e->getFile() . ':' . $e->getLine()
    );
    http_response_code(503);
    $errClass = htmlspecialchars(get_class($e), ENT_QUOTES, 'UTF-8');
    $errMsg   = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    $errFile  = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');
    $errLine  = (int) $e->getLine();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>PJS2 - Error</title>
    <style>
        body { font-family: sans-serif; max-width: 760px; margin: 60px auto; padding: 0 20px; color: #333; line-height: 1.5; }
        h2 { color: #4a4a8a; }
        .err { background: #fee; padding: 12px 16px; border-left: 4px solid #c00; margin: 16px 0; font-family: monospace; word-break: break-word; }
        .where { color: #555; font-family: monospace; font-size: 0.9em; }
        ul { color: #444; }
        .hint { color: #888; font-size: 0.85em; margin-top: 24px; }
    </style>
</head>
<body>
    <h2>PJS2 - Error</h2>
    <p>The dashboard couldn't render. The actual error:</p>
    <div class="err"><strong><?= $errClass ?>:</strong> <?= $errMsg ?></div>
    <p class="where">at <?= $errFile ?>:<?= $errLine ?></p>
    <p><strong>Likely causes:</strong></p>
    <ul>
        <li>Database server (mysql1.hole) is down or restarting</li>
        <li>DNS resolution failed for mysql1.hole (check your DNS resolver / Pi-hole)</li>
        <li>Network blip between web1 and mysql1</li>
        <li>Schema or query change broke a controller call (less likely if nothing was deployed recently)</li>
    </ul>
    <p class="hint">Same details also written to the PHP error log on web1.</p>
</body>
</html>
    <?php
    exit;
}
