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

    // 10. REST API connectivity
    $apiKey = $config->getApiKey();
    if ($apiKey !== '' && $apiKey !== 'ChangeThisToARandomString') {
        // Determine base URL
        if ($isCli) {
            $baseUrl = 'http://127.0.0.1/pjs2/';
        } else {
            $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $path = dirname($_SERVER['SCRIPT_NAME']);
            $baseUrl = $scheme . '://' . $host . $path . '/';
        }
        $apiUrl = $baseUrl . 'api/companies.php';

        // Test with valid API key
        $validCtx = stream_context_create(['http' => [
            'method' => 'GET',
            'header' => "X-API-Key: $apiKey\r\n",
            'timeout' => 5,
            'ignore_errors' => true,
        ]]);
        $validResponse = @file_get_contents($apiUrl, false, $validCtx);
        $validStatus = 0;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $validStatus = (int) $m[1];
        }
        check('API responds with valid key', $validStatus === 200, $validStatus === 200 ? '' : "HTTP $validStatus from $apiUrl");

        // Test with bad API key (should get 401)
        $badCtx = stream_context_create(['http' => [
            'method' => 'GET',
            'header' => "X-API-Key: definitely-not-the-right-key\r\n",
            'timeout' => 5,
            'ignore_errors' => true,
        ]]);
        $badResponse = @file_get_contents($apiUrl, false, $badCtx);
        $badStatus = 0;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $badStatus = (int) $m[1];
        }
        check('API rejects bad key', $badStatus === 401, $badStatus === 401 ? '' : "Expected 401, got HTTP $badStatus");

        // 11. API CRUD smoke tests
        // Uses "__PJS2_SETUP_TEST__" prefix — data that can't exist in real usage.
        // Cleanup happens via direct SQL regardless of pass/fail.
        $testPrefix = '__PJS2_SETUP_TEST__';
        $testCompanyName = $testPrefix . 'Company_' . time();
        $testContactName = $testPrefix . 'Contact_' . time();
        $testIds = ['company' => null, 'contact' => null, 'note' => null];

        // Helper: make an API request and return [httpStatus, decodedBody]
        $apiCall = function ($endpoint, $method, $body = null) use ($baseUrl, $apiKey) {
            $headers = "X-API-Key: $apiKey\r\n";
            $opts = [
                'method' => $method,
                'header' => $headers,
                'timeout' => 5,
                'ignore_errors' => true,
            ];
            if ($body !== null) {
                $opts['header'] .= "Content-Type: application/json\r\n";
                $opts['content'] = json_encode($body);
            }
            $ctx = stream_context_create(['http' => $opts]);
            $url = $baseUrl . 'api/' . $endpoint;
            $response = @file_get_contents($url, false, $ctx);
            $status = 0;
            if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
                $status = (int) $m[1];
            }
            return [$status, json_decode($response, true)];
        };

        // Helper: clean up test data (always runs)
        $cleanup = function () use ($testPrefix, $dbConnected, $dbh) {
            if (! $dbConnected) {
                return;
            }
            // Notes first (no FK but clean up by matching test entities)
            $dbh->query("DELETE FROM note WHERE noteText LIKE '" . $dbh->real_escape_string($testPrefix) . "%'");
            $dbh->query("DELETE FROM contact WHERE contactName LIKE '" . $dbh->real_escape_string($testPrefix) . "%'");
            $dbh->query("DELETE FROM company WHERE companyName LIKE '" . $dbh->real_escape_string($testPrefix) . "%'");
        };

        // Clean up any leftovers from a prior failed run
        $cleanup();

        try {
            // CREATE company
            list($status, $body) = $apiCall('companies.php', 'POST', [
                'companyName' => $testCompanyName,
                'companyCity' => $testPrefix . 'City',
            ]);
            $companyCreated = ($status === 201 && isset($body['id']));
            check('API: Create company', $companyCreated, $companyCreated ? "id={$body['id']}" : "HTTP $status");
            if ($companyCreated) {
                $testIds['company'] = $body['id'];
            }

            // GET company by ID
            if ($testIds['company']) {
                list($status, $body) = $apiCall('companies.php?id=' . $testIds['company'], 'GET');
                $getOk = ($status === 200 && isset($body['company']['companyName']) && $body['company']['companyName'] === $testCompanyName);
                check('API: Get company by ID', $getOk, $getOk ? '' : "HTTP $status");
            }

            // GET company by name (list/search)
            if ($testIds['company']) {
                list($status, $body) = $apiCall('companies.php?name=' . urlencode($testCompanyName), 'GET');
                $searchOk = ($status === 200 && isset($body['count']) && $body['count'] >= 1);
                check('API: Find company by name', $searchOk, $searchOk ? "count={$body['count']}" : "HTTP $status");
            }

            // UPDATE company
            if ($testIds['company']) {
                $updatedCity = $testPrefix . 'UpdatedCity';
                list($status, $body) = $apiCall('companies.php', 'PUT', [
                    'id' => $testIds['company'],
                    'companyName' => $testCompanyName,
                    'companyCity' => $updatedCity,
                ]);
                $updateOk = ($status === 200 && isset($body['company']['companyCity']) && $body['company']['companyCity'] === $updatedCity);
                check('API: Update company', $updateOk, $updateOk ? '' : "HTTP $status");
            }

            // CREATE contact (linked to test company)
            list($status, $body) = $apiCall('contacts.php', 'POST', [
                'contactName' => $testContactName,
                'companyId' => $testIds['company'],
            ]);
            $contactCreated = ($status === 201 && isset($body['id']));
            check('API: Create contact', $contactCreated, $contactCreated ? "id={$body['id']}" : "HTTP $status");
            if ($contactCreated) {
                $testIds['contact'] = $body['id'];
            }

            // GET contact by ID
            if ($testIds['contact']) {
                list($status, $body) = $apiCall('contacts.php?id=' . $testIds['contact'], 'GET');
                $getOk = ($status === 200 && isset($body['contact']['contactName']) && $body['contact']['contactName'] === $testContactName);
                check('API: Get contact by ID', $getOk, $getOk ? '' : "HTTP $status");
            }

            // CREATE note (on test company)
            if ($testIds['company']) {
                list($status, $body) = $apiCall('notes.php', 'POST', [
                    'appliesToTable' => 'company',
                    'appliesToId' => $testIds['company'],
                    'noteText' => $testPrefix . 'This is a test note',
                ]);
                $noteCreated = ($status === 201 && isset($body['id']));
                check('API: Create note', $noteCreated, $noteCreated ? "id={$body['id']}" : "HTTP $status");
                if ($noteCreated) {
                    $testIds['note'] = $body['id'];
                }
            }

            // GET notes by entity
            if ($testIds['company']) {
                list($status, $body) = $apiCall('notes.php?appliesToTable=company&appliesToId=' . $testIds['company'], 'GET');
                $notesOk = ($status === 200 && isset($body['count']) && $body['count'] >= 1);
                check('API: List notes by entity', $notesOk, $notesOk ? "count={$body['count']}" : "HTTP $status");
            }

            // UPDATE note
            if ($testIds['note']) {
                $updatedText = $testPrefix . 'Updated test note';
                list($status, $body) = $apiCall('notes.php', 'PUT', [
                    'id' => $testIds['note'],
                    'noteText' => $updatedText,
                ]);
                $updateOk = ($status === 200 && isset($body['note']['noteText']) && $body['note']['noteText'] === $updatedText);
                check('API: Update note', $updateOk, $updateOk ? '' : "HTTP $status");
            }

            // GET 404 for nonexistent ID
            list($status, $body) = $apiCall('companies.php?id=999999999', 'GET');
            check('API: 404 for nonexistent record', $status === 404, $status === 404 ? '' : "Expected 404, got HTTP $status");
        } finally {
            $cleanup();
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
