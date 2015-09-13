<?php

require_once "Libs/autoload.php" ;

$config = new Config() ;
$webPage = new PJSWebPage( $config->getTitle() . "Application Statuses - Add Application Status" ) ;
$body = '' ;
$act = Tools::Param( 'act' ) ;
if ( "Add Application Status" === $act ) {
    $model = new ApplicationStatusModel() ;
    $model->populateFromForm() ;
    if ( ! $model->validateForAdd() ) {
        $view = new ApplicationStatusFormView( 'Add Application Status', $model ) ;
        $body = "<h2>Invalid data</h2>\n" . $view->getForm() ;
    }
    else {
        $applicationStatusController = new ApplicationStatusController() ;
        $newId = $applicationStatusController->add( $model ) ;
        if ( $newId > 0 ) {
            $body = "Added application status # " . $newId . "<br />\n";
        }
    }
}
else {
    $body = "" ;
    $view = new ApplicationStatusFormView( "Add Application Status", null ) ;
    $body = $view->getForm() ;
}
$webPage->setBody( $body ) ;
$webPage->displayPage() ;
