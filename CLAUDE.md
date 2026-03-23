# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP Job Seeker 2 (PJS2) is a single-user job search tracking web application. It tracks searches, companies, jobs, contacts, application statuses, keywords, and notes. Requires Apache, PHP 5.6+, and MySQL 5.6+.

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

See [DATABASE.md](DATABASE.md) for table schemas, common DML operations, and useful queries. Direct CLI access: `mysql pjs2 -e "SQL HERE;"`

## Code Style

The project follows PSR-2 coding standards (recent commits focus on PSR-2 compliance).
