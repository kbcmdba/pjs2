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
<a href="addApplicationStatus.php">Add new application status</a><br />
<table border="1" cellspacing="0" cellpadding="2">
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
            $id              = $applicationStatus->getId() ;
            $value           = htmlspecialchars( $applicationStatus->getStatusValue() ) ;
            $isActive        = $applicationStatus->getIsActive() ? "Yes" : "No" ;
            $isActiveChecked = $applicationStatus->getIsActive() ? 'checked="checked"' : '' ;
            $sortKey         = $applicationStatus->getSortKey() ;
            $style           = htmlspecialchars( $applicationStatus->getStyle() ) ;
            $created         = $applicationStatus->getCreated() ;
            $updated         = $applicationStatus->getUpdated() ;
            $body .= <<<HTML
      <form name="appstat_$id">
        <tr id="disp_$id" style="display: table-row;">
        <td>
            <input type="button" onclick="return doEditApplicationStatus( $id );" name="act" value="Edit" >
            <input type="button" onclick="return doDeleteApplicationStatus( $id );" name="act" value="Delete" >
        </td>
        <td style="$style">$value</td>
        <td>$style</td>
        <td><input type="checkbox" name="isActivex" value="1" $isActiveChecked onclick="return false" onkeydown="return false" /></td>
        <td>$sortKey</td>
        <td>$created</td>
        <td>$updated</td>
      </tr>
      <tr id="edit_$id" style="display: none;">
        <td>
          <input type="button" onclick="return doSaveApplicationStatus( $id );" name="act" value="Save" >
          <input type="button" onclick="return doCancelApplicationStatusChange( $id );" name="act" value="Cancel" >
        </td>
        <td style="$style"><input type="text" name="statusValue" value="$value" size=15 /></td>
        <td><input type="text" name="style" value="$style" size=40 /></td>
        <td><input type="checkbox" name="isActive" value="1" $isActiveChecked /></td>
        <td><input type="text" name="sortKey" value="$sortKey" size="5" /></td>
        <td>$created</td>
        <td>$updated</td>
      </tr>
      <tr id="status_$id" style="display: none;">
        <td><input type="button" onclick="return doHideResult( $id );" name="act" value="Hide" ></td>
        <td colspan="6" id="result_$id"></td>
      </tr>
    </form>
    
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