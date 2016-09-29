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

$auth = new Auth() ;
if ( ! $auth->isAuthorized() ) {
    $auth->forbidden() ;
    exit( 0 ) ; // Should never get here but just in case...
}
$result          = 'OK' ;
$companyId       = '' ;
$isAnAgency      = Tools::param( 'isAnAgency' ) ;
$agencyCompanyId = Tools::param( 'agencyCompanyId' ) ;
$companyName     = Tools::param( 'companyName' ) ;
$companyAddress1 = Tools::param( 'companyAddress1' ) ;
$companyAddress2 = Tools::param( 'companyAddress2' ) ;
$companyCity     = Tools::param( 'companyCity' ) ;
$companyState    = Tools::param( 'companyState' ) ;
$companyZip      = Tools::param( 'companyZip' ) ;
$companyPhone    = Tools::param( 'companyPhone' ) ;
$companyUrl      = Tools::param( 'companyUrl' ) ;
$rowStyle        = Tools::param( 'rowStyle' ) ;
$newCompanyModel = null ;
try {
    $companyModel = new CompanyModel() ;
    $companyModel->setIsAnAgency( $isAnAgency ) ;
    $companyModel->setAgencyCompanyId( $agencyCompanyId ) ;
    $companyModel->setCompanyName( $companyName ) ;
    $companyModel->setCompanyAddress1( $companyAddress1 ) ;
    $companyModel->setCompanyAddress2( $companyAddress2 ) ;
    $companyModel->setCompanyCity( $companyCity ) ;
    $companyModel->setCompanyState( $companyState ) ;
    $companyModel->setCompanyZip( $companyZip ) ;
    $companyModel->setCompanyPhone( $companyPhone ) ;
    $companyModel->setCompanyUrl( $companyUrl ) ;

    $companyController = new CompanyController() ;
    $companyId         = $companyController->add( $companyModel ) ;

    if ( ! ( $companyId > 0 ) ) {
        throw new ControllerException( "Add failed." ) ;
    }
    $newCompanyModel = $companyController->get( $companyId ) ;
    $companyRowView = new CompanyListView( 'html', null ) ;
    $rows = $companyRowView->displayCompanyRow( $newCompanyModel, 'list', $rowStyle ) ;
}
catch ( ControllerException $e ) {
    $companyRowView = new CompanyListView( 'html', null ) ;
    $rows = $companyRowView->displayCompanyRow( $companyModel
                                              , 'add'
                                              , $rowStyle
                                              , 'Add Company record failed. '
                                              . $e->getMessage()
                                              ) ;
}

$result = array( 'result' => $result, 'rows' => $rows, 'newId' => $companyId ) ;
echo json_encode( $result ) . PHP_EOL ;
