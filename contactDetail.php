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
    header('Location: contacts.php');
    exit(0);
}

$page = new PJSWebPage($config->getTitle() . ' - Contact Detail');

$dbc = new DBConnection('read', null, null, null, null, null, 'mysqli', true);
$dbh = $dbc->getConnection();

// Get contact with company
$stmt = $dbh->prepare(
    "SELECT ct.*, c.companyName, c.id AS compId
       FROM contact ct
  LEFT JOIN company c ON ct.contactCompanyId = c.id
      WHERE ct.id = ?"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$contact = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (! $contact) {
    $page->setBody('<h2>Contact not found</h2><p><a href="contacts.php">Back to Contacts</a></p>');
    $page->displayPage();
    exit(0);
}

$name = Tools::htmlOut($contact['contactName']);
$body = Breadcrumb::render('contact', $contact['contactName']);
$body .= "<h2>$name</h2>\n";

// Contact info
$body .= "<table>\n<caption>Contact Information</caption>\n<tbody>\n";

// Company with link
$companyVal = '';
if ($contact['contactCompanyId']) {
    $companyName = Tools::htmlOut($contact['companyName'] ?? '');
    $fromParams = Breadcrumb::buildFromParams('contact', $id, $contact['contactName']);
    $companyVal = "<a href=\"companyDetail.php?id={$contact['compId']}$fromParams\">$companyName</a>";
}
$body .= "  <tr><td style=\"font-weight: bold; width: 150px;\">Company</td><td>$companyVal</td></tr>\n";

$fields = [
    'Email' => $contact['contactEmail'],
    'Phone' => $contact['contactPhone'],
    'Alternate Phone' => $contact['contactAlternatePhone'],
    'Last Contacted' => $contact['lastContacted'],
    'Created' => $contact['created'],
    'Updated' => $contact['updated'],
];
foreach ($fields as $label => $value) {
    $val = Tools::htmlOut($value ?: '');
    if ($label === 'Email' && $value) {
        $val = "<a href=\"mailto:" . Tools::htmlOut($value) . "\">$val</a>";
    }
    $body .= "  <tr><td style=\"font-weight: bold;\">$label</td><td>$val</td></tr>\n";
}
$body .= "</tbody>\n</table>\n";

// Jobs where this contact is primary
$stmt = $dbh->prepare(
    "SELECT j.id, j.positionTitle, j.location, j.lastStatusChange,
            a.statusValue, c.companyName
       FROM job j
  LEFT JOIN applicationStatus a ON j.applicationStatusId = a.id
  LEFT JOIN company c ON j.companyId = c.id
      WHERE j.primaryContactId = ?
   ORDER BY j.lastStatusChange DESC"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$jobs = $stmt->get_result();
if ($jobs->num_rows > 0) {
    $body .= "<table>\n<caption>Jobs ({$jobs->num_rows})</caption>\n";
    $body .= "<thead><tr><th>Position</th><th>Company</th><th>Location</th><th>Status</th><th>Last Status Change</th></tr></thead>\n<tbody>\n";
    while ($j = $jobs->fetch_assoc()) {
        $jTitle = Tools::htmlOut($j['positionTitle']);
        $jId = $j['id'];
        $body .= "<tr>";
        $fromParams = Breadcrumb::buildFromParams('contact', $id, $contact['contactName']);
        $body .= "<td><a href=\"jobDetail.php?id=$jId$fromParams\">$jTitle</a></td>";
        $body .= "<td>" . Tools::htmlOut($j['companyName'] ?? '') . "</td>";
        $body .= "<td>" . Tools::htmlOut($j['location']) . "</td>";
        $body .= "<td>" . Tools::htmlOut($j['statusValue'] ?? '') . "</td>";
        $body .= "<td>" . Tools::htmlOut($j['lastStatusChange']) . "</td>";
        $body .= "</tr>\n";
    }
    $body .= "</tbody>\n</table>\n";
}
$stmt->close();

// Notes
$noteController = new NoteController('read');
$notes = $noteController->getByTableAndId('contact', $id);
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
