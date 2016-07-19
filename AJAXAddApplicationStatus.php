<?php

require_once "Libs/autoload.php" ;

$auth = new Auth() ;
if ( ! $auth->isAuthorized() ) {
    $auth->forbidden() ;
    exit( 0 ) ; // Should never get here but just in case...
}
$statusValue               = Tools::post( 'statusValue' ) ;
$style                     = Tools::post( 'style' ) ;
$isActive                  = Tools::post( 'isActive' ) ;
$sortKey                   = Tools::post( 'sortKey' ) ;
$result                    = 'OK' ;
$applicationStatusId       = '' ;
$newApplicationStatusModel = null ;
try {
    $applicationStatusModel = new ApplicationStatusModel() ;
    $applicationStatusModel->setStatusValue( $statusValue ) ;
    $applicationStatusModel->setStyle( $style ) ;
    $applicationStatusModel->setIsActive( $isActive ) ;
    $applicationStatusModel->setSortKey( $sortKey ) ;

    $applicationStatusController = new ApplicationStatusController() ;
    $applicationStatusId = $applicationStatusController->add( $applicationStatusModel ) ;

    if ( ! ( $applicationStatusId > 0 ) ) {
        throw new ControllerException( "Add failed." ) ;
    }
    $newApplicationStatusModel = $applicationStatusController->get( $applicationStatusId ) ;
    $applicationStatusRowView = new ApplicationStatusListView() ;
    $row = $applicationStatusRowView->displayApplicationStatusRow( $newApplicationStatusModel, 'list' ) ;
}
catch ( ControllerException $e ) {
    $applicationStatusRowView = new ApplicationStatusListView() ;
    $row = $applicationStatusRowView->displayApplicationStatusRow( $newApplicationStatusModel
                                           , 'list'
                                           , 'Add Application Status record failed. '
                                           . $e->getMessage()
                                           ) ;
}

$result = array( 'result' => $result, 'row' => $row, 'newId' => $applicationStatusId ) ;
echo json_encode( $result ) . PHP_EOL ;
