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
 * ApplicationStatus Form View
 *
 * Edit Template to your class description (used for a button)
 */
class ApplicationStatusFormView extends FormViewBase {
    /** @var ApplicationStatusModel */
    private $_model ;

    /**
     * Class constructor
     *
     * @param string Page title and action button
     * @param ApplicationStatusModel The populated model or null
     */
    public function __construct( $title = "Add Template", $model = null ) {
        parent::__construct( $title ) ;
        if ( NULL !== $model ) {
            $this->_model = $model ;
        }
        else {
            $this->_model = new ApplicationStatusModel ;
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
        $RO                = ( 'readonly' === $readOnly ) ? 'READONLY="READONLY" ' : '' ;
        $model             = $this->_model ;
        $title             = $this->getTitle() ;
        $applicationStatus = $this->_model ;
        $id                = $applicationStatus->getId() ;
        $value             = htmlspecialchars( $applicationStatus->getStatusValue() ) ;
        $isActiveChecked   = $applicationStatus->getIsActive() ? "checked=\"checked\"" : "" ;
        $sortKey           = $applicationStatus->getSortKey() ;
        $style             = htmlspecialchars( $applicationStatus->getStyle() ) ;
        $buttonLabel       = $this->getButtonLabel() ;
        $returnValue       = <<<HTML
    <h2>$title</h2>
    <form name="applicationStatus" onsubmit="return validateApplicationStatus()" method="GET">
      <table border="1" cellspacing="1" cellpadding="2">
        <tr>
          <th>ID</th>
          <td><input type="text" name="id" value="$id" readonly="readonly" /></td>
        </tr>
        <tr>
          <th>Status Value *</th>
          <td><input type="text" name="statusValue" value="$value" $RO /></td>
        </tr>
        <tr>
          <th>Is Active</th>
          <td><input type="checkbox" name="isActive" value="1" $isActiveChecked $RO /></td>
        </tr>
        <tr>
          <th>Sort Key *</th>
          <td><input type="text" name="sortKey" value="$sortKey" $RO /></td>
        </tr>
        <tr>
          <th>Style</th>
          <td><input type="text" name="style" value="$style" $RO /></td>
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

    // @todo Write ApplicationStatusFormView::getApplicationStatusSelectList( $selectedApplicationStatusId, $readOnly )
    public function getApplicationStatusSelectList( $selectedApplicationStatusId, $readOnly ) {
        $applicationStatusController = new ApplicationStatusController( 'read' ) ;
        $applicationStatusModels = $applicationStatusController->getSome( 'isActive = 1' ) ;
        $str = "<select name=\"applicationStatusId\" $readOnly>\n" ;
        if ( ( ! isset( $selectedApplicationStatusId ) ) || ( 0 === $selectedApplicationStatusId ) ) {
            $str .= "  <option value=\"\" selected=\"selected\">None</option>\n" ;
        }
        else {
            $str .= "  <option value=\"\" >None</option>\n" ;
        }
        foreach ( $applicationStatusModels as $applicationStatusModel ) {
            $id = $applicationStatusModel->getId() ;
            $statusValue = $applicationStatusModel->getStatusValue() ;
            $style       = $applicationStatusModel->getStyle() ;
            $selected = ( $selectedApplicationStatusId === $id ) ? 'selected="selected"' : '' ;
            $str .= "  <option value=\"$id\" $selected style=\"$style\">$statusValue</option>\n" ;
        }
        $str .= "</select>\n" ;
        return $str ;
    }

}
