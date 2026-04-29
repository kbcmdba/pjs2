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
 * Job List View
 */
class JobListView extends ListViewBase
{

    /** @var string */
    private $_viewType;

    /** @var mixed */
    private $_supportedViewTypes = [
        'html' => 1
    ];

    /** @var JobModel[] */
    private $_jobModels;

    /** @var string */
    private $_contactList;

    /** @var string */
    private $_companyList;

    /** @var string */
    private $_applicationStatusList;

    /** @var array */
    private $_noteCounts = [];

    /** @var string */
    private $_urgencyList;

    /**
     * Class constructor
     *
     * @param
     *            string View Type
     * @param JobModel[] $jobModels
     * @throws ViewException
     */
    public function __construct($viewType = 'html', $jobModels)
    {
        parent::__construct();
        if (! isset($this->_supportedViewTypes[$viewType])) {
            throw new ViewException("Unsupported view type\n");
        }
        $this->_viewType = $viewType;
        $this->setJobModels($jobModels);
    }

    /**
     * Return the HTML view
     *
     * @return string
     */
    private function _getHtmlView()
    {
        // @todo Display jobs in two rows
        // Urgency Location Title NextAction Url
        // Status Company Contact NADue LastStatusChange
        $noteController = new NoteController('read');
        $this->_noteCounts = $noteController->countByTable('job');
        $body = <<<'HTML'
<button id="AddButton" onclick="addJob()">Add Job</button>
<button id="ToggleClosedButton" onclick="toggleClosedJobs()">Hide Closed</button><br />
<table id="jobs">
  <caption>Current Jobs</caption>
  <thead>
    <tr>
      <th>Actions</th>
      <th>URL</th>
      <th class="sortable" data-sort-type="urgency" onclick="sortJobsTable(this, 2)">Urgency <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="text" onclick="sortJobsTable(this, 3)">Title <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="num" onclick="sortJobsTable(this, 4)">Comp<br />Range <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="text" onclick="sortJobsTable(this, 5)">Location <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="text" onclick="sortJobsTable(this, 6)">Company <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="text" onclick="sortJobsTable(this, 7)">Contact <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="num" onclick="sortJobsTable(this, 8)">Status <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="text" onclick="sortJobsTable(this, 9)">Next Action <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="date" onclick="sortJobsTable(this, 10)">Next Action Due <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="date" onclick="sortJobsTable(this, 11)">Last Status Change <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="num" onclick="sortJobsTable(this, 12)">Notes <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="date" onclick="sortJobsTable(this, 13)">Created <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="date" onclick="sortJobsTable(this, 14)">Updated <span class="sort-ind">&#9830;</span></th>
    </tr>
  </thead>
  <tbody>

HTML;
        // No treven/trodd here — let CSS :nth-child handle the alternation so
        // it follows DOM position. After client-side sort the rows reorder, and
        // a class-based labeling scheme would carry stale colors with the rows
        // (every-other-row pattern visibly breaks). nth-child stays correct
        // regardless of sort. CompanyListView still uses the class-based
        // pattern because its 2-row-per-company grouping needs both rows to
        // share the same background — that case is the exception.
        foreach ($this->getJobModels() as $jobModel) {
            $id = $jobModel->getId();
            $row = $this->displayJobRow($jobModel, 'list');
            $classAttr = '';
            if (! $jobModel->getIsActiveSummary()) {
                $classAttr = ' class="closed-job"';
            }
            $body .= "    <tr id=\"ux$id\"$classAttr>\n$row\n    </tr>";
        }
        
        $body .= "  </tbody>\n</table>\n";
        
        return $body;
    }

    private function _getListValues($id, $contactId, $companyId, $applicationStatusId, $urgency)
    {
        $contactListView = new ContactListView('html', null);
        $this->_contactList = $contactListView->getContactList("$id", $contactId);
        $companyListView = new CompanyListView('html', null);
        $this->_companyList = $companyListView->getCompanyList("$id", $companyId);
        $applicationStatusListView = new ApplicationStatusListView('html', null);
        $this->_applicationStatusList = $applicationStatusListView->getApplicationStatusList("$id", $applicationStatusId);
        $this->_urgencyList = "      <select id=\"urgency$id\">\n";
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
            $this->_urgencyList .= "       <option value=\"$urgency_value\" $selected>$urgency_value</option>\n";
        }
        $this->_urgencyList .= "     </select>\n";
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
        $primaryContactId = $companyId = $applicationStatusId = $urgency = $created = $updated = $nextActionDue = $nextAction = $positionTitle = $location = $url = $dueClass = '';
        $lastStatusChange = ('add' === $displayMode) ? date('Y-m-d H:i:s') : '';
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
                $applicationStatusSortKey = $applicationStatusModel->getSortKey();
                $applicationStatusIsActive = (bool) $applicationStatusModel->getIsActive();
            } else {
                $applicationStatusValue = '---';
                $applicationStatusStyle = '';
                $applicationStatusSortKey = 9999;
                $applicationStatusIsActive = false;
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
            $compRangeLow = $jobModel->getCompRangeLow();
            $compRangeHigh = $jobModel->getCompRangeHigh();
            $now = Tools::currentTimestamp();
            // Highlight Next Action Due cell when it has elapsed AND the job is
            // still active (not in a closed status). Already-closed jobs don't
            // get the overdue treatment - if it's CLOSED/MISMATCH/etc. there's
            // no action to be late on.
            $dueClass = (
                isset($nextActionDue)
                && ($nextActionDue !== '')
                && ($nextActionDue < $now)
                && $applicationStatusIsActive
            ) ? "class=\"overdue\"" : "";
            // Escape all output variables to prevent XSS
            $positionTitle = Tools::htmlOut($positionTitle);
            $location = Tools::htmlOut($location);
            $safeUrl = Tools::safeUrl($url);
            $url = Tools::htmlOut($url);
            $nextAction = Tools::htmlOut($nextAction);
            $nextActionDue = Tools::htmlOut($nextActionDue);
            $lastStatusChange = Tools::htmlOut($lastStatusChange);
            $companyName = Tools::htmlOut($companyName);
            $contactName = Tools::htmlOut($contactName);
            $applicationStatusValue = Tools::htmlOut($applicationStatusValue);
            $applicationStatusStyle = Tools::htmlOut($applicationStatusStyle);
            $urgency = Tools::htmlOut($urgency);
            $created = Tools::htmlOut($created);
            $updated = Tools::htmlOut($updated);
        }
        // Comp range display: high on top, low on bottom (stacked) when both present
        if (isset($compRangeHigh) && $compRangeHigh !== null && isset($compRangeLow) && $compRangeLow !== null) {
            $compRangeDisplay = '$' . number_format($compRangeHigh) . '<br />$' . number_format($compRangeLow);
        } elseif (isset($compRangeHigh) && $compRangeHigh !== null) {
            $compRangeDisplay = '$' . number_format($compRangeHigh);
        } elseif (isset($compRangeLow) && $compRangeLow !== null) {
            $compRangeDisplay = '$' . number_format($compRangeLow) . '+';
        } else {
            $compRangeDisplay = '';
        }
        $compRangeLowInput = (isset($compRangeLow) && $compRangeLow !== null) ? (int) $compRangeLow : '';
        $compRangeHighInput = (isset($compRangeHigh) && $compRangeHigh !== null) ? (int) $compRangeHigh : '';
        $errorMessage = Tools::htmlOut($errorMessage);
        switch ($displayMode) {
            case 'add':
                $this->_getListValues("ix$id", $primaryContactId, $companyId, $applicationStatusId, $urgency);
                return <<<HTML
      <td><button type="button" id="SaveButtonix$id" onclick="saveAddJob( '$id' )">Save</button>
          <button type="button" id="CancelButtonix$id" onclick="deleteRow( 'ix$id' )">Cancel</button>
          $errorMessage
      </td>
      <td><input type="text" id="urlix$id" value="$url" onblur="checkDuplicateUrl( 'urlix$id' )" /></td>
      <td>{$this->_urgencyList}</td>
      <td><input type="text" id="positionTitleix$id" value="$positionTitle" /></td>
      <td>
        <input type="number" id="compRangeHighix$id" value="$compRangeHighInput" placeholder="High" style="width: 90px; display: block; margin-bottom: 2px;" />
        <input type="number" id="compRangeLowix$id" value="$compRangeLowInput" placeholder="Low" style="width: 90px; display: block;" />
      </td>
      <td><input type="text" id="locationix$id" value="$location" /></td>
      <td>{$this->_companyList}</td>
      <td>{$this->_contactList}</td>
      <td>{$this->_applicationStatusList}</td>
      <td><input type="text" id="nextActionix$id" value="$nextAction" /></td>
      <td $dueClass><input type="text" id="nextActionDueix$id" value="$nextActionDue" class="datepicker" /></td>
      <td><input type="text" id="lastStatusChangeix$id" value="$lastStatusChange" class="datepicker" /></td>
      <td></td>
      <td>$created</td>
      <td>$updated</td>

HTML;
                break;
            case 'update':
                $noteCount = isset($this->_noteCounts[$id]) ? $this->_noteCounts[$id] : 0;
                $this->_getListValues($id, $primaryContactId, $companyId, $applicationStatusId, $urgency);
                return <<<HTML
      <td><button type="button" id="SaveButton$id" onclick="saveUpdateJob( '$id' )">Save</button>
          <button type="button" id="CancelButton$id" onclick="cancelUpdateJobRow( '$id' )">Cancel</button>
          $errorMessage
      </td>
      <td><input type="text" id="url$id" value="$url" onblur="checkDuplicateUrl( 'url$id', '$id' )" /></td>
      <td>{$this->_urgencyList}</td>
      <td><input type="text" id="positionTitle$id" value="$positionTitle" /></td>
      <td>
        <input type="number" id="compRangeHigh$id" value="$compRangeHighInput" placeholder="High" style="width: 90px; display: block; margin-bottom: 2px;" />
        <input type="number" id="compRangeLow$id" value="$compRangeLowInput" placeholder="Low" style="width: 90px; display: block;" />
      </td>
      <td><input type="text" id="location$id" value="$location" /></td>
      <td>{$this->_companyList}</td>
      <td>{$this->_contactList}</td>
      <td>{$this->_applicationStatusList}</td>
      <td><input type="text" id="nextAction$id" value="$nextAction" /></td>
      <td $dueClass><input type="text" id="nextActionDue$id" value="$nextActionDue" class="datepicker" /></td>
      <td><input type="text" id="lastStatusChange$id" value="$lastStatusChange" class="datepicker" /></td>
      <td><a href="#" class="note-count-link" id="noteCount-job-$id" onclick="openNotesModal( 'job', '$id', '$positionTitle' ); return false;">$noteCount</a></td>
      <td>$created</td>
      <td>$updated</td>

HTML;
                break;
            case 'delete':
                $noteCount = isset($this->_noteCounts[$id]) ? $this->_noteCounts[$id] : 0;
                return <<<HTML
      <td><button type="button" id="DeleteButton$id" onclick="doDeleteJob( '$id' )">Confirm Delete</button>
          <button type="button" id="CancelButton$id" onclick="cancelUpdateJobRow( '$id' )">Cancel</button>
          $errorMessage
      </td>
      <td><a href="$safeUrl">$url</a></td>
      <td>$urgency</td>
      <td>$positionTitle</td>
      <td>$compRangeDisplay</td>
      <td>$location</td>
      <td>$companyName</td>
      <td>$contactName</td>
      <td style="$applicationStatusStyle">$applicationStatusValue</td>
      <td>$nextAction</td>
      <td $dueClass>$nextActionDue</td>
      <td>$lastStatusChange</td>
      <td>$noteCount</td>
      <td>$created</td>
      <td>$updated</td>

HTML;
                break;
            case 'list':
                $noteCount = isset($this->_noteCounts[$id]) ? $this->_noteCounts[$id] : 0;
                $click = "onclick=\"updateJob( '$id' )\" style=\"cursor: pointer;\"";
                $clickNow = "onclick=\"updateJobSetNow( '$id' )\" style=\"cursor: pointer;\" title=\"Click to edit and set to today\"";
                return <<<HTML
      <td><button type="button" id="UpdateButton$id" onclick="updateJob( '$id' )">Update</button>
          <button type="button" id="DetailsButton$id" onclick="window.location.href='jobDetail.php?id=$id'" title="View full job detail page (with notes and breadcrumbs)">Details</button>
          <button type="button" id="DeleteButton$id" onclick="deleteJob( '$id' )">Delete</button>
          $errorMessage
      </td>
      <td><a href="?jobId=$id" onclick="reviewJob( '$id', '$safeUrl' ); return false;" data-status-id="$applicationStatusId">Review</a> | <a href="$safeUrl" target="_blank">New Tab</a></td>
      <td $click>$urgency</td>
      <td $click>$positionTitle</td>
      <td $click data-sort="$compRangeHighInput">$compRangeDisplay</td>
      <td $click>$location</td>
      <td $click>$companyName</td>
      <td $click>$contactName</td>
      <td style="$applicationStatusStyle cursor: pointer;" onclick="updateJob( '$id' )" data-sort="$applicationStatusSortKey">$applicationStatusValue</td>
      <td $click>$nextAction</td>
      <td $dueClass $click>$nextActionDue</td>
      <td $clickNow>$lastStatusChange</td>
      <td><a href="#" class="note-count-link" id="noteCount-job-$id" onclick="openNotesModal( 'job', '$id', '$positionTitle' ); return false;">$noteCount</a></td>
      <td $click>$created</td>
      <td $click>$updated</td>

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
        switch ($this->_viewType) {
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
        return $this->_jobModels;
    }

    /**
     *
     * @param JobModel[] $jobModels
     */
    public function setJobModels($jobModels)
    {
        $this->_jobModels = $jobModels;
    }
}
