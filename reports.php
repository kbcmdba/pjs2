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
$page = new PJSWebPage($config->getTitle() . ' - Reports');

$period = Tools::param('period') ?: 'week';
$startDate = Tools::param('startDate');
$endDate = Tools::param('endDate');

// Calculate default date range
if ($startDate === '' || $endDate === '') {
    $now = new \DateTime();
    if ($period === 'month') {
        $startDate = $now->format('Y-m-01');
        $endDate = $now->format('Y-m-t');
    } else {
        // Default to current week (Monday-Sunday)
        $day = (int) $now->format('N'); // 1=Mon, 7=Sun
        $monday = clone $now;
        $monday->modify('-' . ($day - 1) . ' days');
        $sunday = clone $monday;
        $sunday->modify('+6 days');
        $startDate = $monday->format('Y-m-d');
        $endDate = $sunday->format('Y-m-d');
    }
}

$startHtml = Tools::htmlOut($startDate);
$endHtml = Tools::htmlOut($endDate);

// Date range picker form
$weekSelected = ($period === 'week') ? 'selected="selected"' : '';
$monthSelected = ($period === 'month') ? 'selected="selected"' : '';

$body = <<<HTML
<h2>Job Search Activity Report</h2>
<form method="get" action="reports.php" style="margin: 8px 12px 16px;">
  <label>Period:
    <select name="period" onchange="this.form.submit()">
      <option value="week" $weekSelected>Weekly</option>
      <option value="month" $monthSelected>Monthly</option>
    </select>
  </label>
  <label style="margin-left: 12px;">From:
    <input type="date" name="startDate" value="$startHtml" class="datepicker" />
  </label>
  <label style="margin-left: 8px;">To:
    <input type="date" name="endDate" value="$endHtml" class="datepicker" />
  </label>
  <button type="submit" style="margin-left: 12px;">Generate</button>
  <button type="button" onclick="window.print()" style="margin-left: 8px;">Print</button>
</form>

HTML;

// Query job activity within date range
$dbc = new DBConnection('read', null, null, null, null, null, 'mysqli', true);
$dbh = $dbc->getConnection();

$endDatePlusOne = date('Y-m-d', strtotime($endDate . ' +1 day'));

// Jobs with status changes in the date range
$sql = "SELECT j.id, j.positionTitle, j.location, j.url, j.lastStatusChange, j.nextAction,
               c.companyName, c.companyAddress1, c.companyCity, c.companyState, c.companyZip,
               c.companyPhone, c.companyUrl,
               a.statusValue,
               ct.contactName, ct.contactEmail, ct.contactPhone AS contactPhone
          FROM job j
     LEFT JOIN company c ON j.companyId = c.id
     LEFT JOIN applicationStatus a ON j.applicationStatusId = a.id
     LEFT JOIN contact ct ON j.primaryContactId = ct.id
         WHERE j.lastStatusChange >= ?
           AND j.lastStatusChange < ?
      ORDER BY j.lastStatusChange ASC";

$stmt = $dbh->prepare($sql);
$stmt->bind_param('ss', $startDate, $endDatePlusOne);
$stmt->execute();
$result = $stmt->get_result();

$body .= "<div id=\"reportContent\">\n";
$body .= "<p style=\"margin: 8px 12px;\"><strong>Report Period:</strong> $startHtml to $endHtml</p>\n";

if ($result->num_rows === 0) {
    $body .= "<p style=\"margin: 8px 12px;\">No job search activity found for this period.</p>\n";
} else {
    $body .= "<p style=\"margin: 8px 12px;\"><strong>Total Activities:</strong> {$result->num_rows}</p>\n";
    $body .= <<<'HTML'
<table id="reportTable">
  <thead>
    <tr>
      <th>Date</th>
      <th>Company</th>
      <th>Company Location</th>
      <th>Company Phone</th>
      <th>Contact Person</th>
      <th>Position Title</th>
      <th>Activity/Status</th>
      <th>Method</th>
      <th>Result/Next Action</th>
    </tr>
  </thead>
  <tbody>
HTML;

    while ($row = $result->fetch_assoc()) {
        $date = date('m/d/Y', strtotime($row['lastStatusChange']));
        $company = Tools::htmlOut($row['companyName'] ?? '');
        $location = '';
        if ($row['companyCity'] || $row['companyState']) {
            $parts = [];
            if ($row['companyCity']) {
                $parts[] = $row['companyCity'];
            }
            if ($row['companyState']) {
                $parts[] = $row['companyState'];
            }
            $location = Tools::htmlOut(implode(', ', $parts));
        }
        if (! $location && $row['location']) {
            $location = Tools::htmlOut($row['location']);
        }
        $phone = Tools::htmlOut($row['companyPhone'] ?? '');
        $contact = Tools::htmlOut($row['contactName'] ?? '');
        $title = Tools::htmlOut($row['positionTitle']);
        $status = Tools::htmlOut($row['statusValue'] ?? '');
        // Infer method from URL presence
        $method = ($row['url'] && $row['url'] !== '') ? 'Online' : '';
        $nextAction = Tools::htmlOut($row['nextAction'] ?? '');

        $body .= "    <tr>";
        $body .= "<td>$date</td>";
        $body .= "<td>$company</td>";
        $body .= "<td>$location</td>";
        $body .= "<td>$phone</td>";
        $body .= "<td>$contact</td>";
        $body .= "<td>$title</td>";
        $body .= "<td>$status</td>";
        $body .= "<td>$method</td>";
        $body .= "<td>$nextAction</td>";
        $body .= "</tr>\n";
    }
    $body .= "  </tbody>\n</table>\n";
}
$stmt->close();

$body .= "</div>\n";

// Print-specific CSS
$body .= <<<'HTML'
<style>
@media print {
    #navBar, form, button, .note-count-link { display: none !important; }
    #reportContent { margin: 0; }
    #reportTable { width: 100%; font-size: 10pt; }
    #reportTable th, #reportTable td { padding: 4px 6px; border: 1px solid #333; }
    h2 { margin-top: 0; }
}
</style>
HTML;

$page->setBody($body);
$page->displayPage();
