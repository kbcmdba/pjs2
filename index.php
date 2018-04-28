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

namespace com\kbcmdba\pjs2 ;

require_once 'Libs/autoload.php' ;

$config = new Config() ;
try {
    $dbc = new DBConnection() ;
} catch (DaoException $ex) {
    echo "Database connection failed. Has this system been set up yet? Have you used resetDb.php?\n" ;
    exit(255) ;
}

$config = new Config() ;
$page = new PJSWebPage($config->getTitle()) ;
$applicationStatusController = new ApplicationStatusController('read') ;
$applicationStatusList = $applicationStatusController->getAll() ;
$appStatusBody = new ApplicationStatusSummaryView('html', $applicationStatusList) ;
$asmList = $applicationStatusController->getAll() ;
$asv = new ApplicationStatusSummaryView('html', $asmList) ;
$body .= $asv->getView() ;

$jobController = new JobController('read') ;
$now      = $jobController->now() ;
$today    = $jobController->today() ;
$tomorrow = $jobController->tomorrow() ;
$active   = "isActiveSummary = true" ;

$jobsOverdue   =  $jobController->countSome("$active AND nextActionDue < '$now'") ;
$jobsDueToday  = $jobController->countSome("$active AND nextActionDue BETWEEN '$today' AND '$tomorrow'") ;
$jobsDue7Days  = $jobController->countSome("$active AND nextActionDue BETWEEN '$today' AND '$nextWeek'") ;
$highUrgency   = $jobController->countSome("$active AND urgency = 'high'") ;
$mediumUrgency = $jobController->countSome("$active AND urgency = 'medium'") ;
$lowUrgency    = $jobController->countSome("$active AND urgency = 'low'") ;

$searchController = new SearchController('read') ;
$searches      = $searchController->countSome() ;

$body .= <<<HTML

<div id="overdue">Active Overdue Jobs: <span id="overdue_count">$jobsOverdue</span></div>
<div id="today">Active Jobs Due Today: <span id="today_count">$jobsDueToday</span></div>
<div id="7days">Active Jobs Due In 7 Days: <span id="7day_count">$jobsDue7Days</span></div>
<div id="high">Active High Urgency Jobs: <span id="high_count">$highUrgency</span></div>
<div id="med">Active Medium Urgency Jobs: <span id="med_count">$mediumUrgency</span></div>
<div id="low">Active Low Urgency Jobs: <span id="low_count">$lowUrgency</span></div>

<div id="searches">Active Job Search URLs: <span id="search_count">$searches</span></div>

HTML;

$page->setBody($body) ;
$page->displayPage() ;
