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
    $jobModel = new JobModel() ;
    $jobModel->setId($id) ;
    $jobController = new JobController() ;
    $jobController->delete($jobModel) ;
} catch (ControllerException $e) {
    $result = "Delete failed. " . $e->getMessage() ;
    $jobController = new JobController() ;
    $jobModel = $jobController->get($id) ;
    $jobListView = new JobListView() ;
    $row = $jobListView->displayJobRow($jobModel, 'list', 'add', $result) ;
}

echo json_encode([ 'result' => $result, 'row' => $row ]) . PHP_EOL ;
