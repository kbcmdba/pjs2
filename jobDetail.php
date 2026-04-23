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
$id = Tools::param('id');

if ($id === '') {
    header('Location: jobs.php');
    exit(0);
}

$page = new PJSWebPage($config->getTitle() . ' - Job Detail');

$dbc = new DBConnection('read', null, null, null, null, null, 'mysqli', true);
$dbh = $dbc->getConnection();

// Get job with company and status
$stmt = $dbh->prepare(
    "SELECT j.*, a.statusValue, c.companyName,
            ct.contactName, ct.contactEmail, ct.contactPhone AS ctPhone
       FROM job j
  LEFT JOIN applicationStatus a ON j.applicationStatusId = a.id
  LEFT JOIN company c ON j.companyId = c.id
  LEFT JOIN contact ct ON j.primaryContactId = ct.id
      WHERE j.id = ?"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (! $job) {
    $page->setBody('<h2>Job not found</h2><p><a href="jobs.php">Back to Jobs</a></p>');
    $page->displayPage();
    exit(0);
}

$title = Tools::htmlOut($job['positionTitle']);
$body = "<h2>$title</h2>\n";
$body .= "<p><a href=\"jobs.php\">&larr; Back to Jobs</a></p>\n";

// Job info
$body .= "<table>\n<caption>Job Information</caption>\n<tbody>\n";

// Company with link
$companyVal = '';
if ($job['companyId']) {
    $companyName = Tools::htmlOut($job['companyName'] ?? '');
    $companyVal = "<a href=\"companyDetail.php?id={$job['companyId']}\">$companyName</a>";
}
$body .= "  <tr><td style=\"font-weight: bold; width: 180px;\">Company</td><td>$companyVal</td></tr>\n";

// Contact with link
$contactVal = '';
if ($job['primaryContactId']) {
    $contactName = Tools::htmlOut($job['contactName'] ?? '');
    $contactVal = "<a href=\"contactDetail.php?id={$job['primaryContactId']}\">$contactName</a>";
    if ($job['contactEmail']) {
        $contactVal .= ' (' . Tools::htmlOut($job['contactEmail']) . ')';
    }
}
$body .= "  <tr><td style=\"font-weight: bold;\">Primary Contact</td><td>$contactVal</td></tr>\n";

$fields = [
    'Location' => $job['location'],
    'Status' => $job['statusValue'],
    'Last Status Change' => $job['lastStatusChange'],
    'Urgency' => $job['urgency'],
    'Next Action Due' => $job['nextActionDue'],
    'Next Action' => $job['nextAction'],
    'URL' => $job['url'],
    'Created' => $job['created'],
    'Updated' => $job['updated'],
];
foreach ($fields as $label => $value) {
    $val = Tools::htmlOut($value ?: '');
    if ($label === 'URL' && $value) {
        $val = "<a href=\"" . Tools::htmlOut($value) . "\" target=\"_blank\">$val</a>";
    }
    $body .= "  <tr><td style=\"font-weight: bold;\">$label</td><td>$val</td></tr>\n";
}
$body .= "</tbody>\n</table>\n";

// Keywords
$stmt = $dbh->prepare(
    "SELECT k.keywordValue FROM jobKeywordMap jkm
       JOIN keyword k ON jkm.keywordId = k.id
      WHERE jkm.jobId = ? ORDER BY jkm.sortKey"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$keywords = $stmt->get_result();
if ($keywords->num_rows > 0) {
    $kwList = [];
    while ($kw = $keywords->fetch_assoc()) {
        $kwList[] = Tools::htmlOut($kw['keywordValue']);
    }
    $body .= "<p><strong>Keywords:</strong> " . implode(', ', $kwList) . "</p>\n";
}
$stmt->close();

// Notes
$noteController = new NoteController('read');
$notes = $noteController->getByTableAndId('job', $id);
if (count($notes) > 0) {
    $body .= "<table>\n<caption>Notes (" . count($notes) . ")</caption>\n";
    $body .= "<thead><tr><th>Note</th><th>Created</th><th>Updated</th></tr></thead>\n<tbody>\n";
    foreach ($notes as $n) {
        $body .= "<tr>";
        $body .= "<td style=\"white-space: pre-wrap;\">" . Tools::htmlOut($n->getNoteText()) . "</td>";
        $body .= "<td>" . Tools::htmlOut($n->getCreated()) . "</td>";
        $body .= "<td>" . Tools::htmlOut($n->getUpdated()) . "</td>";
        $body .= "</tr>\n";
    }
    $body .= "</tbody>\n</table>\n";
}

$page->setBody($body);
$page->displayPage();
