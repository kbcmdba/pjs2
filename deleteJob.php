<?php

/**
 * phpjobseeker
 *
 * Copyright (C) 2009, 2015 Kevin Benton - kbenton at bentonfam dot org
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

require_once "Libs/autoload.php" ;

$config = new Config() ;
$webPage = new PJSWebPage( $config->getTitle() . ' - Delete Job') ;
$act = Tools::Param( 'act' ) ;
if ( "Delete Job" === $act ) {
    $jobModel = new JobModel() ;
    $jobModel->populateFromForm() ;
    if ( ! $jobModel->validateForDelete() ) {
        $jobView = new JobFormView( 'Delete Job', $jobModel ) ;
        $body = "<h2>Invalid data</h2>\n" . $jobView->getForm() ;
    }
    else {
        $jobController = new JobController() ;
        $jobController->delete( $jobModel ) ;
        $body = "Deleted job # " . $jobModel->getId() . "<br />\n";
    }
}
else {
    $jobController = new JobController() ;
    $jobModel = $jobController->get( Tools::param( 'id' ) ) ;
    $jobView = new JobFormView( 'Delete Job', $jobModel ) ;
    $body = $jobView->getForm() ;
}
$webPage->setBody( $body ) ;
$webPage->displayPage() ;
