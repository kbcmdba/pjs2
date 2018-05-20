<?php

/**
 * phpjobseeker
 *
 * Copyright (C) 2009, 2015, 2017 Kevin Benton - kbenton at bentonfam dot org
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

require_once 'Libs/autoload.php';

// Be sure to specify these in apply order. The reset script will automatically
// reverse the order for safe removal.
$controllerNames = [
    '\\com\\kbcmdba\\pjs2\\VersionController',
    '\\com\\kbcmdba\\pjs2\\AuthTicketController',
    '\\com\\kbcmdba\\pjs2\\ApplicationStatusController',
    '\\com\\kbcmdba\\pjs2\\ApplicationStatusSummaryController',
    '\\com\\kbcmdba\\pjs2\\CompanyController',
    '\\com\\kbcmdba\\pjs2\\ContactController',
    '\\com\\kbcmdba\\pjs2\\JobController',
    '\\com\\kbcmdba\\pjs2\\KeywordController',
    '\\com\\kbcmdba\\pjs2\\NoteController',
    '\\com\\kbcmdba\\pjs2\\SearchController',
    '\\com\\kbcmdba\\pjs2\\JobKeywordMapController'
];
$controllers = [];

$config = new Config();
$page = new PJSWebPage($config->getTitle() . " - Reset DB", true);
$body = "<ul>\n";
try {
    $dbc = new DBConnection("admin", null, null, null, null, null, 'mysqli', true);
    if (! $dbc->getCreatedDb()) {
        // Database exists. Don't allow reset if the user is not logged in.
        $auth = new Auth();
        if (! $auth->isAuthorized()) {
            throw new \Exception("User must be logged in to reset the database!");
        }
        if ("1" !== $config->getResetOk()) {
            throw new \Exception("Reset capability is turned off! See config.xml");
        }
    }
    $dbh = $dbc->getConnection();
    foreach (array_reverse($controllerNames) as $controllerName) {
        $controller = new $controllerName('write');
        $controllers[$controllerName] = $controller;
        if (method_exists($controller, 'dropTriggers')) {
            $body .= "<li>Dropping Triggers: $controllerName</li>\n";
            $controller->dropTriggers();
        }
    }
    
    foreach ($controllers as $controllerName => $controller) {
        $body .= "<li>Dropping Tables: $controllerName</li>\n";
        $controller->dropTable();
    }
    
    foreach (array_reverse($controllers) as $controllerName => $controller) {
        $body .= "<li>Creating Tables: $controllerName</li>\n";
        $controller->createTable();
    }
    
    foreach (array_reverse($controllers) as $controllerName => $controller) {
        if (method_exists($controller, 'createTriggers')) {
            $body .= "<li>Creating Triggers: $controllerName</li>\n";
            $controller->createTriggers();
        }
    }
    
    foreach (array_reverse($controllers) as $controllerName => $controller) {
        if (method_exists($controller, 'preLoadData')) {
            $body .= "<li>Pre-populating tables: $controllerName</li>\n";
            $controller->preLoadData();
        }
    }
    $body .= "</ul>\n<p>Done.</p>";
} catch (\Exception $e) {
    $body .= "</ul>\n<p />Uncaught exception: " . $e->getMessage() . "\n";
}
$page->setBody($body);
$page->displayPage();
