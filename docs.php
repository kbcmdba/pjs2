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

require_once "Libs/autoload.php";

$config = new Config();
$page = new PJSWebPage($config->getTitle() . ' - API Docs');

// Render the applicationStatus enum table dynamically so it stays in sync
// with the database. Also drives the rendering of the state-machine flow
// chart below (terminal vs active states are determined by isActive).
$asController = new ApplicationStatusController('read');
$statuses = $asController->getAll();

$enumRowsHtml = '';
foreach ($statuses as $s) {
    $style = htmlspecialchars($s->getStyle(), ENT_QUOTES, 'UTF-8');
    $value = htmlspecialchars($s->getStatusValue(), ENT_QUOTES, 'UTF-8');
    $active = $s->getIsActive() ? 'Yes' : 'No (closed)';
    $enumRowsHtml .= sprintf(
        '<tr><td>%d</td><td style="%s padding: 4px 10px;">%s</td><td>%s</td><td>%d</td></tr>' . "\n",
        (int) $s->getId(),
        $style,
        $value,
        $active,
        (int) $s->getSortKey()
    );
}

$body = <<<HTML
<style>
  .docs { max-width: 980px; margin: 0 auto; padding: 0 16px; line-height: 1.5; }
  .docs h2 { color: #4a4a8a; margin-top: 32px; }
  .docs h3 { color: #333; margin-top: 24px; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
  .docs h4 { color: #555; margin-top: 18px; margin-bottom: 6px; }
  .docs code { background: #f4f4f4; padding: 1px 5px; border-radius: 3px; font-size: 0.92em; }
  .docs pre { background: #f4f4f4; padding: 10px 14px; border-left: 3px solid #888; overflow-x: auto; font-size: 0.88em; }
  .docs table { border-collapse: collapse; margin: 8px 0 16px; }
  .docs th, .docs td { border: 1px solid #ccc; padding: 6px 10px; text-align: left; vertical-align: top; }
  .docs th { background: #eee; }
  .method { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 0.82em; font-weight: bold; color: #fff; margin-right: 6px; }
  .m-get { background: #4a8a4a; }
  .m-post { background: #4a4a8a; }
  .m-put { background: #8a8a4a; }
  .note { background: #fff8e1; border-left: 3px solid #f9a825; padding: 8px 12px; margin: 10px 0; font-size: 0.92em; }
  .gotcha { background: #fce4e4; border-left: 3px solid #c00; padding: 8px 12px; margin: 10px 0; font-size: 0.92em; }
</style>

<div class="docs">

<h2>PJS2 REST API Documentation</h2>

<p>The PJS2 REST API enables programmatic management of jobs, companies, contacts, and notes. It is intended for local automation (e.g., the <code>JobImporter</code> email parser, <code>job-update</code> sweep skill, scripted outreach logging) running on the same network as web1.hole.</p>

<h3>Base URL</h3>
<pre>https://web1.hole/pjs2/api/</pre>
<p>Also accessible via <code>https://dc1.hole/pjs2/api/</code> which redirects to web1.</p>

<h3>Authentication</h3>
<p>All endpoints require an API key passed in the <code>X-API-Key</code> request header. The key is stored at <code>~/.pjs2_api_key</code> on KB's workstation and on cron1.hole. <strong>Never check the key into git.</strong></p>
<pre>API_KEY=\$(cat ~/.pjs2_api_key)
curl -sk -H "X-API-Key: \$API_KEY" "https://web1.hole/pjs2/api/jobs.php"</pre>

<h3>Response format</h3>
<table>
<tr><th>Outcome</th><th>HTTP</th><th>Body shape</th></tr>
<tr><td>Success</td><td>200 / 201</td><td><code>{"result":"OK", ...}</code></td></tr>
<tr><td>Not found</td><td>404</td><td><code>{"result":"FAILED", "error":"... not found"}</code></td></tr>
<tr><td>Bad request / failure</td><td>400 / 401 / 409 / 500</td><td><code>{"result":"FAILED", "error":"message"}</code></td></tr>
</table>

<h3>Known issues / gotchas</h3>
<div class="gotcha">
<ul>
<li><strong>Notes API <code>appliesToTable</code> is singular</strong>: use <code>contact</code>, <code>job</code>, <code>company</code> &mdash; plural returns HTTP 500. Verified 2026-04-26.</li>
<li><strong>Jobs PUT requires all fields preserved</strong>: <code>urgency</code>, <code>positionTitle</code>, <code>location</code> are required even on partial updates. Fields not included in the PUT body get nulled out (verified 2026-04-28: a status-only PUT erased <code>companyId</code>, <code>url</code>, <code>compRangeLow/High</code>).</li>
<li><strong><code>nextAction</code> and <code>nextActionDue</code> may not save via PUT</strong> &mdash; use the notes endpoint as a workaround.</li>
<li><strong>No DELETE endpoints exist by design</strong> &mdash; deletions are done manually via SQL.</li>
<li><strong>web1 hosting</strong>: web1 is LXC container 128 on pm1. If the API returns HTTP 502, restart with <code>ssh pm1 "pct stop 128 && sleep 5 && pct start 128"</code>.</li>
</ul>
</div>

<h2>Endpoints</h2>

<h3>Jobs</h3>

<h4><span class="method m-get">GET</span> <code>api/jobs.php</code></h4>
<p>List all jobs.</p>
<pre>curl -sk -H "X-API-Key: \$API_KEY" "https://web1.hole/pjs2/api/jobs.php"</pre>
<p>Response: <code>{"result":"OK","count":N,"jobs":[{...}]}</code></p>

<h4><span class="method m-get">GET</span> <code>api/jobs.php?id=X</code></h4>
<p>Get a single job by id.</p>
<pre>curl -sk -H "X-API-Key: \$API_KEY" "https://web1.hole/pjs2/api/jobs.php?id=66"</pre>

<h4><span class="method m-get">GET</span> <code>api/jobs.php?url=X</code></h4>
<p>Check for an existing job with this URL (duplicate detection).</p>
<pre>curl -sk -H "X-API-Key: \$API_KEY" "https://web1.hole/pjs2/api/jobs.php?url=https://example.com/job/123"</pre>

<h4><span class="method m-post">POST</span> <code>api/jobs.php</code></h4>
<p>Create a job. Required: <code>companyId</code>, <code>positionTitle</code>, <code>location</code>, <code>urgency</code>, <code>applicationStatusId</code>.</p>
<table>
<tr><th>Field</th><th>Type</th><th>Notes</th></tr>
<tr><td>companyId</td><td>int</td><td>FK to company.id</td></tr>
<tr><td>positionTitle</td><td>string</td><td>Job title</td></tr>
<tr><td>url</td><td>string</td><td>Posting URL (used for dup detection)</td></tr>
<tr><td>location</td><td>string</td><td>"Remote", "Bristol, TN", etc.</td></tr>
<tr><td>urgency</td><td>enum</td><td><code>low</code>, <code>medium</code>, <code>high</code></td></tr>
<tr><td>applicationStatusId</td><td>int</td><td>FK to applicationStatus.id (see enum below)</td></tr>
<tr><td>compRangeLow</td><td>int</td><td>Lower bound of comp, whole USD/year (NULL = not disclosed)</td></tr>
<tr><td>compRangeHigh</td><td>int</td><td>Upper bound of comp, whole USD/year</td></tr>
<tr><td>nextAction</td><td>string</td><td>Free text, what's next on this job</td></tr>
<tr><td>nextActionDue</td><td>date</td><td>YYYY-MM-DD</td></tr>
</table>
<pre>curl -sk -X POST -H "X-API-Key: \$API_KEY" -H "Content-Type: application/json" \\
  -d '{"companyId":52,"positionTitle":"Sr Manager DBA","url":"...","location":"Remote","urgency":"medium","applicationStatusId":1,"compRangeLow":170000,"compRangeHigh":185000,"nextAction":"Follow up with Bill"}' \\
  "https://web1.hole/pjs2/api/jobs.php"</pre>

<h4><span class="method m-put">PUT</span> <code>api/jobs.php</code></h4>
<p>Update a job. <code>id</code> required. Also requires <code>urgency</code>, <code>positionTitle</code>, <code>location</code> to be in the body even for partial updates &mdash; missing fields will be nulled.</p>
<pre>curl -sk -X PUT -H "X-API-Key: \$API_KEY" -H "Content-Type: application/json" \\
  -d '{"id":66,"applicationStatusId":3,"urgency":"medium","positionTitle":"Sr Manager DBA","location":"Remote"}' \\
  "https://web1.hole/pjs2/api/jobs.php"</pre>

<h4>Application Status enum</h4>
<p>Rendered dynamically from <code>applicationStatus</code> table via <code>ApplicationStatusController::getAll()</code> so this stays in sync as statuses are added or modified. Cell color reflects each status's actual <code>style</code> column.</p>
<table>
<tr><th>id</th><th>statusValue</th><th>Active in dashboard</th><th>sortKey</th></tr>
{$enumRowsHtml}</table>

<p>The enum is also exposed as a read-only API endpoint:</p>
<h4><span class="method m-get">GET</span> <code>api/applicationStatuses.php</code></h4>
<p>List all application statuses (active and closed). Returns array of <code>{id, statusValue, isActive, sortKey, style, summaryCount, created, updated}</code>.</p>
<pre>curl -sk -H "X-API-Key: \$API_KEY" "https://web1.hole/pjs2/api/applicationStatuses.php"</pre>

<h4><span class="method m-get">GET</span> <code>api/applicationStatuses.php?id=X</code></h4>
<p>Get one status by id.</p>

<div class="note">This endpoint is <strong>read-only</strong> by design. applicationStatus rows are reference data managed via SQL/migrations, not arbitrary user CRUD. POST/PUT return 405.</div>

<h4>State-machine convention</h4>
<p>The diagram below shows the <strong>conventional</strong> transitions between application statuses as KB uses them in practice. <strong>The flow is NOT enforced anywhere in PJS</strong> — any status can transition to any other status at the data layer (e.g., CHASING &rarr; NETWORKING or CHASING &rarr; FOUND is legal SQL even though it's not a typical workflow). The diagram is a reading aid, not a constraint.</p>

<div class="note">
<strong>Notes on the diagram:</strong>
<ul>
<li>Solid arrows show the most common forward transitions during a real job-search cycle.</li>
<li>NETWORKING is treated as a parallel ambient state for relationship-building; it may feed into CONTACTED but often stands alone.</li>
<li>Any active state (FOUND / CONTACTED / APPLIED / INTERVIEWING / FOLLOWUP / CHASING / NETWORKING / STALE) can transition directly to any terminal state (UNAVAILABLE / INVALID / DUPLICATE / MISSING / MISMATCH / CLOSED) — these "shut down at any time" edges are not drawn for clarity.</li>
<li>STALE is rendered as an active state (isActive=1) but semantically sits between active pursuit and closed; treat it as "soft-paused, may revive."</li>
</ul>
</div>

<pre class="mermaid">
flowchart LR
    FOUND --> CONTACTED
    FOUND --> APPLIED
    CONTACTED --> APPLIED
    CONTACTED --> STALE
    APPLIED --> INTERVIEWING
    APPLIED --> FOLLOWUP
    APPLIED --> STALE
    FOLLOWUP --> INTERVIEWING
    FOLLOWUP --> CHASING
    CHASING --> INTERVIEWING
    CHASING --> STALE
    INTERVIEWING --> CLOSED
    NETWORKING -.parallel.-> CONTACTED

    classDef terminal fill:#222,color:#fff
    class CLOSED terminal
    classDef stale fill:#E0FFFF,color:#000
    class STALE stale
</pre>

<script type="module">
  import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.esm.min.mjs';
  mermaid.initialize({ startOnLoad: true, theme: 'default' });
</script>

<h3>Companies</h3>

<h4><span class="method m-get">GET</span> <code>api/companies.php</code></h4>
<p>List all companies.</p>

<h4><span class="method m-get">GET</span> <code>api/companies.php?id=X</code></h4>
<p>Get one company by id.</p>

<h4><span class="method m-get">GET</span> <code>api/companies.php?name=X</code></h4>
<p>Search companies by name (substring match).</p>
<pre>curl -sk -H "X-API-Key: \$API_KEY" "https://web1.hole/pjs2/api/companies.php?name=HHAeXchange"</pre>

<h4><span class="method m-post">POST</span> <code>api/companies.php</code></h4>
<p>Create a company.</p>
<table>
<tr><th>Field</th><th>Type</th><th>Notes</th></tr>
<tr><td>companyName</td><td>string</td><td>Required</td></tr>
<tr><td>url</td><td>string</td><td>Careers page or company homepage</td></tr>
<tr><td>companyAddress1, companyAddress2</td><td>string</td><td>Optional</td></tr>
<tr><td>companyCity, companyState, companyZip</td><td>string</td><td>Optional</td></tr>
<tr><td>companyPhone</td><td>string</td><td>Optional</td></tr>
</table>
<pre>curl -sk -X POST -H "X-API-Key: \$API_KEY" -H "Content-Type: application/json" \\
  -d '{"companyName":"The Voleon Group","url":"https://voleon.com/"}' \\
  "https://web1.hole/pjs2/api/companies.php"</pre>

<h4><span class="method m-put">PUT</span> <code>api/companies.php</code></h4>
<p>Update a company. <code>id</code> required.</p>

<h3>Contacts</h3>

<h4><span class="method m-get">GET</span> <code>api/contacts.php</code></h4>
<p>List all contacts.</p>

<h4><span class="method m-get">GET</span> <code>api/contacts.php?id=X</code></h4>
<p>Get one contact by id.</p>

<h4><span class="method m-get">GET</span> <code>api/contacts.php?email=X</code></h4>
<p>Find a contact by email address (exact match).</p>

<h4><span class="method m-post">POST</span> <code>api/contacts.php</code></h4>
<p>Create a contact.</p>
<table>
<tr><th>Field</th><th>Type</th><th>Notes</th></tr>
<tr><td>contactName</td><td>string</td><td>Required. Stored as a single field, not first/last.</td></tr>
<tr><td>contactCompanyId</td><td>int</td><td>FK to company.id (links the contact to their employer)</td></tr>
<tr><td>contactEmail</td><td>string</td><td>Optional</td></tr>
<tr><td>contactPhone</td><td>string</td><td>Optional</td></tr>
<tr><td>contactAlternatePhone</td><td>string</td><td>Optional</td></tr>
</table>
<pre>curl -sk -X POST -H "X-API-Key: \$API_KEY" -H "Content-Type: application/json" \\
  -d '{"contactName":"Bill Thomalla","contactCompanyId":52,"contactEmail":""}' \\
  "https://web1.hole/pjs2/api/contacts.php"</pre>

<h4><span class="method m-put">PUT</span> <code>api/contacts.php</code></h4>
<p>Update a contact. <code>id</code> required.</p>

<h3>Notes</h3>

<div class="note">Notes are PJS2's durable cross-session memory layer. Outreach decisions, rationale for skipped jobs, status-change context, and anything else worth keeping &mdash; record it as a note on the relevant job, contact, or company.</div>

<h4><span class="method m-get">GET</span> <code>api/notes.php?id=X</code></h4>
<p>Get a single note by id.</p>

<h4><span class="method m-get">GET</span> <code>api/notes.php?appliesToTable=X&amp;appliesToId=Y</code></h4>
<p>List all notes attached to a given entity. <strong><code>appliesToTable</code> must be singular</strong> &mdash; <code>contact</code>, <code>job</code>, or <code>company</code>. Plural values return HTTP 500.</p>
<pre>curl -sk -H "X-API-Key: \$API_KEY" \\
  "https://web1.hole/pjs2/api/notes.php?appliesToTable=contact&amp;appliesToId=16"</pre>

<h4><span class="method m-post">POST</span> <code>api/notes.php</code></h4>
<p>Create a note attached to an entity.</p>
<table>
<tr><th>Field</th><th>Type</th><th>Notes</th></tr>
<tr><td>appliesToTable</td><td>string</td><td><code>contact</code>, <code>job</code>, or <code>company</code> (singular)</td></tr>
<tr><td>appliesToId</td><td>int</td><td>FK to the relevant table</td></tr>
<tr><td>noteText</td><td>string</td><td>Free text. Lead with date for searchability (e.g., "2026-04-28 outreach: ...")</td></tr>
</table>
<pre>curl -sk -X POST -H "X-API-Key: \$API_KEY" -H "Content-Type: application/json" \\
  -d '{"appliesToTable":"contact","appliesToId":16,"noteText":"2026-04-28 LinkedIn DM sent..."}' \\
  "https://web1.hole/pjs2/api/notes.php"</pre>

<h4><span class="method m-put">PUT</span> <code>api/notes.php</code></h4>
<p>Update a note. <code>id</code> required. Only <code>noteText</code> can be modified &mdash; the entity binding is immutable (delete and re-create if you need to move a note to a different entity).</p>

<h3>Reference: convention summary</h3>
<ul>
<li>API key in <code>X-API-Key</code> header on every request.</li>
<li>JSON request bodies for POST/PUT (<code>Content-Type: application/json</code>).</li>
<li><code>-sk</code> on curl: silent mode + insecure (web1.hole is LAN-only with self-signed cert).</li>
<li>Notes API <code>appliesToTable</code> is <strong>singular</strong>.</li>
<li>Jobs PUT preserves only fields included in the body &mdash; always include <code>urgency</code>, <code>positionTitle</code>, <code>location</code> at minimum.</li>
<li>Use notes as the durable record for anything that doesn't fit a structured field.</li>
</ul>

</div>
HTML;

$page->setBody($body);
$page->displayPage();
