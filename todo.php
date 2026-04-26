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
// @todo 15 Review panel: Show notes count badge + click-through to view/add notes — currently the bar shows status / next action / due but never notes (biggest spouse-visibility gap; Kathy can't see the deliberate effort otherwise)
// @todo 25 Review panel: Add a View Details link to jobDetail.php — faster than closing review and clicking from the list
// @todo 25 Review panel: Show Next/Skip even when reviewQueueActiveRemaining() === 0, OR add an "all jobs" toggle so the user can navigate beyond the active-queue end (encountered 2026-04-26 on the last active job — UX dead-end)
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
// DONE 20 Review panel: Encode state in URL via ?jobId=X (refresh-safe, forwardable, print-pasteable) (2026-04-26)
// DONE 60 Write KeywordListView
