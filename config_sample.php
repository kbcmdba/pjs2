<?php

/**
 * PJS2 Configuration
 *
 * Copy this file to config.php and update with your settings.
 * PHP config files cannot be served raw by any web server,
 * preventing accidental exposure of credentials.
 *
 * NEVER commit config.php — it contains secrets.
 */

return [
    'timeZone'           => 'America/Chicago',
    'title'              => 'PHP Job Seeker 2',
    'dbHost'             => '127.0.0.1',
    'dbName'             => 'pjs2',
    'dbPort'             => '3306',
    'dbUser'             => 'pjs2_app',
    'dbPass'             => 'SomethingComplicated',
    'userId'             => 'ChangeToYourUserInterfaceId',
    'userPassword'       => 'ChangeToYourInterfacePassword',

    // ONLY ENABLE THE FOLLOWING FOR DEVELOPMENT/TESTING
    // MAY CAUSE DATA LOSS!!! DO NOT ENABLE IN YOUR PERMANENT DATA STORE
    'resetOk'            => '0',

    // ONLY ENABLE THE FOLLOWING FOR DEVELOPMENT/TESTING
    // DO NOT ENABLE ON YOUR PUBLIC WEB SITE
    'skipAuth'           => '0',

    // Session timeout in seconds (default: 3600)
    'authTimeoutSeconds' => '3600',

    // API key for REST API access (required for api/ endpoints)
    'apiKey'             => 'ChangeThisToARandomString',
];
