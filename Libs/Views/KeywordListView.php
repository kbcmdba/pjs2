<?php

/**
 * phpjobseeker
 *
 * Copyright (C) 2009, 2015, 2017 Kevin Benton - kbenton at bentonfam dot org
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
namespace com\kbcmdba\pjs2\Libs\Views;

use com\kbcmdba\pjs2\Libs\Controllers\ApplicationStatusController;
use com\kbcmdba\pjs2\Libs\Controllers\CompanyController;
use com\kbcmdba\pjs2\Libs\Controllers\ContactController;
use com\kbcmdba\pjs2\Libs\Exceptions\ViewException;
use com\kbcmdba\pjs2\Libs\Models\JobModel;
use com\kbcmdba\pjs2\Libs\Models\KeywordModel;
use com\kbcmdba\pjs2\Libs\Tools;

/**
 * Keyword List View
 */
class KeywordListView extends ListViewBase
{

    /** @var string */
    private $viewType;

    /** @var mixed */
    private $supportedViewTypes = [
        'html' => 1
    ];

    /** @var KeywordModel[] */
    private $keywordModels;

    /**
     * Class constructor
     *
     * @param
     *            string View Type
     * @param JobModel[] $jobModels
     * @throws ViewException
     */
    public function __construct($viewType = 'html', $keywordModels)
    {
        parent::__construct();
        if (! isset($this->supportedViewTypes[$viewType])) {
            throw new ViewException("Unsupported view type\n");
        }
        $this->viewType = $viewType;
        $this->setKeywordModels($keywordModels);
    }

    /**
     * Return the HTML view
     *
     * @return string
     */
    private function _getHtmlView()
    {
        $body = <<<'HTML'
<button id="AddButton" onclick="addJob()">Add Job</button><br />
<table border="1" cellspacing="0" cellpadding="2" id="jobs">
  <caption>Current Jobs</caption>
  <thead>
    <tr>
      <th>Actions</th>
      <th>Keyword</th>
      <th>SortKey</th>
      <th>Created</th>
      <th>Updated</th>
    </tr>
  </thead>
  <tbody>

HTML;
        foreach ($this->getKeywordModels() as $keywordModel) {
            $id = $keywordModel->getId();
            $row = $this->displayKeywordRow($keywordModel, 'list');
            $body .= "    <tr id=\"ux$id\">\n$row\n    </tr>";
        }
        
        $body .= "  </tbody>\n</table>\n";
        
        return $body;
    }

!! LEFT OFF HERE converting JobListView to KeywordListView

    private function _getListValues($id, $contactId, $companyId, $applicationStatusId, $urgency)
    {
        $contactListView = new ContactListView('html', null);
        $this->contactList = $contactListView->getContactList("$id", $contactId);
        $companyListView = new CompanyListView('html', null);
        $this->companyList = $companyListView->getCompanyList("$id", $companyId);
        $applicationStatusListView = new ApplicationStatusListView('html', null);
        $this->applicationStatusList = $applicationStatusListView->getApplicationStatusList("$id", $applicationStatusId);
        $this->urgencyList = "      <select id=\"urgency$id\">\n";
        foreach ([
            '---',
            'high',
            'medium',
            'low'
        ] as $urgency_value) {
            $selected = '';
            if ($urgency_value === $urgency) {
                $selected = 'selected="selected"';
            }
            if (('---' === $urgency_value) && ((! isset($urgency)) || ('' === $urgency))) {
                $selected = 'selected="selected"';
            }
            $this->urgencyList .= "       <option value=\"$urgency_value\" $selected>$urgency_value</option>\n";
        }
        $this->urgencyList .= "     </select>\n";
    }

    /**
     *
     * @param JobModel $jobModel
     * @param String $displayMode
     * @param String $errorMessage
     */
    public function displayJobRow($jobModel, $displayMode, $errorMessage = '')
    {
        $id = $jobModel->getId();
        $primaryContactId = $companyId = $applicationStatusId = $lastStatusChange = $urgency = $created = $updated = $nextActionDue = $nextAction = $positionTitle = $location = $url = $dueClass = '';
        if ('add' !== $displayMode) {
            $primaryContactId = $jobModel->getPrimaryContactId();
            $contactController = new ContactController('read');
            if ($primaryContactId >= 1) {
                $contactModel = $contactController->get($primaryContactId);
                $contactName = $contactModel->getContactName();
            } else {
                $contactName = '---';
            }
            $companyId = $jobModel->getCompanyId();
            $companyController = new CompanyController('read');
            if ($companyId >= 1) {
                $companyModel = $companyController->get($companyId);
                $companyName = $companyModel->getCompanyName();
            } else {
                $companyName = '---';
            }
            $applicationStatusId = $jobModel->getApplicationStatusId();
            $applicationStatusController = new ApplicationStatusController('read');
            if ($applicationStatusId >= 1) {
                $applicationStatusModel = $applicationStatusController->get($applicationStatusId);
                $applicationStatusValue = $applicationStatusModel->getStatusValue();
                $applicationStatusStyle = $applicationStatusModel->getStyle();
            } else {
                $applicationStatusValue = '---';
                $applicationStatusStyle = '';
            }
            $lastStatusChange = $jobModel->getLastStatusChange();
            $urgency = $jobModel->getUrgency();
            $created = $jobModel->getCreated();
            $updated = $jobModel->getUpdated();
            $nextActionDue = $jobModel->getNextActionDue();
            $nextAction = $jobModel->getNextAction();
            $positionTitle = $jobModel->getPositionTitle();
            $location = $jobModel->getLocation();
            $url = $jobModel->getUrl();
            $now = Tools::currentTimestamp();
            $dueClass = (isset($nextActionDue) && ($nextActionDue !== '') && ($nextActionDue < $now)) ? "class=\"overdue\"" : "";
        }
        switch ($displayMode) {
            case 'add':
                $this->_getListValues("ix$id", $primaryContactId, $companyId, $applicationStatusId, $urgency);
                return <<<HTML
      <td><button type="button" id="SaveButtonix$id" onclick="saveAddJob( '$id' )">Save</button>
          <button type="button" id="CancelButtonix$id" onclick="deleteRow( 'ix$id' )">Cancel</button>
          $errorMessage
      </td>
      <td>{$this->urgencyList}</td>
      <td><input type="text" id="positionTitleix$id" value="$positionTitle" /></td>
      <td><input type="text" id="locationix$id" value="$location" /></td>
      <td>{$this->companyList}</td>
      <td>{$this->contactList}</td>
      <td>{$this->applicationStatusList}</td>
      <td><input type="text" id="nextActionix$id" value="$nextAction" /></td>
      <td $dueClass><input type="text" id="nextActionDueix$id" value="$nextActionDue" class="datepicker" /></td>
      <td><input type="text" id="urlix$id" value="$url" /></td>
      <td><input type="text" id="lastStatusChangeix$id" value="$lastStatusChange" class="datepicker" /></td>
      <td>$created</td>
      <td>$updated</td>

HTML;
                break;
            case 'update':
                $this->_getListValues($id, $primaryContactId, $companyId, $applicationStatusId, $urgency);
                return <<<HTML
      <td><button type="button" id="SaveButton$id" onclick="saveUpdateJob( '$id' )">Save</button>
          <button type="button" id="CancelButton$id" onclick="cancelUpdateJobRow( '$id' )">Cancel</button>
          $errorMessage
      </td>
      <td>{$this->urgencyList}</td>
      <td><input type="text" id="positionTitle$id" value="$positionTitle" /></td>
      <td><input type="text" id="location$id" value="$location" /></td>
      <td>{$this->companyList}</td>
      <td>{$this->contactList}</td>
      <td>{$this->applicationStatusList}</td>
      <td><input type="text" id="nextAction$id" value="$nextAction" /></td>
      <td $dueClass><input type="text" id="nextActionDue$id" value="$nextActionDue" class="datepicker" /></td>
      <td><input type="text" id="url$id" value="$url" /></td>
      <td><input type="text" id="lastStatusChange$id" value="$lastStatusChange" class="datepicker" /></td>
      <td>$created</td>
      <td>$updated</td>

HTML;
                break;
            case 'delete':
                return <<<HTML
      <td><button type="button" id="DeleteButton$id" onclick="doDeleteJob( '$id' )">Confirm Delete</button>
          <button type="button" id="CancelButton$id" onclick="cancelUpdateJobRow( '$id' )">Cancel</button>
          $errorMessage
      </td>
      <td>$urgency</td>
      <td>$positionTitle</td>
      <td>$location</td>
      <td>$companyName</td>
      <td>$contactName</td>
      <td style="$applicationStatusStyle">$applicationStatusValue</td>
      <td>$nextAction</td>
      <td $dueClass>$nextActionDue</td>
      <td><a href="$url">$url</a></td>
      <td>$lastStatusChange</td>
      <td>$created</td>
      <td>$updated</td>

HTML;
                break;
            case 'list':
                return <<<HTML
      <td><button type="button" id="UpdateButton$id" onclick="updateJob( '$id' )">Update</button>
          <button type="button" id="DeleteButton$id" onclick="deleteJob( '$id' )">Delete</button>
          $errorMessage
      </td>
      <td>$urgency</td>
      <td>$positionTitle</td>
      <td>$location</td>
      <td>$companyName</td>
      <td>$contactName</td>
      <td style="$applicationStatusStyle">$applicationStatusValue</td>
      <td>$nextAction</td>
      <td $dueClass>$nextActionDue</td>
      <td><a href="$url" target="_blank">Link</a></td>
      <td>$lastStatusChange</td>
      <td>$created</td>
      <td>$updated</td>

HTML;
                break;
            default:
                throw new ViewException('Undefined display mode');
        }
        // Should never ever get here.
    }

    /**
     *
     * @return string
     * @throws ViewException
     */
    public function getView()
    {
        switch ($this->viewType) {
            case 'html':
                return $this->_getHtmlView();
            default:
                throw new ViewException("Unsupported view type.");
        }
    }

    /**
     *
     * @return JobModel[]
     */
    public function getJobModels()
    {
        return $this->jobModels;
    }

    /**
     *
     * @param JobModel[] $jobModels
     */
    public function setJobModels($jobModels)
    {
        $this->jobModels = $jobModels;
    }
}
