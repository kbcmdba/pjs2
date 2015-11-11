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
 * Contact Form View
 */
class ContactFormView extends FormViewBase {
    /** @var ContactModel */
    private $_contactModel ;

    /**
     * Class constructor
     *
     * @param string Page title and action button
     * @param ContactModel The populated model or null
     */
    public function __construct( $title = "Add Contact", $contactModel = null ) {
        parent::__construct( $title ) ;
        if ( NULL !== $contactModel ) {
            $this->_contactModel = $contactModel ;
        }
        else {
            $this->_contactModel = new ContactModel ;
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
        $RO              = ( 'readonly' === $readOnly ) ? 'READONLY="READONLY" ' : '' ;
        $contactModel    = $this->_contactModel ;
        $title           = $this->getTitle() ;
        $companyId       = $contactModel->getContactCompanyId() ;
        $contactName     = $contactModel->getContactName() ;
        $contactEmail    = $contactModel->getContactEmail() ;
        $contactPhone    = $contactModel->getContactPhone() ;
        $contactAltPhone = $contactModel->getContactAlternatePhone() ;
        $created         = $contactModel->getCreated() ;
        $updated         = $contactModel->getUpdated() ;
        $companyFormView = new CompanyFormView() ;
        $companyList = $companyFormView->getCompanySelectList( $companyId, $RO ) ;
        $buttonLabel     = $this->getButtonLabel() ;
        $returnValue     = <<<HTML
    <h2>$title</h2>
    <form method="GET">
      <table border="1" cellspacing="1" cellpadding="2">
        <tr>
          <th>ID</th>
          <td><input type="text" name="id" value="$id" readonly="readonly" /></td>
        </tr>
        <tr>
          <th>Contact's Company</th>
          <td>$companyList</td>
        </tr>
        <tr>
          <th>Contact's Name</th>
          <td><input type="text" name="contactName" value="$contactName" $RO /></td>
        </tr>
        <tr>
          <th>Contact's Email</th>
          <td><input type="text" name="contactEmail" value="$contactEmail" $RO /></td>
        </tr>
        <tr>
          <th>Contact's Phone</th>
          <td><input type="text" name="contactPhone" value="$contactPhone" $RO /></td>
        </tr>
        <tr>
          <th>Contact's Alternate Phone</th>
          <td><input type="text" name="contactAlternatePhone" value="$contactAltPhone" $RO /></td>
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

    /**
     * 
     * @param unknown $selectedContactId
     * @param unknown $readOnly
     * @return string Selection list of contacts
     * @throws ControllerException
     */
    public function getContactSelectList( $selectedContactId, $readOnly ) {
        $contactController = new ContactController( 'read' ) ;
        $contactModels = $contactController->getAll() ;
        $str = "<select name=\"contactId\" $readOnly>\n" ;
        if ( ( ! isset( $selectedContactId ) ) || ( 0 === $selectedContactId ) ) {
            $str .= "  <option value=\"\" selected=\"selected\">None</option>\n" ;
        }
        else {
            $str .= "  <option value=\"\" >None</option>\n" ;
        }
        foreach ( $contactModels as $contactModel ) {
            $id = $contactModel->getId() ;
            $contactName  = $contactModel->getContactName() ;
            $contactEmail = $contactModel->getContactEmail() ;
            $selected = ( $selectedContactId === $id ) ? 'selected="selected"' : '' ;
            $str .= "  <option value=\"$id\" $selected>$contactName ($contactEmail)</option>\n" ;
        }
        $str .= "</select>\n" ;
        return $str ;
    }

}
