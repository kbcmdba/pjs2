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

require_once 'Libs/autoload.php' ;

$config = new Config() ;
try {
    $dbc = new DBConnection() ;
}
catch ( DaoException $ex ) {
    echo "Database connection failed. Has this system been set up yet? Have you used resetDb.php?\n" ;
    exit( 255 ) ;
}

$config = new Config() ;
$page = new PJSWebPage( $config->getTitle() ) ;
$applicationStatusController = new ApplicationStatusController( 'read' ) ;
$applicationStatusList = $applicationStatusController->getAll() ;
$appStatusBody = new ApplicationStatusSummaryView( 'html', $applicationStatusList ) ;
$asmList = $applicationStatusController->getAll() ;
$asv = new ApplicationStatusSummaryView( 'html', $asmList ) ;
$body .= $asv->getView() ;

$jobController = new JobController( 'read' ) ;
$now      = $jobController->now() ;
$today    = $jobController->today() ;
$tomorrow = $jobController->tomorrow() ;
$active   = "isActiveSummary = true" ;

$jobsOverdue   =  $jobController->countSome( "$active AND nextActionDue < '$now'" ) ;
$jobsDueToday  = $jobController->countSome( "$active AND nextActionDue BETWEEN '$today' AND '$tomorrow'" ) ;
$jobsDue7Days  = $jobController->countSome( "$active AND nextActionDue BETWEEN '$today' AND '$nextWeek'" ) ;
// @todo # of jobs by urgency
$highUrgency   = $jobController->countSome( "$active AND urgency = 'high'" ) ;
$mediumUrgency = $jobController->countSome( "$active AND urgency = 'medium'" ) ;
$lowUrgency    = $jobController->countSome( "$active AND urgency = 'low'" ) ;

$searchController = new SearchController( 'read' ) ;
$searches      = $searchController->countSome() ;

$body         .= "<div id=\"overdue\">Active Overdue Jobs: $jobsOverdue</div>\n" ;
$body         .= "<div id=\"today\">Active Jobs Due Today: $jobsDueToday</div>\n" ;
$body         .= "<div id=\"7days\">Active Jobs Due In 7 Days: $jobsDue7Days</div>\n" ;
$body         .= "<div id=\"high\">Active High Urgency Jobs: $highUrgency</div>\n" ;
$body         .= "<div id=\"medium\">Active Medium Urgency Jobs: $mediumUrgency</div>\n" ;
$body         .= "<div id=\"low\">Active Low Urgency Jobs: $lowUrgency</div>\n" ;

$body         .= "<div id=\"searches\">Active Job Search URLs: $searches</div>\n" ;
// @todo Show Jobs

// @todo Show Searches

$page->setBody( $body ) ;
$page->displayPage() ;
