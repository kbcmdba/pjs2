<?php

namespace com\kbcmdba\pjs2 ;

require_once "Libs/autoload.php" ;

$auth = new Auth() ;
if (! $auth->isAuthorized()) {
    $auth->forbidden() ;
    exit(0) ; // Should never get here but just in case...
}
$id     = Tools::param('id') ;
$result = 'OK' ;
$rows = [ "", "" ] ;
try {
    $companyModel = new CompanyModel() ;
    $companyModel->setId($id) ;
    $companyController = new CompanyController() ;
    $companyController->delete($companyModel) ;
} catch (ControllerException $e) {
    $result = "Delete failed. " . $e->getMessage() ;
    $companyController = new CompanyController() ;
    $companyModel = $companyController->get($id) ;
    $companyListView = new CompanyListView() ;
    $rows = $companyListView->displayCompanyRow($companyModel, 'list', 'add', $result) ;
}

echo json_encode([ 'result' => $result, 'rows' => $rows ]) . PHP_EOL ;
