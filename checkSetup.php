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

$isCli = (php_sapi_name() === 'cli');
$pass = 0;
$fail = 0;
$warn = 0;
$results = [];

function check($label, $ok, $detail = '', $isWarning = false)
{
    global $pass, $fail, $warn, $results;
    if ($ok) {
        $pass++;
        $results[] = ['status' => 'PASS', 'label' => $label, 'detail' => $detail];
    } elseif ($isWarning) {
        $warn++;
        $results[] = ['status' => 'WARN', 'label' => $label, 'detail' => $detail];
    } else {
        $fail++;
        $results[] = ['status' => 'FAIL', 'label' => $label, 'detail' => $detail];
    }
}

// 1. config.xml exists and is readable
$configExists = is_readable('config.xml');
check('config.xml exists and is readable', $configExists, $configExists ? '' : 'Copy config_sample.xml to config.xml and edit it');

// 2. Parse config.xml
$config = null;
if ($configExists) {
    try {
        $config = new Config();
        check('config.xml parses successfully', true);
    } catch (\Exception $e) {
        check('config.xml parses successfully', false, $e->getMessage());
    }
}

if ($config !== null) {
    // 3. Required config values are not sample defaults
    $dbHost = $config->getDbHost();
    $dbUser = $config->getDbUser();
    $dbPass = $config->getDbPass();
    $dbName = $config->getDbName();
    $dbPort = $config->getDbPort();

    check('dbHost is set', $dbHost !== null && $dbHost !== '', "dbHost: $dbHost");
    check('dbUser is set', $dbUser !== null && $dbUser !== '', "dbUser: $dbUser");
    check('dbPass is not the sample default', $dbPass !== 'SomethingComplicated', $dbPass === 'SomethingComplicated' ? 'Still using sample password' : '');
    check('dbName is set', $dbName !== null && $dbName !== '', "dbName: $dbName");
    check('dbPort is set', $dbPort !== null && $dbPort !== '', "dbPort: $dbPort");

    // 4. API key configured
    $apiKey = $config->getApiKey();
    check(
        'apiKey is configured',
        $apiKey !== '' && $apiKey !== 'ChangeThisToARandomString',
        ($apiKey === '' || $apiKey === 'ChangeThisToARandomString') ? 'API key is missing or still the sample default' : '',
        true
    );

    // 5. Auth settings
    check(
        'skipAuth is disabled',
        $config->getSkipAuth() !== '1',
        $config->getSkipAuth() === '1' ? 'skipAuth is enabled — disable for production' : '',
        true
    );
    check(
        'resetOk is disabled',
        $config->getResetOk() !== '1',
        $config->getResetOk() === '1' ? 'resetOk is enabled — disable for production' : '',
        true
    );

    // 6. Database connection
    $dbConnected = false;
    try {
        $dbc = new DBConnection('read', null, null, null, null, null, 'mysqli', true);
        $dbh = $dbc->getConnection();
        $dbConnected = ($dbh !== null);
        check('Database connection', $dbConnected);
    } catch (\Exception $e) {
        check('Database connection', false, $e->getMessage());
    }

    // 7. Tables exist
    if ($dbConnected) {
        $expectedTables = [
            'applicationStatus',
            'applicationStatusSummary',
            'auth_ticket',
            'company',
            'contact',
            'job',
            'jobKeywordMap',
            'keyword',
            'note',
            'search',
            'searchStatus',
            'user',
            'version',
        ];
        $tablesResult = $dbh->query("SHOW TABLES");
        $actualTables = [];
        while ($row = $tablesResult->fetch_row()) {
            $actualTables[] = $row[0];
        }
        foreach ($expectedTables as $table) {
            check("Table '$table' exists", in_array($table, $actualTables));
        }
        $extra = array_diff($actualTables, $expectedTables);
        if (! empty($extra)) {
            check('No unexpected tables', false, 'Extra tables: ' . implode(', ', $extra), true);
        }

        // 8. user table has at least one admin
        if (in_array('user', $actualTables)) {
            $userResult = $dbh->query("SELECT COUNT(*) AS cnt FROM user WHERE role = 'admin'");
            $adminCount = $userResult ? $userResult->fetch_assoc()['cnt'] : 0;
            check('At least one admin user exists', $adminCount > 0, $adminCount == 0 ? 'No admin users — login will not work' : "$adminCount admin user(s)");
        }

        // 9. applicationStatus has data (required for job entry)
        if (in_array('applicationStatus', $actualTables)) {
            $asResult = $dbh->query("SELECT COUNT(*) AS cnt FROM applicationStatus");
            $asCount = $asResult ? $asResult->fetch_assoc()['cnt'] : 0;
            check('Application statuses populated', $asCount > 0, $asCount == 0 ? 'No application statuses — job entry will fail' : "$asCount status(es)");
        }
    }
}

// Output results
if ($isCli) {
    echo "\nPJS2 Setup Check\n";
    echo str_repeat('=', 60) . "\n\n";
    foreach ($results as $r) {
        $icon = $r['status'] === 'PASS' ? 'PASS' : ($r['status'] === 'WARN' ? 'WARN' : 'FAIL');
        $line = sprintf("  [%s] %s", $icon, $r['label']);
        if ($r['detail'] !== '') {
            $line .= " — " . $r['detail'];
        }
        echo $line . "\n";
    }
    echo "\n" . str_repeat('-', 60) . "\n";
    echo sprintf("  %d passed, %d failed, %d warnings\n\n", $pass, $fail, $warn);
    exit($fail > 0 ? 1 : 0);
} else {
    $page = new PJSWebPage($config ? $config->getTitle() . ' - Setup Check' : 'PJS2 - Setup Check');
    $body = "<h2>Setup Check</h2>\n<table>\n";
    $body .= "<thead><tr><th>Status</th><th>Check</th><th>Detail</th></tr></thead>\n<tbody>\n";
    foreach ($results as $r) {
        $color = $r['status'] === 'PASS' ? '#28a745' : ($r['status'] === 'WARN' ? '#ffc107' : '#dc3545');
        $body .= sprintf(
            "<tr><td style=\"color: %s; font-weight: bold;\">%s</td><td>%s</td><td>%s</td></tr>\n",
            $color,
            htmlspecialchars($r['status']),
            htmlspecialchars($r['label']),
            htmlspecialchars($r['detail'])
        );
    }
    $body .= "</tbody>\n</table>\n";
    $body .= sprintf("<p><strong>%d passed, %d failed, %d warnings</strong></p>\n", $pass, $fail, $warn);
    $page->setBody($body);
    $page->displayPage();
}
