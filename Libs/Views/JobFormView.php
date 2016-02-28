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
 * Job Form View
 */
class JobFormView extends FormViewBase {
    /** @var JobModel */
    private $_jobModel ;

    /**
     * Class constructor
     *
     * @param string Page title and action button
     * @param JobModel The populated model or null
     */
    public function __construct( $title = "Add Job", $jobModel = null ) {
        parent::__construct( $title ) ;
        if ( NULL !== $jobModel ) {
            $this->_jobModel = $jobModel ;
        }
        else {
            $this->_jobModel = new JobModel ;
        }
    }

    /**
     * magic method
     * @return string
     */
    public function __toString() {
        return $this->getForm() ;
    }

    /**
     *
     * @return string
     */
    public function getForm( $readOnly = 'readwrite' ) {
        $RO                    = ( 'readonly' === $readOnly ) ? 'READONLY="READONLY" ' : '' ;
        $jobModel              = $this->_jobModel ;
        $title                 = $this->getTitle() ;
        $id                    = $jobModel->getId() ;
        $primaryContactId      = $jobModel->getPrimaryContactId() ;
        $companyId             = $jobModel->getCompanyId() ;
        $applicationStatusId   = $jobModel->getApplicationStatusId() ;
        $lastStatusChange      = $jobModel->getLastStatusChange() ;
        $urgency               = $jobModel->getUrgency() ;
        $created               = $jobModel->getCreated() ;
        $updated               = $jobModel->getUpdated() ;
        $nextActionDue         = $jobModel->getNextActionDue() ;
        $nextAction            = $jobModel->getNextAction() ;
        $positionTitle         = $jobModel->getPositionTitle() ;
        $location              = $jobModel->getLocation() ;
        $url                   = $jobModel->getUrl() ;
        $buttonLabel           = $this->getButtonLabel() ;
        $contactFormView       = new ContactFormView() ;
        $contactList           = $contactFormView->getContactSelectList( $primaryContactId, $RO ) ;
        $companyFormView       = new CompanyFormView() ;
        $companyList           = $companyFormView->getCompanySelectList( $companyId, $RO ) ;
        $applicationStatusView = new ApplicationStatusFormView( ) ;
        $applicationStatusList = $applicationStatusView->getApplicationStatusSelectList( $applicationStatusId, $RO ) ;
        $returnValue           = <<<HTML
    <h2>$title</h2>
    <form name="job" onsubmit="return validateJob()" method="GET">
      <table border="1" cellspacing="1" cellpadding="2">
        <tr>
          <th>ID</th>
          <td><input type="text" name="id" value="$id" readonly="readonly" /></td>
        </tr>
        <tr>
          <th>Primary Contact *</th>
          <td>$contactList</td>
        </tr>
        <tr>
          <th>Company *</th>
          <td>$companyList</th>
        </tr>
        <tr>
          <th>Application Status *</th>
          <td>$applicationStatusList</td>
        </tr>
        <tr>
          <th>Last Status Change</th>
          <td><input type="text" name="lastStatusChange" value="$lastStatusChange" readonly="readonly" /></td>
        </tr>
        <tr>
          <th>Urgency *</th>
          <td><input type="text" name="urgency" value="$urgency" $RO /></td>
        </tr>
        <tr>
          <th>Created</th>
          <td><input type="text" name="created" value="$created" readonly="readonly" /></td>
        </tr>
        <tr>
          <th>Updated</th>
          <td><input type="text" name="updated" value="$updated" readonly="readonly" /></td>
        </tr>
        <tr>
          <th>Next Action Due *</th>
          <td><input type="text" name="nextActionDue" value="$nextActionDue" class="datepicker" $RO/></td>
        </tr>
        <tr>
          <th>Next Action *</th>
          <td><input type="text" name="nextAction" value="$nextAction" $RO /></td>
        </tr>
        <tr>
          <th>Position Title *</th>
          <td><input type="text" name="positionTitle" value="$positionTitle" $RO /></td>
        </tr>
        <tr>
          <th>Location *</th>
          <td><input type="text" name="location" value="$location" $RO /></td>
        </tr>
        <tr>
          <th>URL</th>
          <td><input type="text" name="url" value="$url" $RO /></td>
        </tr>
        <tr>
          <td colspan="2">
            <center>
              <input type="reset" /> &nbsp; &nbsp; <input type="submit" name="act" value="$buttonLabel" />
            </center>
          </td>
        </tr>
      </table>
    </form>
HTML;
        return $returnValue ;
    }

}
