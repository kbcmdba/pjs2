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
 * Contact List View
 *
 */

class ContactListView extends ListViewBase {

    /** @var string */
    private $_viewType ;
    /** @var mixed */
    private $_supportedViewTypes = array( 'html' => 1 ) ;
    /** @var ContactModel[] */
    private $contactModels ;

    /**
     * Class constructor
     *
     * @param string View Type
     * @param ContactModel[] $contactModels
     * @throws ViewException
     */
    public function __construct( $viewType = 'html', $contactModels ) {
        parent::__construct() ;
        if ( ! isset( $this->_supportedViewTypes[ $viewType ] ) ) {
            throw new ViewException( "Unsupported view type\n" ) ;
        }
        $this->_viewType = $viewType ;
        $this->setContactModels( $contactModels ) ;
    }

    /**
     * Return the HTML view
     *
     * @return string
     */
    private function _getHtmlView() {
        $body = <<<'HTML'
<button id="AddButton" onclick="addContact()">Add Contact</button><br />
<table border="1" cellspacing="0" cellpadding="2" id="contacts">
  <caption>Current Contacts</caption>
  <thead>
    <tr>
      <th>Actions</th>
      <th>Company</th>
      <th>Name</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Alternate Phone</th>
      <th>Created</th>
      <th>Updated</th>
    </tr>
  </thead>
  <tbody>
    
HTML;
        foreach ( $this->getContactModels() as $contactModel ) {
            $id = $contactModel->getId() ;
            $row  = $this->displayContactRow( $contactModel, 'list' ) ;
            $body .= "    <tr>\n$row\n    </tr>" ;
        }
    
        $body .= "  </tbody>\n</table>\n" ;
    
        return $body ;
    }

    public function displayContactRow( $contactModel, $displayMode ) {
        $id                = $contactModel->getId() ;
        $companyId         = $contactModel->getContactCompanyId() ;
        $companyController = new CompanyController( 'read' ) ;
        $companyModel      = $companyController->get( $companyId ) ;
        $companyName       = $companyModel->getCompanyName() ;
        $name              = $contactModel->getContactName() ;
        $email             = $contactModel->getContactEmail() ;
        $aphone            = $contactModel->getContactPhone() ;
        $bphone            = $contactModel->getContactAlternatePhone() ;
        $created           = $contactModel->getCreated() ;
        $updated           = $contactModel->getUpdated() ;
        switch ( $displayMode ) {
            case 'add' :
                // @todo 00 Compute companyNames
                return <<<RETVAL
      <td><button type="button" id="SaveButtonix$id" onclick="saveAddContact( '$id' )">Save</button>
          <button type="button" id="CancelButtonix$id" onclick="deleteRow( 'ix$id' )">Cancel</button>
      </td>
      <td>$companyNames</td>
      <td><input type="text" id="nameix$id" value="$name"</td>
      <td><input type="email" id="emailix$id" value="$email"</td>
      <td><input type="text" id="phoneix$id" value="$aphone"</td>
      <td><input type="text" id="alternatePhoneix$id" value="$bphone" /></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>

RETVAL;
            case 'update' :
                // @todo 00 Compute companyNames
                return <<<RETVAL
      <td><button type="button" id="SaveButtonix$id" onclick="saveAddContact( '$id' )">Save</button>
          <button type="button" id="CancelButtonix$id" onclick="deleteRow( 'ix$id' )">Cancel</button>
      </td>
      <td>$companyNames</td>
      <td><input type="text" id="nameix$id" value="$name"</td>
      <td><input type="email" id="emailix$id" value="$email"</td>
      <td><input type="text" id="phoneix$id" value="$aphone"</td>
      <td><input type="text" id="alternatePhoneix$id" value="$bphone" /></td>
      <td>$created</td>
      <td>$updated</td>
                
RETVAL;
            case 'delete' :
                return <<<RETVAL
      <td><button type="button" id="DeleteButton$id" onclick="doDeleteContact( '$id' )">Confirm Delete</button>
          <button type="button" id="CancelButton$id" onclick="cancelUpdateContactRow( '$id' )">Cancel</button>
      </td>
      <td>$companyName</td>
      <td>$name</td>
      <td>$email</td>
      <td>$aphone</td>
      <td>$bphone</td>
      <td>$created</td>
      <td>$updated</td>
                
RETVAL;
                break ;
            case 'list' :
                return <<<RETVAL
      <td><button type="button" id="EditButton$id" onclick="editContact( '$id' )">Edit</button>
          <button type="button" id="DeleteButton$id" onclick="deleteContact( 'ix$id' )">Delete</button>
      </td>
      <td>$companyName</td>
      <td>$name</td>
      <td>$email</td>
      <td>$aphone</td>
      <td>$bphone</td>
      <td>$created</td>
      <td>$updated</td>
                
RETVAL;
            default :
                throw new ViewException( 'Undefined display mode' ) ;
        }
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
     * @return ContactModel[]
     */
    public function getContactModels() {
        return $this->contactModels ;
    }

    /**
     * @param ContactModel[] $contactModels
     */
    public function setContactModels( $contactModels ) {
        $this->contactModels = $contactModels ;
    }

}