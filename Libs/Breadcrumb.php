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

/**
 * Breadcrumb navigation helper for detail pages.
 *
 * Reads 'from', 'q', 'fromId', and 'fromName' query parameters to build
 * a contextual breadcrumb trail.
 */
class Breadcrumb
{
    /**
     * Build a breadcrumb HTML string for a detail page.
     *
     * @param string $entityType  'job', 'company', or 'contact'
     * @param string $entityName  Display name of the current entity
     * @return string HTML breadcrumb
     */
    public static function render($entityType, $entityName)
    {
        $from = Tools::param('from');
        $q = Tools::param('q');
        $fromId = Tools::param('fromId');
        $fromName = Tools::param('fromName');

        $listPages = [
            'job' => ['url' => 'jobs.php', 'label' => 'Jobs'],
            'company' => ['url' => 'companies.php', 'label' => 'Companies'],
            'contact' => ['url' => 'contacts.php', 'label' => 'Contacts'],
        ];

        $crumbs = [];
        $crumbs[] = '<a href="index.php">Home</a>';

        switch ($from) {
            case 'search':
                $qEnc = Tools::htmlOut($q);
                $crumbs[] = "<a href=\"globalSearch.php?q=" . urlencode($q) . "\">Search: \"$qEnc\"</a>";
                break;

            case 'company':
                $crumbs[] = '<a href="companies.php">Companies</a>';
                if ($fromId) {
                    $name = Tools::htmlOut($fromName ?: "Company #$fromId");
                    $crumbs[] = "<a href=\"companyDetail.php?id=$fromId\">$name</a>";
                }
                break;

            case 'contact':
                $crumbs[] = '<a href="contacts.php">Contacts</a>';
                if ($fromId) {
                    $name = Tools::htmlOut($fromName ?: "Contact #$fromId");
                    $crumbs[] = "<a href=\"contactDetail.php?id=$fromId\">$name</a>";
                }
                break;

            case 'job':
                $crumbs[] = '<a href="jobs.php">Jobs</a>';
                if ($fromId) {
                    $name = Tools::htmlOut($fromName ?: "Job #$fromId");
                    $crumbs[] = "<a href=\"jobDetail.php?id=$fromId\">$name</a>";
                }
                break;

            default:
                // No from context — link to the entity's listing page
                if (isset($listPages[$entityType])) {
                    $crumbs[] = '<a href="' . $listPages[$entityType]['url'] . '">'
                              . $listPages[$entityType]['label'] . '</a>';
                }
                break;
        }

        $crumbs[] = Tools::htmlOut($entityName);

        return '<nav style="margin: 8px 12px; font-size: 0.9em; color: #666;">'
             . implode(' &rsaquo; ', $crumbs)
             . "</nav>\n";
    }

    /**
     * Build query string parameters to pass navigation context to a detail page.
     *
     * @param string $fromType  'company', 'contact', 'job', or 'search'
     * @param int    $fromId    ID of the source entity (not used for 'search')
     * @param string $fromName  Display name of the source entity
     * @return string  URL query string fragment (starts with &)
     */
    public static function buildFromParams($fromType, $fromId = null, $fromName = null)
    {
        $params = '&from=' . urlencode($fromType);
        if ($fromId !== null) {
            $params .= '&fromId=' . urlencode($fromId);
        }
        if ($fromName !== null) {
            $params .= '&fromName=' . urlencode($fromName);
        }
        // Pass through search query if we're in a search context
        $q = Tools::param('q');
        if ($q !== '') {
            $params .= '&q=' . urlencode($q);
        }
        return $params;
    }
}
