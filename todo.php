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

// @todo 10 Implement data validation tests for all required fields
// @todo 20 Add review panel workflow to Searches page (iframe + minimal controls, same pattern as Jobs)
// @todo 30 Give users control over sorting in each page (e.g., Jobs by status, then next action due)
// @todo 40 Jobs: Make it possible to log time and date together with jobs
// @todo 40 Allow moving a job entry to a search (and vice versa) when a URL is a search page, not a posting
// @todo 50 Add Notes column to Keywords listing (infrastructure exists, just needs wiring)
// @todo 60 Write KeywordFormView — tag jobs with keywords
// @todo 60 Write KeywordFormView::getKeywordSelectList( $selectedKeywordId, $readOnly )
// @todo 80 Make the interface multiuser
// @todo 80 User entitlements
// @todo 80 REST API for external access
// @todo 90 Make input fields mobile-friendly
// @todo 90 Style the site
// @todo 95 Add help to the site
// @todo 99 Build a manual on how to use the site.
// @todo 99 Future: Mobile-first hosted service — let job seekers track and
//           direct their search entirely from their smartphone. Multi-tenant,
//           billing, REST API, responsive/native UI.
//
// DONE 06 Implement last contact date for contacts
// DONE 06 Implement and test last contact date update button for contacts
// DONE 60 Write KeywordListView
