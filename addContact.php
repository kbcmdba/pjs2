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
$webPage = new PJSWebPage( $config->getTitle() . "Contacts - Add Contact" ) ;
$body = '' ;
$act = Tools::Param( 'act' ) ;
if ( "Add Contact" === $act ) {
    $model = new ContactModel() ;
    $model->populateFromForm() ;
    if ( ! $model->validateForAdd() ) {
        $view = new ContactFormView( 'Add Contact', $model ) ;
        $body = "<h2>Invalid data</h2>\n" . $view->getForm() ;
    }
    else {
        $contactController = new ContactController() ;
        $newId = $contactController->add( $model ) ;
        if ( $newId > 0 ) {
            $body = "Added contact # " . $newId . "<br />\n";
        }
    }
}
else {
    $body = "" ;
    $contactModel = new ContactModel() ;
    $companyId = ( '' === Tools::param( 'contactCompanyId' ) ) ? 0 : Tools::param( 'contactCompanyId' ) ;
    $contactModel->setContactCompanyId( $companyId ) ;
    $contactModel->setContactName( Tools::param( 'contactName' ) ) ;
    $contactModel->setContactEmail( Tools::param( 'contactEmail' ) ) ;
    $contactModel->setContactPhone( Tools::param( 'contactPhone' ) ) ;
    $contactModel->setContactAlternatePhone( Tools::param( 'contactAlternatePhone' ) ) ;
    $view = new ContactFormView( "Add Contact", $contactModel ) ;
    $body = $view->getForm() ;
}
$webPage->setBody( $body ) ;
$webPage->displayPage() ;
