# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP Job Seeker 2 (PJS2) is a single-user job search tracking web application. It tracks searches, companies, jobs, contacts, application statuses, keywords, and notes. Requires Apache or nginx+PHP-FPM, PHP 8.0+, and MySQL 8.0+.

**Status: Maintenance mode.** PJS2 is feature-complete. Only bug fixes and security fixes are accepted. New feature development has moved to Personal Job Seeker 3 (PJS3) — a new codebase in Node.js. The "PJS" acronym is being rebranded from "PHP Job Seeker" to "Personal Job Seeker" for the SaaS version.

## Setup

1. Copy `config_sample.php` to `config.php` and update with your database/auth settings
2. Run `resetDb.php` in a browser to initialize the database (requires `resetOk=1` in config.php)
3. Run `checkSetup.php` (CLI or browser) to verify configuration, database, tables, and API connectivity
4. Configuration is never committed — `config.php` contains DB credentials and auth settings

**Why config.php, not config.xml?** PHP files are processed by the web server, never served raw. An XML file in the webroot can be accessed directly by any browser, exposing credentials. This applies to both Apache and nginx — `.htaccess` only protects Apache. Legacy `config.xml` is still supported but `checkSetup.php` will warn to migrate.

## Running Tests

```bash
cd Tests
./runTests.sh
```

This runs a PHP syntax check on all `.php` files, then runs PHPUnit integration tests via `phpunit IntegrationTests.php`. It swaps in `config.xml.auth` during testing.

## Architecture

**Namespace:** `com\kbcmdba\pjs2`

**Autoloading:** `Libs/autoload.php` uses `spl_autoload_register` with class name pattern matching (not PSR-4). Classes are routed to directories based on suffix:
- `*Controller` / `ControllerBase` → `Libs/Controllers/`
- `*Model` / `ModelBase` → `Libs/Models/`
- `*View` / `ViewBase` → `Libs/Views/`
- `*Exception` → `Libs/Exceptions/`
- Everything else → `Libs/`

**MVC Pattern:**
- **Models** (`Libs/Models/`) — Data objects with getters/setters and validation methods (`validateForAdd`, `validateForUpdate`, `validateForDelete`). Extend `ModelBase`.
- **Controllers** (`Libs/Controllers/`) — Database access layer using mysqli prepared statements. Extend `ControllerBase`. Constructor takes a read/write mode (`"read"`, `"write"`, `"admin"`).
- **Views** (`Libs/Views/`) — HTML rendering. List views extend `ListViewBase`, summary views extend `SummaryViewBase`.

**AJAX Endpoints:** Top-level `AJAX*.php` files handle CRUD operations (Add, Get, Update, Delete) for each entity. Each corresponds to a JS file in `js/` (e.g., `ajaxJob.js` ↔ `AJAXAddJob.php`, `AJAXUpdateJob.php`, etc.).

**Page Files:** Top-level PHP files (`jobs.php`, `companies.php`, `contacts.php`, `searches.php`, `applicationStatuses.php`, `keywords.php`) are the main page entry points. Each includes `Libs/autoload.php` and renders via `PJSWebPage`.

**Detail Pages:** `jobDetail.php`, `companyDetail.php`, `contactDetail.php` show a single entity with all related data (cross-linked contacts, jobs, notes). Support breadcrumb navigation via `Libs/Breadcrumb.php` using `from`/`fromId`/`fromName` query parameters.

**Global Search:** `globalSearch.php` searches across jobs, companies, contacts, and notes. Search box is in the nav bar on every page. Results link to detail pages with search context preserved in breadcrumbs.

**Reports:** `reports.php` generates weekly/monthly job search activity reports for unemployment agency compliance. Printable output with date range selection.

**Auth:** `Libs/Auth.php` handles authentication against the `user` database table (bcrypt passwords, roles: `admin`, `user`, `viewer`). Config.xml's `userId`/`userPassword` are legacy fields not used by the current auth flow. Session timeout is controlled by `authTimeoutSeconds` in config.xml (default 3600). Can be bypassed with `skipAuth=1` in config.xml for development.

**Notes:** AJAX-based notes system (`js/ajaxNote.js`, `AJAXGetNotes.php`, `AJAXAddNote.php`, `AJAXUpdateNote.php`, `AJAXDeleteNote.php`) provides a shared modal for viewing, adding, editing, and deleting notes. Used by Jobs, Companies, and Contacts listings via `NoteController::countByTable()` for counts and `getByTableAndId()` for content.

## Database Access

See [DATABASE.md](DATABASE.md) for table schemas, common DML operations, and useful queries. Direct CLI access: `mysql --defaults-file=~/.my.claude.cnf -e "SQL HERE;"`

- **`~/.my.claude.cnf`** contains the pjs2_app credentials (user, password, host, database). Always use `--defaults-file=~/.my.claude.cnf` instead of inline `-u`/`-p` flags to avoid exposing credentials on the command line.
- **DB hosts:** Production app runs on web1.hole with mysql1.hole serving the database. dc1.hole redirects all `/pjs2/` requests to web1.hole (301 via `.htaccess`).
- **Trigger definer:** All triggers use `DEFINER='dba_sproc'@'localhost'` — a dedicated account with full privileges on the pjs2 database. Never create triggers without an explicit `DEFINER` clause, as MySQL will use the current connection user, which may lack the required grants when the app connects later.
- **Job posting URLs go stale fast** — always verify a URL is live (not 404/410/closed) before adding or updating a job in the database.
- **Duplicate URL detection:** `AJAXCheckDuplicateUrl.php` and `JobController::getByUrl()` check for duplicate job URLs. The JS checks on blur of the URL field; PHP checks on add and update.

## Deployment

Production is on web1.hole. To deploy:

```bash
ssh web1.hole "sudo -u www-data git -C /var/www/html/pjs2 pull"
```

The repo on web1.hole is owned by `www-data`, so git commands must run as that user. Schema changes (ALTER TABLE, triggers) must be applied separately on mysql1.hole using `mysql --defaults-file=~/.my.cnf -h mysql1.hole -D pjs2`.

## Code Style

The project follows PSR-2 coding standards (recent commits focus on PSR-2 compliance).

## REST API

API endpoints live in `api/` and are authenticated via API key (not session/CSRF). The key is set in `config.xml` as `apiKey` and passed in the `X-API-Key` request header. Validated by `Libs/ApiAuth.php` using `hash_equals()`.

JSON request bodies are decoded into `$_REQUEST`/`$_POST` by `ApiAuth::populateRequestFromJson()` so that existing model validation methods (which read `Tools::param()`) work without modification. **Note:** Not all models need this — `CompanyModel::validateForAdd()` reads from model properties, but `JobModel` and `ContactModel` read from `$_REQUEST`. Call `populateRequestFromJson()` for any endpoint where the model's validation uses `Tools::param()`.

**CWD gotcha:** API files in `api/` must call `chdir(__DIR__ . '/..')` before requiring the autoloader, because `Config.php` reads `config.xml` relative to CWD.

**API key:** Managed via Ansible Vault (`vault_pjs2_api_key` in `~/claude/projects/Ansible-Terraform`), templated into `config.xml` on deploy. Never hardcode the key.

### Endpoints

| Method | URL | Purpose |
|--------|-----|---------|
| `GET` | `api/jobs.php` | List all jobs |
| `GET` | `api/jobs.php?id=X` | Get job by ID |
| `GET` | `api/jobs.php?url=X` | Check for duplicate job by URL |
| `POST` | `api/jobs.php` | Create a job |
| `PUT` | `api/jobs.php` | Update a job (id required, duplicate URL check) |
| `GET` | `api/companies.php` | List all companies |
| `GET` | `api/companies.php?id=X` | Get company by ID |
| `GET` | `api/companies.php?name=X` | Find companies by name (returns all matches) |
| `POST` | `api/companies.php` | Create a company |
| `PUT` | `api/companies.php` | Update a company (id required) |
| `GET` | `api/contacts.php` | List all contacts |
| `GET` | `api/contacts.php?id=X` | Get contact by ID |
| `GET` | `api/contacts.php?email=X` | Find contact by email |
| `POST` | `api/contacts.php` | Create a contact |
| `PUT` | `api/contacts.php` | Update a contact (id required) |
| `GET` | `api/notes.php?id=X` | Get note by ID |
| `GET` | `api/notes.php?appliesToTable=X&appliesToId=Y` | List notes for an entity |
| `POST` | `api/notes.php` | Create a note |
| `PUT` | `api/notes.php` | Update a note (id required, noteText only) |

No DELETE endpoints exist by design — deletions are done manually to prevent accidental data loss.

### Response Format

Success: HTTP 200/201 + `{"result": "OK", ...}`
Not found: HTTP 404 + `{"result": "FAILED", "error": "... not found"}`
Failure: HTTP 400/401/409/500 + `{"result": "FAILED", "error": "message"}`

### Consumer

The primary API consumer is [JobImporter](~/claude/projects/JobImporter) — a cron job that parses forwarded job emails and imports them into PJS2.

**Known issue:** The API is not reachable from web1.hole CLI (nginx returns 502/301 for local requests). External HTTP requests work correctly (verified via `checkSetup.php` in browser). The nginx vhost config on web1 may need adjustment for local loopback requests. This is an Ansible infrastructure issue, not a PJS2 code issue.

**NoteModel gotcha:** `NoteModel::validateForUpdate()` requires `appliesToTable` and `appliesToId` in `$_REQUEST` even though they're immutable on update. The API PUT endpoint works around this by populating `$_REQUEST` from the existing model before calling update.

**`appliesToTable` value: singular, not plural.** POST and GET on `/api/notes.php` both expect singular table names (`company`, `job`, `contact`). Sending plural returns HTTP 500. Verified 2026-04-26.

## Notes as durable cross-session memory

Notes attached to jobs / companies / contacts are not just convenience tracking — they're the system's persistence layer for the *why* behind decisions across sessions. When a session ends, the conversational thread fades; PJS2 notes carry the decision rationale forward so the next session (or a future Claude session, or KB days later) can pick up without losing context. Examples from 2026-04-26 / 2026-04-27:

- F500 sweep results captured per-company so Kathy can see effort breadth
- HHAeXchange email-format research (RocketReach 76% pattern, best-guess addresses for Bill Thomalla and Juan Fernandez)
- Tandem re-engagement strategy (pivot to management framing after the careers@ inbox went silent)
- Airbnb values-veto rationale (so future sweeps don't re-surface)
- Ritesh Shrestha's connection-points (Akron alumni, Denver-metro overlap) used to draft the LinkedIn outreach

When adding a note, write it to be useful to a *future reader*, not just the current moment.

## Successor: Personal Job Seeker 3 (PJS3)

PJS2 is being succeeded by **Personal Job Seeker 3 (PJS3)** — a new codebase (not evolution) in Node.js. PJS3 goals:

- **Multi-tenancy** — Per-user data isolation from day one
- **Mobile-friendly web client** — Responsive SPA (no native apps) consuming a REST API
- **Cloud hosting** — Available to others without self-hosting
- **Unemployment reporting** — Jurisdiction-specific compliance (US states + Canada EI)
- **Paid subscriptions** — Affordable monthly fee; payment processor TBD (alternatives to Stripe being evaluated for low price points)
- **Tech stack** — Node.js

The schema improvements planned below will likely land in PJS3, not PJS2, since PJS2 is maintenance-only.

### Schema Improvements Planned for PJS3

Lookup tables to support unemployment reporting and richer job tracking:

| Lookup table | FK on job | Seed values |
|---|---|---|
| `positionType` | `positionTypeId` | FTE, CTH, PTE, Contract, Seasonal, Freelance |
| `workModel` | `workModelId` | Remote, Hybrid, On-site |
| `applicationMethod` | `applicationMethodId` | Online, Email, Phone, In-person, Referral, Staffing Agency |
| `foundMethod` | `foundMethodId` | Online, News, Paper, Referral, Word-of-Mouth, Agency, Other |
| `activityType` | `activityTypeId` | Applied, Networking, Job Fair, Workshop, Staffing Agency Visit, Training |

`applicationMethod` is in PJS3 MVP scope; `foundMethod` is in `~/work/pjs3/docs/POST_MVP_BACKLOG.md`. Each lookup table follows the `applicationStatus`/`searchStatus` pattern: id, value, sortKey.

### Schema changes shipped in PJS2 (daily-friction overrides maintenance discipline)

- **`compRangeLow` / `compRangeHigh`** (`INT UNSIGNED NULL`) on `job`. Whole USD/year. NULL = not disclosed; one-bound-only displays as "$170,000+" or "up to $200,000". Sortable in the jobs list (sort uses upper bound). Shipped 2026-04-26 because the friction of NOT having it was real every job-add session. PJS3 should consider per-workspace currency.

## UI / Workflow Patterns

### Sortable list columns (jobs.php, contacts.php, companies.php)

Column headers: `class="sortable"`, `data-sort-type` ("text" | "num" | "date" | "urgency"), and `onclick="sortTable(this, columnIndex, tableId)"` (or table-specific wrapper like `sortJobsTable`). Cells whose visible content has HTML stacking (Comp Range, Status when ordered by `sortKey`) carry `data-sort` with the comparable raw value. Active column shows `^` (asc) or `v` (desc); inactive sortable columns show `♦`. Sort is client-side (~50-row scale; no SQL ORDER BY round-trip).

Paired-row tables (CompanyListView's 2-rows-per-company) pass `{ pairRows: true }` so units stay glued during sort. Pair-grouping is preserved visually via `table#companies tbody tr:nth-child(4n+1/4n+2 vs 4n+3/4n+4)` CSS bands rather than `treven`/`trodd` classes (which carry with rows on sort and break alternation).

### Dashboard click-through filters

`jobs.php` reads `?filter=overdue|dueToday|dueWeek` and `?urgency=high|medium|low` URL params and renders a filtered list using "active-only" controller methods that JOIN on `applicationStatus.isActive = 1`. Closed-state statuses (CLOSED, INVALID, MISMATCH, DUPLICATE, MISSING, UNAVAILABLE) are excluded automatically. Filtered views show a "← Show all jobs" link.

### Error page hierarchy on entry-point scripts

Wrap the body in `function main()` and put `try { main(); } catch (...)` on the *caller* — KB's preferred shape for entry-point scripts because adding/widening defensive error handling stays trivial later. Catch tiers, in order:

1. `DaoException` → "Database Error" with DB-targeted causes (DNS to mysql1, network, refused, schema)
2. `ControllerException` → "Controller Error" (query / schema-drift / bind_param mismatch)
3. `\Throwable` → "Unexpected Error" with honest "I'm not sure what category" message + logs pointer

`DBConnection.php` wraps `mysqli_sql_exception` (PHP 8.1+ throws on connect failure) into `DaoException` so the layered hierarchy actually fires. `ControllerBase.__construct` does NOT catch+rewrap (the previous code lost the original type and dereferenced a null `_dbh` in its catch handler — fixed 2026-04-27).

Currently applied to `index.php`. Other entry-point scripts (jobs.php, jobDetail.php, companies.php, etc.) still have older shapes and will be migrated as they're touched next.

## Additional gotchas (continued)

**`JobController` bind_param type-string fragility:** The `INSERT` and `UPDATE` queries use positional `?` placeholders with a manual type-string ('iiisssssssii...') paired to a positional variable list. Adding a column means updating: SELECT list, bind_result vars + setters, INSERT column list, INSERT VALUES placeholders, INSERT bind_param + type-string, UPDATE SET clause, UPDATE bind_param + type-string. Off-by-one in the type-string hits at runtime as `mysqli_stmt::bind_param: ArgumentCountError`. Bit twice on the comp-range column add 2026-04-27. Refactor candidate: array-based binding via `mysqli_stmt::execute([...])` so the type-string can be inferred or omitted.

**`lastStatusChange` zero-date trap:** `DBConnection.php` sets `SQL_MODE = 'ALLOW_INVALID_DATES'`, so an empty timestamp from an API POST silently lands as `'0000-00-00 00:00:00'`. The job's inline-edit form then renders empty for that field, the JS date validator rejects empty, and the user can't save *any* change to that job. Fix is server-side defaulting on writes — for state-change timestamps, default conditionally: preserve existing value when the triggering state (`applicationStatusId`) is unchanged; default to NOW only when the state actually changed. Otherwise NOW corrupts the field's semantics on every save.
