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
$page = new PJSWebPage($config->getTitle() . ' - Search Results');
$q = trim(Tools::param('q'));

if ($q === '') {
    $page->setBody('<h2>Search</h2><p>Enter a search term in the search box above.</p>');
    $page->displayPage();
    exit(0);
}

$qHtml = Tools::htmlOut($q);
$body = "<h2>Search Results for \"$qHtml\"</h2>\n";

$dbc = new DBConnection('read', null, null, null, null, null, 'mysqli', true);
$dbh = $dbc->getConnection();
$like = '%' . $q . '%';
$totalResults = 0;

// Jobs
$sql = "SELECT j.id, j.positionTitle, j.location, j.url, j.nextAction,
               c.companyName, a.statusValue
          FROM job j
     LEFT JOIN company c ON j.companyId = c.id
     LEFT JOIN applicationStatus a ON j.applicationStatusId = a.id
         WHERE j.positionTitle LIKE ?
            OR j.location LIKE ?
            OR j.nextAction LIKE ?
            OR j.url LIKE ?
      ORDER BY j.updated DESC
         LIMIT 25";
$stmt = $dbh->prepare($sql);
$stmt->bind_param('ssss', $like, $like, $like, $like);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $totalResults += $result->num_rows;
    $body .= "<h3>Jobs ({$result->num_rows})</h3>\n";
    $body .= "<table>\n<thead><tr><th>Position</th><th>Company</th><th>Location</th><th>Status</th><th>Next Action</th></tr></thead>\n<tbody>\n";
    while ($row = $result->fetch_assoc()) {
        $title = Tools::htmlOut($row['positionTitle']);
        $company = Tools::htmlOut($row['companyName'] ?? '');
        $location = Tools::htmlOut($row['location']);
        $status = Tools::htmlOut($row['statusValue'] ?? '');
        $nextAction = Tools::htmlOut($row['nextAction']);
        $id = $row['id'];
        $body .= "<tr style=\"cursor: pointer;\" onclick=\"updateJob('$id')\">";
        $body .= "<td>$title</td><td>$company</td><td>$location</td><td>$status</td><td>$nextAction</td>";
        $body .= "</tr>\n";
    }
    $body .= "</tbody>\n</table>\n";
}
$stmt->close();

// Companies
$sql = "SELECT id, companyName, companyCity, companyState, companyPhone, companyUrl
          FROM company
         WHERE companyName LIKE ?
            OR companyCity LIKE ?
            OR companyAddress1 LIKE ?
            OR companyPhone LIKE ?
            OR companyUrl LIKE ?
      ORDER BY updated DESC
         LIMIT 25";
$stmt = $dbh->prepare($sql);
$stmt->bind_param('sssss', $like, $like, $like, $like, $like);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $totalResults += $result->num_rows;
    $body .= "<h3>Companies ({$result->num_rows})</h3>\n";
    $body .= "<table>\n<thead><tr><th>Name</th><th>City</th><th>State</th><th>Phone</th><th>URL</th></tr></thead>\n<tbody>\n";
    while ($row = $result->fetch_assoc()) {
        $name = Tools::htmlOut($row['companyName']);
        $city = Tools::htmlOut($row['companyCity']);
        $state = Tools::htmlOut($row['companyState']);
        $phone = Tools::htmlOut($row['companyPhone']);
        $url = Tools::htmlOut($row['companyUrl']);
        $body .= "<tr><td>$name</td><td>$city</td><td>$state</td><td>$phone</td><td>$url</td></tr>\n";
    }
    $body .= "</tbody>\n</table>\n";
}
$stmt->close();

// Contacts
$sql = "SELECT ct.id, ct.contactName, ct.contactEmail, ct.contactPhone, c.companyName
          FROM contact ct
     LEFT JOIN company c ON ct.contactCompanyId = c.id
         WHERE ct.contactName LIKE ?
            OR ct.contactEmail LIKE ?
            OR ct.contactPhone LIKE ?
            OR ct.contactAlternatePhone LIKE ?
      ORDER BY ct.updated DESC
         LIMIT 25";
$stmt = $dbh->prepare($sql);
$stmt->bind_param('ssss', $like, $like, $like, $like);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $totalResults += $result->num_rows;
    $body .= "<h3>Contacts ({$result->num_rows})</h3>\n";
    $body .= "<table>\n<thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Company</th></tr></thead>\n<tbody>\n";
    while ($row = $result->fetch_assoc()) {
        $name = Tools::htmlOut($row['contactName']);
        $email = Tools::htmlOut($row['contactEmail']);
        $phone = Tools::htmlOut($row['contactPhone']);
        $company = Tools::htmlOut($row['companyName'] ?? '');
        $body .= "<tr><td>$name</td><td>$email</td><td>$phone</td><td>$company</td></tr>\n";
    }
    $body .= "</tbody>\n</table>\n";
}
$stmt->close();

// Notes
$sql = "SELECT id, appliesToTable, appliesToId, noteText, created
          FROM note
         WHERE noteText LIKE ?
      ORDER BY updated DESC
         LIMIT 25";
$stmt = $dbh->prepare($sql);
$stmt->bind_param('s', $like);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $totalResults += $result->num_rows;
    $body .= "<h3>Notes ({$result->num_rows})</h3>\n";
    $body .= "<table>\n<thead><tr><th>Entity</th><th>Note</th><th>Created</th></tr></thead>\n<tbody>\n";
    while ($row = $result->fetch_assoc()) {
        $entity = Tools::htmlOut($row['appliesToTable']) . ' #' . Tools::htmlOut($row['appliesToId']);
        $text = Tools::htmlOut($row['noteText']);
        if (strlen($text) > 200) {
            $text = substr($text, 0, 200) . '...';
        }
        $created = Tools::htmlOut($row['created']);
        $body .= "<tr><td>$entity</td><td>$text</td><td>$created</td></tr>\n";
    }
    $body .= "</tbody>\n</table>\n";
}
$stmt->close();

if ($totalResults === 0) {
    $body .= "<p>No results found.</p>\n";
}

$page->setBody($body);
$page->displayPage();
