<?php

require_once "Libs/autoload.php" ;

$auth = new Auth() ;
if ( ! $auth->isAuthorized() ) {
    $auth->forbidden() ;
    exit( 0 ) ; // Should never get here but just in case...
}
$id     = Tools::post( 'id' ) ;
$result = 'OK' ;
$row = "" ;
try {
    $applicationStatusModel = new ApplicationStatusModel() ;
    $applicationStatusModel->setId( $id ) ;
    $applicationStatusController = new ApplicationStatusController() ;
    $applicationStatusController->delete( $applicationStatusModel ) ;
}
catch ( ControllerException $e ) {
    $result = "Delete failed. " . $e->getMessage() ;
}

echo json_encode( array( 'result' => $result, 'row' => $row ) ) . PHP_EOL ;
