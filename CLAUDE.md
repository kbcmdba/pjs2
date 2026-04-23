# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP Job Seeker 2 (PJS2) is a single-user job search tracking web application. It tracks searches, companies, jobs, contacts, application statuses, keywords, and notes. Requires Apache, PHP 8.0+, and MySQL 8.0+.

PJS2 is expected to evolve incrementally into PJS3 — a multi-user SaaS product rather than a separate rewrite. The transition will happen through architectural changes made over time (REST API, multi-tenancy, responsive web frontend). There is no fixed boundary between PJS2 and PJS3.

## Setup

1. Copy `config_sample.xml` to `config.xml` and update with your database/auth settings
2. Run `resetDb.php` in a browser to initialize the database (requires `resetOk=1` in config.xml)
3. Run `checkSetup.php` (CLI or browser) to verify configuration, database, tables, and API connectivity
4. Configuration is never committed — `config.xml` contains DB credentials and auth settings

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

## Future Direction (PJS3)

PJS2 will evolve toward a hosted multi-user SaaS product. Key goals:

- **REST API** — Initial API endpoints exist in `api/` (see above). Expand to cover all entities and eventually replace AJAX endpoints.
- **Multi-tenancy** — Add user management and per-user data isolation. Currently single-user with no user association on data rows.
- **Mobile-friendly web client** — A responsive SPA frontend (no native mobile apps) that works on phones and desktops, consuming the REST API.
- **Hosting** — Cloud deployment (AWS or another provider), accessible without requiring users to self-host or run a VPN.
- **Scale** — PJS3 must be able to scale 10,000x over PJS2.
- **Paid subscriptions** — Low monthly fee to cover infrastructure costs. Payment processor TBD (Stripe considered expensive for low price points; exploring alternatives like Square, Lemon Squeezy, or annual billing).
- **Tech stack** — Node.js. PJS2 stays PHP; PJS3 will be a new implementation in Node.js.

Features like RSS feed ingestion should be built now for single-user use and designed so they can be extended to multi-user later.

### Planned Schema Changes

New lookup tables (FK from job table) to support unemployment reporting and richer job tracking:

| Lookup table | FK on job | Seed values |
|---|---|---|
| `positionType` | `positionTypeId` | FTE, CTH, PTE, Contract, Seasonal, Freelance |
| `workModel` | `workModelId` | Remote, Hybrid, On-site |
| `applicationMethod` | `applicationMethodId` | Online, Email, Phone, In-person, Referral, Staffing Agency |
| `activityType` | `activityTypeId` | Applied, Networking, Job Fair, Workshop, Staffing Agency Visit, Training |

Plus `compRange` (VARCHAR) on job table for compensation range (format varies too much to normalize).

Each lookup table follows the `applicationStatus`/`searchStatus` pattern: id, value, sortKey.
