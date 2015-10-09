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

/**
 * Job List View
 */

class JobListView extends ListViewBase {

    /** @var string */
    private $_viewType ;
    /** @var mixed */
    private $_supportedViewTypes = array( 'html' => 1 ) ;
    /** @var JobModel[] */
    private $_jobModels ;

    /**
     * Class constructor
     *
     * @param string View Type
     * @param JobModel[] $jobModels
     * @throws ViewException
     */
    public function __construct( $viewType = 'html', $jobModels ) {
        parent::__construct() ;
        if ( ! isset( $this->_supportedViewTypes[ $viewType ] ) ) {
            throw new ViewException( "Unsupported view type\n" ) ;
        }
        $this->_viewType = $viewType ;
        $this->setJobModels( $jobModels ) ;
    }

    /**
     * Return the HTML view
     *
     * @return string
     */
    private function _getHtmlView() {
        $body = <<<'HTML'
<a href="addJobs.php">Add a new job</a><br />
<table border="1" cellspacing="0" cellpadding="2">
  <caption>Current Jobs</caption>
  <tr>
    <th>Actions</th>
    <th>Urgency</th>
    <th>Title</th>
    <th>Location</th>
    <th>Company</th>
    <th>Contact</th>
    <th>Status</th>
    <th>Next Action</th>
    <th>Next Action Due</th>
    <th>Link</th>
  </tr>
HTML;
        foreach ( $this->getJobModels() as $jobModel ) {
            $id = $jobModel->getId() ;
            $primaryContactId    = $jobModel->getPrimaryContactId() ;
            $companyId           = $jobModel->getCompanyId() ;
            $applicationStatusId = $jobModel->getApplicationStatusId() ;
            $lastStatusChange    = $jobModel->getLastStatusChange() ;
            $urgency             = $jobModel->getUrgency() ;
            $created             = $jobModel->getCreated() ;
            $updated             = $jobModel->getUpdated() ;
            $nextActionDue       = $jobModel->getNexActionDue() ;
            $nextAction          = $jobModel->getNextAction() ;
            $positionTitle       = $jobModel->getPositionTitle() ;
            $location            = $jobModel->getLocation() ;
            $url                 = $jobModel->getUrl() ;
            // @FIXME left off here.
            $body .= <<<HTML
  <tr>
    <td>
        <a href="editJobs.php?id=$id">Edit</a>
      | <a href="deleteJobs.php?id=$id">Delete</a>
    </td>
    <td>$blah</td>
    <td>$blah</td>
    <td>$blah</td>
  </tr>
HTML;
        }

        $body .= '</table>' ;

        return $body ;
    }

    /**
     *
     * @return string
     * @throws ViewException
     */
    public function getView() {
        switch ( $this->_viewType ) {
            case 'html' :
                return $this->_getHtmlView() ;
            default :
                throw new ViewException( "Unsupported view type." ) ;
        }
    }

    /**
     * @return JobModel[]
     */
    public function getJobModels() {
        return $this->_jobModels ;
    }

    /**
     * @param JobModel[] $jobModels
     */
    public function setJobModels( $jobModels ) {
        $this->_jobModels = $jobModels ;
    }

}