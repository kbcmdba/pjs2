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
    header('Location: companies.php');
    exit(0);
}

$page = new PJSWebPage($config->getTitle() . ' - Company Detail');

$dbc = new DBConnection('read', null, null, null, null, null, 'mysqli', true);
$dbh = $dbc->getConnection();

// Get company
$stmt = $dbh->prepare("SELECT * FROM company WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (! $company) {
    $page->setBody('<h2>Company not found</h2><p><a href="companies.php">Back to Companies</a></p>');
    $page->displayPage();
    exit(0);
}

$name = Tools::htmlOut($company['companyName']);
$body = Breadcrumb::render('company', $company['companyName']);
$body .= "<h2>$name</h2>\n";

// Company info
$body .= "<table>\n<caption>Company Information</caption>\n<tbody>\n";
$fields = [
    'Address' => trim(($company['companyAddress1'] ?: '') . ' ' . ($company['companyAddress2'] ?: '')),
    'City/State/Zip' => trim(implode(', ', array_filter([
        $company['companyCity'],
        $company['companyState'],
    ])) . ' ' . ($company['companyZip'] ?: '')),
    'Phone' => $company['companyPhone'],
    'Website' => $company['companyUrl'],
    'Last Contacted' => $company['lastContacted'],
    'Created' => $company['created'],
    'Updated' => $company['updated'],
];
foreach ($fields as $label => $value) {
    $val = Tools::htmlOut($value ?: '');
    if ($label === 'Website' && $value) {
        $val = "<a href=\"" . Tools::htmlOut($value) . "\" target=\"_blank\">$val</a>";
    }
    $body .= "  <tr><td style=\"font-weight: bold; width: 150px;\">$label</td><td>$val</td></tr>\n";
}
$body .= "</tbody>\n</table>\n";

// Agency info
if ($company['agencyCompanyId']) {
    $stmt = $dbh->prepare("SELECT companyName FROM company WHERE id = ?");
    $stmt->bind_param('i', $company['agencyCompanyId']);
    $stmt->execute();
    $agency = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($agency) {
        $agencyName = Tools::htmlOut($agency['companyName']);
        $agencyId = $company['agencyCompanyId'];
        $body .= "<p><strong>Agency:</strong> <a href=\"companyDetail.php?id=$agencyId\">$agencyName</a></p>\n";
    }
}

// Contacts at this company
$stmt = $dbh->prepare(
    "SELECT id, contactName, contactEmail, contactPhone, contactAlternatePhone, lastContacted
       FROM contact WHERE contactCompanyId = ? ORDER BY contactName"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$contacts = $stmt->get_result();
if ($contacts->num_rows > 0) {
    $body .= "<table>\n<caption>Contacts ({$contacts->num_rows})</caption>\n";
    $body .= "<thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Alt Phone</th><th>Last Contacted</th></tr></thead>\n<tbody>\n";
    while ($c = $contacts->fetch_assoc()) {
        $cName = Tools::htmlOut($c['contactName']);
        $cId = $c['id'];
        $body .= "<tr>";
        $fromParams = Breadcrumb::buildFromParams('company', $id, $company['companyName']);
        $body .= "<td><a href=\"contactDetail.php?id=$cId$fromParams\">$cName</a></td>";
        $body .= "<td>" . Tools::htmlOut($c['contactEmail']) . "</td>";
        $body .= "<td>" . Tools::htmlOut($c['contactPhone']) . "</td>";
        $body .= "<td>" . Tools::htmlOut($c['contactAlternatePhone']) . "</td>";
        $body .= "<td>" . Tools::htmlOut($c['lastContacted'] ?? '') . "</td>";
        $body .= "</tr>\n";
    }
    $body .= "</tbody>\n</table>\n";
}
$stmt->close();

// Jobs at this company
$stmt = $dbh->prepare(
    "SELECT j.id, j.positionTitle, j.location, j.url, j.lastStatusChange, j.nextAction,
            a.statusValue
       FROM job j
  LEFT JOIN applicationStatus a ON j.applicationStatusId = a.id
      WHERE j.companyId = ?
   ORDER BY j.lastStatusChange DESC"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$jobs = $stmt->get_result();
if ($jobs->num_rows > 0) {
    $body .= "<table>\n<caption>Jobs ({$jobs->num_rows})</caption>\n";
    $body .= "<thead><tr><th>Position</th><th>Location</th><th>Status</th><th>Last Status Change</th><th>Next Action</th></tr></thead>\n<tbody>\n";
    while ($j = $jobs->fetch_assoc()) {
        $jTitle = Tools::htmlOut($j['positionTitle']);
        $jId = $j['id'];
        $body .= "<tr>";
        $fromParams = Breadcrumb::buildFromParams('company', $id, $company['companyName']);
        $body .= "<td><a href=\"jobDetail.php?id=$jId$fromParams\">$jTitle</a></td>";
        $body .= "<td>" . Tools::htmlOut($j['location']) . "</td>";
        $body .= "<td>" . Tools::htmlOut($j['statusValue'] ?? '') . "</td>";
        $body .= "<td>" . Tools::htmlOut($j['lastStatusChange']) . "</td>";
        $body .= "<td>" . Tools::htmlOut($j['nextAction']) . "</td>";
        $body .= "</tr>\n";
    }
    $body .= "</tbody>\n</table>\n";
}
$stmt->close();

// Notes
$noteController = new NoteController('read');
$notes = $noteController->getByTableAndId('company', $id);
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
