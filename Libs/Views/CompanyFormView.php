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
 * Company Form View
 *
 * Change company to your model's name
 * Change Company to your model's Name
 * Edit Company to your class description (used for a button)
 */
class CompanyFormView extends FormViewBase {
    /** @var CompanyModel */
    private $_companyModel ;

    /**
     * Class constructor
     *
     * @param string Page title and action button
     * @param CompanyModel The populated model or null
     */
    public function __construct( $title = "Add Company", $companyModel = null ) {
        parent::__construct( $title ) ;
        if ( NULL !== $companyModel ) {
            $this->_companyModel = $companyModel ;
        }
        else {
            $this->_companyModel = new CompanyModel ;
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
        $RO                 = ( 'readonly' === $readOnly ) ? 'READONLY="READONLY" ' : '' ;
        $companyModel       = $this->_companyModel ;
        $title              = $this->getTitle() ;
        $id                 = $companyModel->getId() ;
        $isAnAgency         = $companyModel->getIsAnAgency() ;
        $isAnAgencyCheckbox = $isAnAgency ? 'checked="checked"' : '' ;
        $agencyCompanyId    = $companyModel->getAgencyCompanyId() ;
        $companyName        = $companyModel->getCompanyName() ;
        $companyAddress1    = $companyModel->getCompanyAddress1() ;
        $companyAddress2    = $companyModel->getCompanyAddress2() ;
        $companyCity        = $companyModel->getCompanyCity() ;
        $companyState       = $companyModel->getCompanyState() ;
        $companyZip         = $companyModel->getCompanyZip() ;
        $companyPhone       = $companyModel->getCompanyPhone() ;
        $companyUrl         = $companyModel->getCompanyUrl() ;
        $created            = $companyModel->getCreated() ;
        $updated            = $companyModel->getUpdated() ;
        $buttonLabel        = $this->getButtonLabel() ;
        $agencySelectList   = $this->getAgencySelectList( $id, $agencyCompanyId, $RO ) ;
        if ( Tools::isNumeric( $id ) ) {
            $noteController   = new NoteController( 'read' ) ;
            $noteModels       = $noteController->getSome( "appliesToTable = 'company' and appliesToId = $id" ) ;
            $noteListView     = new NoteListView( 'html', $noteModels ) ;
            $noteListView->setNoteModels( $noteModels ) ;
            $noteListView->setAppliesToTable( 'company' ) ;
            $noteListView->setAppliesToId( $id ) ;
            $noteListViewText = $noteListView->getView() ;
        }
        else {
            $noteListViewText = '' ;
        }
        $returnValue     = <<<HTML
    <h2>$title</h2>
    <form method="GET">
      <table border="1" cellspacing="1" cellpadding="2">
        <tr>
          <th>ID</th>
          <td><input type="text" name="id" value="$id" readonly="readonly" /></td>
        </tr>
        <tr>
          <th>Is An Agency</th>
          <td><input type="checkbox" name="isAnAgency" value="1" $isAnAgencyCheckbox $RO /></td>
        </tr>
        <tr>
          <th>Agency Of</th>
          <td>$agencySelectList
        </tr>
        <tr>
          <th>Company Name</th>
          <td><input type="text" name="companyName" value="$companyName" $RO /></td>
        </tr>
        <tr>
          <th>Company Address</th>
          <td><input type="text" name="companyAddress1" value="$companyAddress1" $RO /></td>
        </tr>
        <tr>
          <th>Company Address (Line 2)</th>
          <td><input type="text" name="companyAddress2" value="$companyAddress2" $RO /></td>
        </tr>
        <tr>
          <th>Company City</th>
          <td><input type="text" name="companyCity" value="$companyCity" $RO /></td>
        </tr>
        <tr>
          <th>Company State</th>
          <td><input type="text" name="companyState" value="$companyState" $RO /></td>
        </tr>
        <tr>
          <th>Company Zip Code</th>
          <td><input type="text" name="companyZip" value="$companyZip" $RO /></td>
        </tr>
        <tr>
          <th>Company Phone Number</th>
          <td><input type="text" name="companyPhone" value="$companyPhone" $RO /></td>
        </tr>
        <tr>
          <th>Company URL (Main)</th>
          <td><input type="text" name="companyUrl" value="$companyUrl" $RO /></td>
        </tr>
        <tr>
          <th>Created</th>
          <td><input type="text" name="created" value="$created" disabled="disabled" /></td>
        </tr>
        <tr>
          <th>Updated</th>
          <td><input type="text" name="updated" value="$updated" disabled="disabled" /></td>
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
    $noteListViewText
HTML;
        return $returnValue ;
    }

    /**
     * Return a selection list of agency names
     *
     * @param integer $myId
     * @param integer $agencyId
     * @param string  $readOnly
     */
    public function getAgencySelectList( $myId, $agencyId, $readOnly ) {
        $companyController = new CompanyController( 'read' ) ;
        $companyModels = $companyController->getAll() ;
        $str = "<select name=\"agencyCompanyId\" $readOnly>\n" ;
        if ( ( ! isset( $agencyId ) ) || 0 === $agencyId ) {
            $str .= "  <option value=\"\" selected=\"selected\">None</option>\n" ;
        }
        else {
            $str .= "  <option value=\"\" >None</option>\n" ;
        }
        foreach ( $companyModels as $companyModel ) {
            $id = $companyModel->getId() ;
            if ( $myId === $id ) {
                continue ;
            }
            $companyName = $companyModel->getCompanyName() ;
            $companyCity = $companyModel->getCompanyCity() ;
            $companyState = $companyModel->getCompanyState() ;
            $selected = ( $agencyId === $id ) ? 'selected="selected"' : '' ;
            $str .= "  <option value=\"$id\" $selected>$companyName ($companyCity, $companyState)</option>\n" ;
        }
        $str .= "</select>\n" ;
        return $str ;
    }

    /**
     * Return a selection list of agency names
     *
     * @param integer $selectedCompanyId
     * @param string  $readOnly
     * @throws ControllerException
     */
    public function getCompanySelectList( $selectedCompanyId, $readOnly ) {
        $companyController = new CompanyController( 'read' ) ;
        $companyModels = $companyController->getAll() ;
        $str = "<select name=\"companyId\" $readOnly>\n" ;
        if ( ( ! isset( $selectedCompanyId ) ) || ( 0 === $selectedCompanyId ) ) {
            $str .= "  <option value=\"\" selected=\"selected\">None</option>\n" ;
        }
        else {
            $str .= "  <option value=\"\" >None</option>\n" ;
        }
        foreach ( $companyModels as $companyModel ) {
            $id = $companyModel->getId() ;
            $companyName = $companyModel->getCompanyName() ;
            $companyCity = $companyModel->getCompanyCity() ;
            $companyState = $companyModel->getCompanyState() ;
            $selected = ( $selectedCompanyId === $id ) ? 'selected="selected"' : '' ;
            $str .= "  <option value=\"$id\" $selected>$companyName ($companyCity, $companyState)</option>\n" ;
        }
        $str .= "</select>\n" ;
        return $str ;
    }

}
