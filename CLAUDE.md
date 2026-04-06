# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP Job Seeker 2 (PJS2) is a single-user job search tracking web application. It tracks searches, companies, jobs, contacts, application statuses, keywords, and notes. Requires Apache, PHP 5.6+, and MySQL 5.6+.

PJS2 is expected to evolve incrementally into PJS3 — a multi-user SaaS product rather than a separate rewrite. The transition will happen through architectural changes made over time (REST API, multi-tenancy, responsive web frontend). There is no fixed boundary between PJS2 and PJS3.

## Setup

1. Copy `config_sample.xml` to `config.xml` and update with your database/auth settings
2. Run `resetDb.php` in a browser to initialize the database (requires `resetOk=1` in config.xml)
3. Configuration is never committed — `config.xml` contains DB credentials and auth settings

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

**Auth:** `Libs/Auth.php` handles authentication. Can be bypassed with `skipAuth=1` in config.xml for development.

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

## Future Direction (PJS3)

PJS2 will evolve toward a hosted multi-user SaaS product. Key goals:

- **REST API** — Replace AJAX endpoints with a proper JSON REST API. The existing controller/model architecture was designed with this in mind.
- **Multi-tenancy** — Add user management and per-user data isolation. Currently single-user with no user association on data rows.
- **Mobile-friendly web client** — A responsive SPA frontend (no native mobile apps) that works on phones and desktops, consuming the REST API.
- **Hosting** — Cloud deployment (AWS or another provider), accessible without requiring users to self-host or run a VPN.
- **Scale** — PJS3 must be able to scale 10,000x over PJS2.
- **Paid subscriptions** — Low monthly fee to cover infrastructure costs. Payment processor TBD (Stripe considered expensive for low price points; exploring alternatives like Square, Lemon Squeezy, or annual billing).
- **Tech stack** — Undecided. PJS3 may stay in PHP or move to a different language/framework. The decision will be driven by concrete benefits, not change for its own sake. Frameworks are acceptable if they don't create heavy lock-in.

Features like RSS feed ingestion should be built now for single-user use and designed so they can be extended to multi-user later.
