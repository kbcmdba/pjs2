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

// Browser-driven auth check. This page outputs CSV directly (no PJSWebPage
// wrapper), so we handle auth manually rather than relying on the page-class
// side effect.
$auth = new Auth();
if (! $auth->isAuthorized()) {
    header('Location: login.php');
    exit;
}

// Pipeline export. Filters to active-status jobs only - closed/triage states
// (MISMATCH, CLOSED, MISSING, DUPLICATE, INVALID, UNAVAILABLE) are excluded
// because they reveal disqualifier criteria (sensitive intel for vendor
// handoff). To export everything including triaged-out, add ?all=1.
$includeAll = (Tools::param('all') === '1');

$dbc = new DBConnection('read', null, null, null, null, null, 'mysqli', true);
$dbh = $dbc->getConnection();

$activeFilter = $includeAll ? '' : 'WHERE a.isActive = 1';

$sql = <<<SQL
SELECT
  IFNULL(c.companyName, '') AS Company,
  IFNULL(j.positionTitle, '') AS Position,
  a.statusValue AS Status,
  IFNULL(j.compRangeLow, '') AS CompLow,
  IFNULL(j.compRangeHigh, '') AS CompHigh,
  IFNULL(j.location, '') AS Location,
  IFNULL(j.url, '') AS PostingURL,
  DATE(j.created) AS DateFound,
  DATE(j.lastStatusChange) AS LastStatusChange,
  CASE
    WHEN j.nextActionDue IS NULL OR YEAR(j.nextActionDue) < 2000 THEN ''
    ELSE DATE(j.nextActionDue)
  END AS NextActionDue,
  IFNULL(j.nextAction, '') AS NextAction,
  IFNULL(co.contactName, '') AS PrimaryContact,
  (SELECT COUNT(*) FROM note WHERE appliesToTable = 'job' AND appliesToId = j.id) AS NotesCount
FROM job j
JOIN applicationStatus a ON j.applicationStatusId = a.id
LEFT JOIN company c ON j.companyId = c.id
LEFT JOIN contact co ON j.primaryContactId = co.id
$activeFilter
ORDER BY a.sortKey, j.lastStatusChange DESC
SQL;

$result = $dbh->query($sql);
if (! $result) {
    http_response_code(500);
    header('Content-Type: text/plain');
    echo "Query failed: " . $dbh->error;
    exit;
}

// CSV output. Filename includes today's date so multiple exports don't
// collide; "active" or "all" suffix indicates filter scope.
$filterTag = $includeAll ? 'all' : 'active';
$filename = 'pipeline-' . $filterTag . '-' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store');

$out = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility (without it, Excel mangles non-ASCII
// characters on import).
fwrite($out, "\xEF\xBB\xBF");

// Header row
$headers = [
    'Company', 'Position', 'Status',
    'Comp Low', 'Comp High', 'Location', 'Posting URL',
    'Date Found', 'Last Status Change', 'Next Action Due', 'Next Action',
    'Primary Contact', 'Notes Count',
];
fputcsv($out, $headers);

// Data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($out, [
        $row['Company'],
        $row['Position'],
        $row['Status'],
        $row['CompLow'],
        $row['CompHigh'],
        $row['Location'],
        $row['PostingURL'],
        $row['DateFound'],
        $row['LastStatusChange'],
        $row['NextActionDue'],
        $row['NextAction'],
        $row['PrimaryContact'],
        $row['NotesCount'],
    ]);
}

fclose($out);
