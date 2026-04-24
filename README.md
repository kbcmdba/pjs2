# PHP Job Seeker 2 (PJS2)

PJS2 is a single-user job search tracking web application. It tracks job applications, companies, contacts, searches, application statuses, keywords, and notes — everything a job seeker needs to stay organized during an active search.

**Status: Maintenance mode.** PJS2 is feature-complete. Only bug fixes and security fixes will be accepted. New feature development is moving to [Personal Job Seeker 3 (PJS3)](#whats-next).

## Features

- **Job tracking** with application status, urgency, next actions, and due dates
- **Company management** with address, phone, URL, and agency relationships
- **Contact tracking** linked to companies, with phone, email, and last-contacted dates
- **Notes system** attachable to jobs, companies, and contacts
- **Global search** across all entities from a search box in the nav bar
- **Detail pages** for jobs, companies, and contacts with breadcrumb navigation
- **Reports** for weekly/monthly job search activity (unemployment agency compliance)
- **REST API** for programmatic access (GET, POST, PUT — no DELETE by design)
- **Setup validation** via `checkSetup.php` (CLI and browser)
- **Job review panel** for reviewing job postings inline
- **Duplicate URL detection** to prevent duplicate job entries

## Requirements

- PHP 8.0+
- MySQL 8.0+
- Apache or nginx with PHP-FPM
- HTTPS recommended

## Quick Start

1. Extract files into your web server's document root
2. Copy `config_sample.php` to `config.php` and update with your database credentials
3. Create the MySQL database and user:
   ```sql
   CREATE DATABASE pjs2;
   CREATE USER 'pjs2_app'@'localhost' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON pjs2.* TO 'pjs2_app'@'localhost';
   ```
4. Set `resetOk` to `1` in `config.php` and visit `resetDb.php` in your browser to initialize the database
5. Set `resetOk` back to `0`
6. Run `checkSetup.php` in your browser to verify everything is configured correctly
7. Log in and start tracking your job search

## Configuration

Configuration lives in `config.php` (not XML) because PHP files cannot be served raw by any web server, preventing accidental exposure of database credentials. A legacy `config.xml` format is still supported but deprecated.

See `config_sample.php` for all available settings.

## REST API

API endpoints in `api/` are authenticated via API key (`X-API-Key` header). Set the `apiKey` value in `config.php`.

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `api/jobs.php` | List/search/get jobs |
| POST | `api/jobs.php` | Create a job |
| PUT | `api/jobs.php` | Update a job |
| GET | `api/companies.php` | List/search/get companies |
| POST | `api/companies.php` | Create a company |
| PUT | `api/companies.php` | Update a company |
| GET | `api/contacts.php` | List/search/get contacts |
| POST | `api/contacts.php` | Create a contact |
| PUT | `api/contacts.php` | Update a contact |
| GET | `api/notes.php` | Get notes by ID or entity |
| POST | `api/notes.php` | Create a note |
| PUT | `api/notes.php` | Update a note |

DELETE endpoints are intentionally omitted — deletions are done manually.

## License

This software is licensed under GPLv2. See the [LICENSE](LICENSE) file for details.

## What's Next

PJS2 is being succeeded by **Personal Job Seeker 3 (PJS3)** — a multi-user SaaS application built in Node.js. PJS3 will offer:

- Multi-tenancy with per-user data isolation
- Responsive web frontend (mobile and desktop)
- Cloud hosting (no self-hosting required)
- Unemployment reporting with jurisdiction-specific compliance
- Paid subscriptions at an affordable price point for active job seekers

The "PJS" acronym is being rebranded from "PHP Job Seeker" to "Personal Job Seeker" to reflect the tech stack change and broader vision.
