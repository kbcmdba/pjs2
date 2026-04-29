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

    // Helper: build "Label: value [Copy]" line. Used in row 2 for each of
    // the TN-unemployment "one of" required fields (phone, email, address,
    // URL) so KB can paste any of them straight into the portal.
    $buildCopyField = function (string $label, string $value, bool $isLink = false): string {
        if ($value === '') {
            return '<div><strong>' . $label . ':</strong> <em style="color: #999;">[not on file]</em></div>';
        }
        $safe = Tools::htmlOut($value);
        $display = $isLink
            ? "<a href=\"$safe\" target=\"_blank\">$safe</a>"
            : $safe;
        return '<div><strong>' . $label . ':</strong> ' . $display
            . " <button type=\"button\" class=\"copy-url-btn\" data-url=\"$safe\""
            . " style=\"margin-left: 8px; padding: 2px 8px; font-size: 0.85em; cursor: pointer;\">Copy</button></div>";
    };

    $rowStyle = 'treven';
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
        // Default to 'Online'. The vast majority of applications are online; a
        // per-job applicationMethod override field is the next feature so the
        // rare mail / phone / in-person / referral cases get reported correctly.
        $method = 'Online';
        $nextAction = Tools::htmlOut($row['nextAction'] ?? '');
        // Assemble full company address from the parts available on the
        // company record. TN's form wants a complete street address, not
        // just city/state.
        $addressParts = [];
        if (! empty($row['companyAddress1'])) {
            $addressParts[] = $row['companyAddress1'];
        }
        if (! empty($row['companyAddress2'])) {
            $addressParts[] = $row['companyAddress2'];
        }
        $cityStateZip = trim(
            ($row['companyCity'] ?? '')
            . (! empty($row['companyState']) ? ', ' . $row['companyState'] : '')
            . (! empty($row['companyZip']) ? ' ' . $row['companyZip'] : '')
        );
        if ($cityStateZip !== '' && $cityStateZip !== ',') {
            $addressParts[] = $cityStateZip;
        }
        $fullAddress = implode(', ', $addressParts);

        // Build all four "one of" fields (phone, email, address, URL). TN
        // unemployment requires *any one* of these per entry; surfacing all
        // four with copy buttons lets KB pick whichever the row supplies.
        $urlValue = ($method === 'Online') ? ($row['url'] ?? '') : '';
        $contactBlockContent = $buildCopyField('Phone', $row['companyPhone'] ?? '')
                             . $buildCopyField('Email', $row['contactEmail'] ?? '')
                             . $buildCopyField('Address', $fullAddress)
                             . $buildCopyField('Posting URL', $urlValue, true);

        // Row 1: data columns. Class on tr keeps both rows of one job grouped
        // by background color (treven/trodd overrides the per-row nth-child).
        $body .= "    <tr class=\"$rowStyle\">";
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
        // Row 2: contact block spans the full table width.
        $body .= "    <tr class=\"$rowStyle\">";
        $body .= "<td colspan=\"9\" style=\"padding-left: 30px; word-break: break-all;\">$contactBlockContent</td>";
        $body .= "</tr>\n";
        $rowStyle = ($rowStyle === 'treven') ? 'trodd' : 'treven';
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

<script>
// Copy URL to clipboard from any .copy-url-btn. Used on the activity report
// so KB can paste application URLs straight into the unemployment portal
// without manual select+copy.
document.querySelectorAll('.copy-url-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var url = btn.getAttribute('data-url');
        navigator.clipboard.writeText(url).then(function() {
            var orig = btn.textContent;
            btn.textContent = 'Copied!';
            setTimeout(function() { btn.textContent = orig; }, 1500);
        }).catch(function() {
            btn.textContent = 'Copy failed';
            setTimeout(function() { btn.textContent = 'Copy'; }, 1500);
        });
    });
});
</script>
HTML;

$page->setBody($body);
$page->displayPage();
