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
 * Application Status List View
 */
class ApplicationStatusListView extends ListViewBase {

    /** @var string */
    private $_viewType ;
    /** @var mixed */
    private $_supportedViewTypes = array( 'html' => 1 ) ;
    /** @var ApplicationStatusModel[] */
    private $_applicationStatusModels ;

    /**
     * Class constructor
     *
     * @param string View Type
     * @throws ViewException
     */
    public function __construct( $viewType = 'html', $applicationStatusModels ) {
        parent::__construct() ;
        if ( ! isset( $this->_supportedViewTypes[ $viewType ] ) ) {
            throw new ViewException( "Unsupported view type\n" ) ;
        }
        $this->_viewType = $viewType ;
        $this->_applicationStatusModels = $applicationStatusModels ;
    }

    /**
     * Return the HTML view
     *
     * @return string
     */
    private function _getHtmlView() {
        $body = <<<'HTML'
<button type="button" onclick="addApplicationStatus()" >Add Application Status</button>
<table border="1" cellspacing="0" cellpadding="2" id="applicationStatus" >
  <caption>Current Application Statuses</caption>
  <thead>
    <tr>
      <th>Actions</th>
      <th>Value</th>
      <th>Style</th>
      <th>Is Active</th>
      <th>Sort Key</th>
      <th>Created</th>
      <th>Updated</th>
    </tr>
  </thead>
  <tbody>

HTML;
        foreach ( $this->_applicationStatusModels as $applicationStatus ) {
            $id       = $applicationStatus->getId() ;
            $row      = $this->displayApplicationStatusRow( $applicationStatus, 'list' ) ;
            $body .= <<<HTML
    <tr id="ux$id">
      $row
    </tr>

HTML;
        }

        $body .= "  </tbody>\n</table>\n" ;

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
     * Return the display of an application status table row
     *
     * @param ApplicationStatusModel $applicationStatusModel
     * @param string $displayMode 'add', 'edit', 'delete', 'list'
     * @return string
     */
    public function displayApplicationStatusRow( $applicationStatusModel, $displayMode ) {
        $id              = $applicationStatusModel->getId() ;
        $statusValue     = $applicationStatusModel->getStatusValue() ;
        $style           = $applicationStatusModel->getStyle() ;
        $isActive        = $applicationStatusModel->getIsActive() ;
        $isActiveChecked = ( $isActive ) ? "checked=\"checked\"" : "" ;
        $isActiveDisplay = ( $isActive ) ? "Yes" : "No" ;
        $sortKey         = $applicationStatusModel->getSortKey() ;
        $created         = $applicationStatusModel->getCreated() ;
        $updated         = $applicationStatusModel->getUpdated() ;
        switch ( $displayMode ) {
            case 'add'    :
                return <<<RETVAL
      <td><button type="button" id="SaveButtonix$id" onclick="saveAddApplicationStatus( '$id' )">Save</button>
          <button type="button" id="CancelButtonix$id" onclick="deleteRow( 'ix$id' )">Cancel</button>
      </td>
      <td><input type="text" id="statusValueix$id" value="$statusValue" /></td>
      <td><input type="text" id="styleix$id" value="$style" /></td>
      <td><input type="checkbox" id="isActiveix$id" $isActiveChecked /></td>
      <td><input type="text" id="sortKeyix$id" value="$sortKey" /></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>

RETVAL;
            case 'update' :
                return <<<RETVAL
      <td><button type="button" id="UpdateButton$id" onclick="doUpdateApplicationStatus( '$id' )">Save</button>
          <button type="button" id="CancelButton$id" onclick="cancelUpdateApplicationStatusRow( '$id' )">Cancel</button>
      </td>
      <td><input type="text" id="statusValueix$id" value="$statusValue" /></td>
      <td><input type="text" id="styleix$id" value="$style" /></td>
      <td><input type="checkbox" id="isActiveix$id" $isActiveChecked /></td>
      <td><input type="text" id="sortKeyix$id" value="$sortKey" /></td>
      <td>$created</td>
      <td>$updated</td>

RETVAL;
            case 'delete' :
                return <<<RETVAL
      <td><button type="button" id="DeleteButton$id" onclick="doDeleteApplicationStatus( '$id' )">Confirm Delete</button>
          <button type="button" id="CancelButton$id" onclick="cancelUpdateApplicationStatusRow( '$id' )">Cancel</button>
      </td>
      <td>$statusValue</td>
      <td>$style</td>
      <td>$isActiveDisplay</td>
      <td>$sortKey</td>
      <td>$created</td>
      <td>$updated</td>

RETVAL;
            case 'list'   :
                return <<<RETVAL
      <td><button type="button" id="UpdateButton$id" onclick="updateApplicationStatus( '$id' )">Edit</button>
          <button type="button" id="DeleteButton$id" onclick="deleteApplicationStatus( '$id' )">Delete</button>
      </td>
      <td style="$style">$statusValue</td>
      <td>$style</td>
      <td>$isActiveDisplay</td>
      <td>$sortKey</td>
      <td>$created</td>
      <td>$updated</td>

RETVAL;
        }
        return "" ;
    }


    /**
     * @return ApplicationStatusModel[]
     */
    public function getApplicationStatusModels() {
        return $this->_applicationStatusModels ;
    }

    /**
     * @param ApplicationStatusModel[] $applicationStatusModels
     */
    public function setApplicationStatusModels( $applicationStatusModels ) {
        $this->_applicationStatusModels = $applicationStatusModels ;
    }

}