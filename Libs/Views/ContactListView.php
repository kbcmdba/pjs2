<?php

/**
 * phpjobseeker
 *
 * Copyright (C) 2009, 2015, 2017, 2026 Kevin Benton - kbenton at bentonfam dot org
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
namespace com\kbcmdba\pjs2;

/**
 * Contact List View
 */
class ContactListView extends ListViewBase
{

    /** @var string */
    private $_viewType;

    /** @var mixed */
    private $_supportedViewTypes = [
        'html' => 1
    ];

    /** @var ContactModel[] */
    private $contactModels;

    /** @var array */
    private $_noteCounts = [];

    /**
     * Class constructor
     *
     * @param
     *            string View Type
     * @param ContactModel[] $contactModels
     * @throws ViewException
     */
    public function __construct($viewType = 'html', $contactModels = null)
    {
        parent::__construct();
        if (! isset($this->_supportedViewTypes[$viewType])) {
            throw new ViewException("Unsupported view type\n");
        }
        $this->_viewType = $viewType;
        $this->setContactModels($contactModels);
    }

    /**
     * Return the HTML view
     *
     * @return string
     */
    private function _getHtmlView()
    {
        $noteController = new NoteController('read');
        $this->_noteCounts = $noteController->countByTable('contact');
        $body = <<<'HTML'
<button id="AddButton" onclick="addContact()">Add Contact</button><br />
<table id="contacts">
  <caption>Current Contacts</caption>
  <thead>
    <tr>
      <th>Actions</th>
      <th>Company</th>
      <th>Name</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Alternate Phone</th>
      <th>Last Contacted</th>
      <th>Notes</th>
      <th>Created</th>
      <th>Updated</th>
    </tr>
  </thead>
  <tbody>
    
HTML;
        foreach ($this->getContactModels() as $contactModel) {
            $id = $contactModel->getId();
            $row = $this->displayContactRow($contactModel, 'list');
            $body .= "    <tr id=\"ux$id\">\n$row\n    </tr>";
        }
        
        $body .= "  </tbody>\n</table>\n";
        
        return $body;
    }

    public function displayContactRow($contactModel, $displayMode, $errorMessage = '')
    {
        $id = $contactModel->getId();
        if ("add" === $displayMode) {
            $companyId = $companyName = $name = $email = $aphone = $bphone = $lastContacted = $created = $updated = '';
        } else {
            $companyId = $contactModel->getContactCompanyId();
            $companyController = new CompanyController('read');
            if ($companyId > 0) {
                $companyModel = $companyController->get($companyId);
                $companyName = Tools::htmlOut($companyModel->getCompanyName());
            } else {
                $companyName = '---';
            }
            $name = Tools::htmlOut($contactModel->getContactName());
            $email = Tools::htmlOut($contactModel->getContactEmail());
            $aphone = Tools::htmlOut($contactModel->getContactPhone());
            $bphone = Tools::htmlOut($contactModel->getContactAlternatePhone());
            $lastContacted = Tools::htmlOut($contactModel->getLastContacted());
            $created = Tools::htmlOut($contactModel->getCreated());
            $updated = Tools::htmlOut($contactModel->getUpdated());
        }
        $errorMessage = Tools::htmlOut($errorMessage);
        switch ($displayMode) {
            case 'add':
                $companyListView = new CompanyListView('html', null);
                $companyNames = $companyListView->getCompanyList("ix$id", $companyId);
                return <<<RETVAL
      <td><button type="button" id="SaveButtonix$id" onclick="saveAddContact( '$id' )">Save</button>
          <button type="button" id="CancelButtonix$id" onclick="deleteRow( 'ix$id' )">Cancel</button>
          $errorMessage
      </td>
      <td>$companyNames</td>
      <td><input type="text" id="nameix$id" value="$name"</td>
      <td><input type="email" id="emailix$id" value="$email"</td>
      <td><input type="text" id="phoneix$id" value="$aphone"</td>
      <td><input type="text" id="alternatePhoneix$id" value="$bphone" /></td>
      <td><input type="text" id="lastContactedix$id" value="$lastContacted" class="datepicker" /></td>
      <td></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>

RETVAL;
            case 'update':
                $noteCount = isset($this->_noteCounts[$id]) ? $this->_noteCounts[$id] : 0;
                $companyListView = new CompanyListView('html', null);
                $companyNames = $companyListView->getCompanyList("$id", $companyId);
                return <<<RETVAL
      <td><button type="button" id="SaveButton$id" onclick="saveUpdateContact( '$id' )">Save</button>
          <button type="button" id="CancelButton$id" onclick="cancelUpdateContactRow( '$id' )">Cancel</button>
          $errorMessage
      </td>
      <td>$companyNames</td>
      <td><input type="text" id="name$id" value="$name"</td>
      <td><input type="email" id="email$id" value="$email"</td>
      <td><input type="text" id="phone$id" value="$aphone"</td>
      <td><input type="text" id="alternatePhone$id" value="$bphone" /></td>
      <td><input type="text" id="lastContacted$id" value="$lastContacted" class="datepicker" /></td>
      <td><a href="#" class="note-count-link" id="noteCount-contact-$id" onclick="openNotesModal( 'contact', '$id', '$name' ); return false;">$noteCount</a></td>
      <td>$created</td>
      <td>$updated</td>

RETVAL;
            case 'delete':
                $noteCount = isset($this->_noteCounts[$id]) ? $this->_noteCounts[$id] : 0;
                return <<<RETVAL
      <td><button type="button" id="DeleteButton$id" onclick="doDeleteContact( '$id' )">Confirm Delete</button>
          <button type="button" id="CancelButton$id" onclick="cancelUpdateContactRow( '$id' )">Cancel</button>
          $errorMessage
      </td>
      <td>$companyName</td>
      <td>$name</td>
      <td>$email</td>
      <td>$aphone</td>
      <td>$bphone</td>
      <td>$lastContacted</td>
      <td>$noteCount</td>
      <td>$created</td>
      <td>$updated</td>

RETVAL;
                break;
            case 'list':
                $noteCount = isset($this->_noteCounts[$id]) ? $this->_noteCounts[$id] : 0;
                $click = "onclick=\"updateContact( '$id' )\" style=\"cursor: pointer;\"";
                $clickNow = "onclick=\"doUpdateContactLastContacted( '$id' )\" style=\"cursor: pointer;\" title=\"Click to mark as contacted now\"";
                return <<<RETVAL
      <td><button type="button" id="UpdateButton$id" onclick="updateContact( '$id' )">Update</button>
          <button type="button" id="DeleteButton$id" onclick="deleteContact( '$id' )">Delete</button>
          <button type="button" id="ContactButton$id" onclick="doUpdateContactLastContacted( '$id' )">Contacted</button>
          $errorMessage
      </td>
      <td $click>$companyName</td>
      <td $click>$name</td>
      <td $click>$email</td>
      <td $click>$aphone</td>
      <td $click>$bphone</td>
      <td $clickNow>$lastContacted</td>
      <td><a href="#" class="note-count-link" id="noteCount-contact-$id" onclick="openNotesModal( 'contact', '$id', '$name' ); return false;">$noteCount</a></td>
      <td $click>$created</td>
      <td $click>$updated</td>

RETVAL;
            default:
                throw new ViewException('Undefined display mode');
        }
    }

    /**
     *
     * @return string
     * @throws ViewException
     */
    public function getView()
    {
        switch ($this->_viewType) {
            case 'html':
                return $this->_getHtmlView();
            default:
                throw new ViewException("Unsupported view type.");
        }
    }

    /**
     *
     * @return ContactModel[]
     */
    public function getContactModels()
    {
        return $this->contactModels;
    }

    /**
     *
     * @param ContactModel[] $contactModels
     */
    public function setContactModels($contactModels)
    {
        $this->contactModels = $contactModels;
    }

    /**
     * Get contact SELECT list
     *
     * @param string $id
     *            Field ID
     * @param integer $value
     *            The selected value
     * @return string
     */
    public function getContactList($id, $value)
    {
        $retVal = "<select id=\"contactId$id\" >\n  <option value=\"\">---</option>";
        $contactController = new ContactController();
        $contacts = $contactController->getAll();
        foreach ($contacts as $contact) {
            $cid = $contact->getId();
            $cname = Tools::htmlOut($contact->getContactName());
            $selected = ($cid === $value) ? "selected=\"selected\"" : "";
            $retVal .= "  <option value=\"$cid\" $selected>$cname</option>\n";
        }
        $retVal .= "</select>\n";
        return $retVal;
    }
}
