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

// === Active PJS2 punch list ===
// PJS2 is in maintenance / dogfooding mode while PJS3 is being built. Items
// here are the minimum needed to keep PJS2 usable for daily job-search work.
// Larger features moved to ~/work/pjs3/docs/POST_MVP_BACKLOG.md on 2026-04-26.
//
// @todo 40 Application Method + Found Method: add two columns to job — applicationMethod and foundMethod (both VARCHAR with sensible defaults), expose via Model/Controller/API, add select dropdowns in the jobs list inline edit and the review panel bar, and update reports.php to read from these columns with 'Online' fallback. Two distinct concepts: applicationMethod = how the user applied (Online, Email, Phone, In-person, Referral, Staffing Agency), foundMethod = how the user discovered the job (Online, News, Paper, Referral, Word-of-Mouth, Agency, Other). Both are needed for unemployment activity reporting which wants breadth of search activity. Phase 1 (default applicationMethod 'Online' in reports.php) shipped 2026-04-26; this is phase 2. PJS3 MVP_SCOPE already includes applicationMethod as a workspace-scoped lookup; foundMethod placement (MVP vs post-MVP) is an open PJS3 decision as of 2026-04-26. If PJS3 ships before this becomes painful, defer entirely.
//
// === Migrated to PJS3 (2026-04-26) ===
// MIGRATED 20 Review panel workflow on Searches page → PJS3 docs/POST_MVP_BACKLOG.md
// MIGRATED 30 User-controlled sorting per page → PJS3 docs/POST_MVP_BACKLOG.md
// MIGRATED 40 Log time+date with jobs → PJS3 docs/POST_MVP_BACKLOG.md
// MIGRATED 40 Move job ↔ search → PJS3 docs/POST_MVP_BACKLOG.md
// MIGRATED 95 Help system → PJS3 docs/POST_MVP_BACKLOG.md
// MIGRATED 99 User manual → PJS3 docs/POST_MVP_BACKLOG.md
//
// === Dropped — already addressed in PJS3 MVP scope or rejected (2026-04-26) ===
// DROPPED 10 Data validation tests for all required fields — PJS3 ships TDD discipline as baseline; PJS2 will not be retrofitted
// DROPPED 50 Notes column on Keywords listing — Keywords "Undecided" in PJS3 MVP_SCOPE.md (may not return)
// DROPPED 60 KeywordFormView — Keywords-dependent (see above)
// DROPPED 60 KeywordFormView::getKeywordSelectList — Keywords-dependent (see above)
// DROPPED 80 Multiuser — PJS3 MVP ships workspace tenancy
// DROPPED 80 User entitlements — PJS3 MVP ships workspace roles (Owner, Viewer)
// DROPPED 80 REST API for external access — PJS3 already deferred-and-named (post-MVP)
// DROPPED 90 Mobile-friendly inputs — PJS3 MVP "Responsive web UI" non-functional
// DROPPED 90 Style the site — implicit in PJS3 React stack
// DROPPED 99 Future: Mobile-first hosted service — this is what PJS3 is becoming
//
// === Historical (PJS2 completed) ===
// DONE 06 Implement last contact date for contacts
// DONE 06 Implement and test last contact date update button for contacts
// DONE 10 Jobs list: Add Details button per row → jobDetail.php (2026-04-26)
// DONE 15 Review panel: Notes button with count badge → openNotesModal (2026-04-26)
// DONE 20 Review panel: Encode state in URL via ?jobId=X (refresh-safe, forwardable, print-pasteable) (2026-04-26)
// DONE 25 Review panel: Details button → jobDetail.php (2026-04-26)
// DONE 25 Review panel: Show Next/Skip past last active job (labeled "N closed" so the boundary is visible) (2026-04-26)
// DONE 30 Activity Report: Per-row Copy buttons for phone, email, address, URL (TN "one of" requirement) (2026-04-26)
// DONE 35 index.php: main() pattern + outer try/catch on layered semantic exceptions (DaoException -> "Database Error" with DB-targeted causes, \Throwable -> "Unexpected" with honest "I don't know" fallback). DBConnection wraps mysqli_sql_exception so DaoException catches DB issues. (2026-04-26)
// DONE 60 Write KeywordListView
