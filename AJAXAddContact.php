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

require_once "Libs/autoload.php" ;

$auth = new Auth() ;
if ( ! $auth->isAuthorized() ) {
    $auth->forbidden() ;
    exit( 0 ) ; // Should never get here but just in case...
}
$companyId       = Tools::param( 'contactCompanyId' ) ;
$name            = Tools::param( 'contactName' ) ;
$email           = Tools::param( 'contactEmail' ) ;
$phone           = Tools::param( 'contactPhone' ) ;
$alternatePhone  = Tools::param( 'contactAlternatePhone' ) ;
$rowId           = Tools::param( 'rowId' ) ;

$result          = 'OK' ;
$contactId       = '' ;
$newContactModel = null ;
try {
    $contactModel = new ContactModel() ;
    $contactModel->setContactCompanyId( $companyId ) ;
    $contactModel->setContactName( $name ) ;
    $contactModel->setContactEmail( $email ) ;
    $contactModel->setContactPhone( $phone ) ;
    $contactModel->setContactAlternatePhone( $alternatePhone ) ;

    $contactController = new ContactController() ;
    $contactId = $contactController->add( $contactModel ) ;

    if ( ! ( $contactId > 0 ) ) {
        throw new ControllerException( "Add failed." ) ;
    }
    $newContactModel = $contactController->get( $contactId ) ;
    $contactRowView = new ContactListView() ;
    $row = $contactRowView->displayContactRow( $newContactModel, 'list' ) ;
}
catch ( ControllerException $e ) {
    $contactRowView = new ContactListView( 'html', null ) ;
    $contactModel->setId( $rowId ) ;
    $row = $contactRowView->displayContactRow( $contactModel
                                             , 'add'
                                             , 'Add Contact record failed. '
                                             . $e->getMessage()
                                             ) ;
}

$result = array( 'result' => $result, 'row' => $row, 'newId' => $contactId ) ;
echo json_encode( $result ) . PHP_EOL ;
