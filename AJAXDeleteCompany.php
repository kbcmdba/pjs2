<?php

require_once "Libs/autoload.php" ;

$auth = new Auth() ;
if ( ! $auth->isAuthorized() ) {
    $auth->forbidden() ;
    exit( 0 ) ; // Should never get here but just in case...
}
$id     = Tools::post( 'id' ) ;
$result = 'OK' ;
$rows = array( "", "" ) ;
try {
    $companyModel = new CompanyModel() ;
    $companyModel->setId( $id ) ;
    $companyController = new CompanyController() ;
    $companyController->delete( $companyModel ) ;
}
catch ( ControllerException $e ) {
    $result = "Delete failed. " . $e->getMessage() ;
}

echo json_encode( array( 'result' => $result, 'rows' => $rows ) ) . PHP_EOL ;
