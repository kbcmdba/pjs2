<?php

require_once "Libs/autoload.php" ;

$auth = new Auth() ;
if (! $auth->isAuthorized()) {
    $auth->forbidden() ;
    exit(0) ; // Should never get here but just in case...
}
$id     = Tools::param('id') ;
$result = 'OK' ;
$row    = "" ;
try {
    $contactModel = new ContactModel() ;
    $contactModel->setId($id) ;
    $contactController = new ContactController() ;
    $contactController->delete($contactModel) ;
} catch (ControllerException $e) {
    $result = "Delete failed. " . $e->getMessage() ;
    $contactController = new ContactController() ;
    $contactModel = $contactController->get($id) ;
    $contactListView = new ContactListView() ;
    $row = $contactListView->displayContactRow($contactModel, 'list', 'add', $result) ;
}

echo json_encode([ 'result' => $result, 'row' => $row ]) . PHP_EOL ;
