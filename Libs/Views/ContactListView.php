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
<a href="addContact.php">Add new contact</a><br />
<table border="1" cellspacing="0" cellpadding="2">
  <caption>Current Contacts</caption>
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
HTML;
        foreach ( $this->getContactModels() as $contactModel ) {
            $id = $contactModel->getId() ;
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
            $body .= <<<HTML
  <tr>
    <td>
        <a href="editContact.php?id=$id">Edit</a>
      | <a href="deleteContact.php?id=$id">Delete</a>
    </td>
    <td>$companyName</td>
    <td>$name</td>
    <td>$email</td>
    <td>$aphone</td>
    <td>$bphone</td>
    <td>$created</td>
    <td>$updated</td>
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