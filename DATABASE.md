# PJS2 Database Reference

Direct database access: `mysql pjs2 -e "SQL HERE;"`

For timestamps with MySQL 8 strict mode, prefix with: `SET SESSION sql_mode = '';`

## Application Statuses (lookup table)

| ID | Status        | Active |
|----|---------------|--------|
| 1  | FOUND         | yes    |
| 2  | CONTACTED     | yes    |
| 3  | APPLIED       | yes    |
| 4  | INTERVIEWING  | yes    |
| 5  | FOLLOWUP      | yes    |
| 6  | CHASING       | yes    |
| 7  | NETWORKING    | yes    |
| 8  | UNAVAILABLE   | no     |
| 9  | INVALID       | no     |
| 10 | DUPLICATE     | no     |
| 11 | CLOSED        | no     |

## Common DML

### Add a company

```sql
INSERT INTO company (companyName, companyAddress1, companyAddress2, companyCity, companyState, companyZip, companyPhone, companyUrl, created)
VALUES ('Acme Corp', '123 Main St', '', 'Dallas', 'TX', '75201', '', 'https://acme.example.com', NOW());
```

### Add a contact

```sql
INSERT INTO contact (contactCompanyId, contactName, contactEmail, contactPhone, contactAlternatePhone, created)
VALUES ((SELECT id FROM company WHERE companyName = 'Acme Corp'), 'Jane Smith', 'jane@acme.com', '555-1234', '', NOW());
```

### Add a job

```sql
INSERT INTO job (companyId, applicationStatusId, positionTitle, location, url, urgency, nextAction, nextActionDue, lastStatusChange, created)
VALUES (
  (SELECT id FROM company WHERE companyName = 'Acme Corp'),
  1,  -- FOUND
  'Senior DBA',
  'Dallas, TX (Remote)',
  'https://acme.example.com/jobs/123',
  'medium',
  'Review posting and apply',
  DATE_ADD(NOW(), INTERVAL 2 DAY),
  NOW(),
  NOW()
);
```

### Update job status

```sql
UPDATE job SET applicationStatusId = 3, lastStatusChange = NOW(), nextAction = 'Follow up in one week', nextActionDue = DATE_ADD(NOW(), INTERVAL 7 DAY) WHERE id = ?;
```

### Mark contact as contacted

```sql
UPDATE contact SET lastContacted = NOW() WHERE id = ?;
```

### Mark company as contacted

```sql
UPDATE company SET lastContacted = NOW() WHERE id = ?;
```

### Add a keyword

```sql
INSERT INTO keyword (keywordValue, sortKey, created) VALUES ('DBA', 0, NOW());
```

### Tag a job with a keyword

```sql
INSERT INTO jobKeywordMap (jobId, keywordId, sortKey, created)
VALUES (?, (SELECT id FROM keyword WHERE keywordValue = 'DBA'), 0, NOW());
```

### Add a note

```sql
INSERT INTO note (appliesToTable, appliesToId, noteText, created)
VALUES ('job', ?, 'Had phone screen, went well. Next step is technical interview.', NOW());
```

### Add a search

```sql
INSERT INTO search (engineName, searchName, url, rssFeedUrl, created)
VALUES ('LinkedIn', 'DBA jobs Dallas remote', 'https://linkedin.com/jobs/search?...', '', NOW());
```

## Useful Queries

### Active jobs with upcoming actions

```sql
SELECT j.id, j.positionTitle, c.companyName, a.statusValue, j.nextAction, j.nextActionDue, j.urgency
  FROM job j
  LEFT JOIN company c ON c.id = j.companyId
  LEFT JOIN applicationStatus a ON a.id = j.applicationStatusId
 WHERE a.isActive = 1
 ORDER BY j.nextActionDue ASC;
```

### Jobs by status

```sql
SELECT j.id, j.positionTitle, c.companyName, j.nextAction, j.nextActionDue
  FROM job j
  LEFT JOIN company c ON c.id = j.companyId
 WHERE j.applicationStatusId = ?
 ORDER BY j.lastStatusChange DESC;
```

### Overdue actions

```sql
SELECT j.id, j.positionTitle, c.companyName, a.statusValue, j.nextAction, j.nextActionDue
  FROM job j
  LEFT JOIN company c ON c.id = j.companyId
  LEFT JOIN applicationStatus a ON a.id = j.applicationStatusId
 WHERE a.isActive = 1 AND j.nextActionDue < NOW()
 ORDER BY j.nextActionDue ASC;
```

### Search for a company by name

```sql
SELECT id, companyName FROM company WHERE companyName LIKE '%acme%';
```

## Table Schemas

### company
id, agencyCompanyId (FK->company), companyName, companyAddress1, companyAddress2, companyCity, companyState, companyZip, companyPhone, companyUrl, created, updated, lastContacted

### contact
id, contactCompanyId (FK->company), contactName, contactEmail, contactPhone, contactAlternatePhone, lastContacted, created, updated

### job
id, primaryContactId (FK->contact), companyId (FK->company), applicationStatusId (FK->applicationStatus), isActiveSummary, lastStatusChange, urgency (high/medium/low), created, updated, nextActionDue, nextAction, positionTitle, location, url

### keyword
id, keywordValue (UNIQUE), sortKey, created, updated

### jobKeywordMap
id, jobId (FK->job), keywordId (FK->keyword), sortKey, created, updated

### note
id, appliesToTable (enum: job/company/contact/keyword/search), appliesToId, created, updated, noteText

### search
id, engineName, searchName, url, rssFeedUrl, rssLastChecked, created, updated

### applicationStatus
id, statusValue, isActive, sortKey, style, summaryCount, created, updated
