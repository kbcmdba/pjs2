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
 * Company List View
 */
class CompanyListView extends ListViewBase
{

    /** @var string */
    private $_viewType;

    /** @var mixed */
    private $_supportedViewTypes = [
        'html' => 1
    ];

    /** @var CompanyModel[] */
    private $_companyModels;

    /** @var array */
    private $_noteCounts = [];

    /**
     * Class constructor
     *
     * @param
     *            string View Type
     * @param CompanyModel[] $companyModels
     * @throws ViewException
     */
    public function __construct($viewType = 'html', $companyModels)
    {
        parent::__construct();
        if (! isset($this->_supportedViewTypes[$viewType])) {
            throw new ViewException("Unsupported view type\n");
        }
        $this->_viewType = $viewType;
        $this->setCompanyModels($companyModels);
    }

    /**
     * Return the HTML view
     *
     * @return string
     */
    private function _getHtmlView()
    {
        $noteController = new NoteController('read');
        $this->_noteCounts = $noteController->countByTable('company');
        $rowStyle = "treven";
        $body = <<<'HTML'
<button id="AddButton" onclick="addCompany()">Add Company</button><br />
<table id="companies">
  <caption>Current Companies</caption>
  <thead>
    <tr class="thead">
      <th rowspan="2">Actions</th>
      <th class="sortable" data-sort-type="text" onclick="sortCompaniesTable(this, 1)"><font size="+2">Company</font> <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="text" onclick="sortCompaniesTable(this, 2)">Address 1 <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="text" onclick="sortCompaniesTable(this, 3)">City <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="text" onclick="sortCompaniesTable(this, 4)">State <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="text" onclick="sortCompaniesTable(this, 5)">Zip <span class="sort-ind">&#9830;</span></th>
      <th class="sortable" data-sort-type="date" onclick="sortCompaniesTable(this, 6)">Last Contacted <span class="sort-ind">&#9830;</span></th>
      <th rowspan="2">Notes</th>
      <th class="sortable" data-sort-type="date" onclick="sortCompaniesTable(this, 8)">Created <span class="sort-ind">&#9830;</span></th>
    </tr>
    <tr class="thead">
      <th>Agency</th>
      <th>Address 2</th>
      <th>Phone</th>
      <th colspan="3">URL</th>
      <th>Updated</th>
    </tr>
  </thead>
  <tbody>
HTML;
        // No treven/trodd: per-company pair grouping comes from a #companies-
        // specific CSS rule using :nth-child(4n+1/4n+2 vs 4n+3/4n+4) so the
        // pattern survives client-side sort.
        foreach ($this->getCompanyModels() as $companyModel) {
            $id = $companyModel->getId();
            $rows = $this->displayCompanyRow($companyModel, 'list', '');
            $body .= "    <tr id='ux$id-1'>\n      {$rows[0]}\n    </tr>\n";
            $body .= "    <tr id='ux$id-2'>\n      {$rows[1]}\n    </tr>\n";
        }
        $body .= "  </tbody>\n</table>\n";
        return $body;
    }

    public function displayCompanyRow($companyModel, $displayMode, $rowStyle, $warningMsg = null)
    {
        if (null === $warningMsg) {
            $warningMsg = '';
        } else {
            $warningMsg = "<br /><span style=\"color: red;\">" . Tools::htmlOut($warningMsg) . "</span>";
        }
        $id = $companyModel->getId();
        $agencyCompanyId = $companyModel->getAgencyCompanyId();
        $companyName = Tools::htmlOut($companyModel->getCompanyName());
        $companyAddress1 = Tools::htmlOut($companyModel->getCompanyAddress1());
        $companyAddress2 = Tools::htmlOut($companyModel->getCompanyAddress2());
        $companyCity = Tools::htmlOut($companyModel->getCompanyCity());
        $companyState = Tools::htmlOut($companyModel->getCompanyState());
        $companyZip = Tools::htmlOut($companyModel->getCompanyZip());
        $companyPhone = Tools::htmlOut($companyModel->getCompanyPhone());
        $companyUrl = $companyModel->getCompanyUrl();
        $created = Tools::htmlOut($companyModel->getCreated());
        $updated = Tools::htmlOut($companyModel->getUpdated());
        $lastContacted = Tools::htmlOut($companyModel->getLastContacted());
        $encodedUrl = Tools::safeUrl($companyUrl);
        if ($agencyCompanyId > 0) {
            $agencyCompanyController = new CompanyController();
            $agencyCompanyModel = $agencyCompanyController->get($agencyCompanyId);
            $agency = Tools::htmlOut($agencyCompanyModel->getCompanyName());
        } else {
            $agency = 'None';
        }
        $row1 = $row2 = "";
        switch ($displayMode) {
            case 'add':
                $agencyList = $this->getAgencyList("ix$id", null);
                $row1 = <<<HTML
      <td rowspan="2">
        <button type="button" id="SaveButtonix$id" onclick="saveAddCompany( '$id' )">Save</button>
        <button type="button" id="CancelButtonix$id" onclick="deleteRow( 'ix$id-1' ); deleteRow( 'ix$id-2' );">Cancel</button>
        $warningMsg
      </td>
      <td><font size="+2"><input type="text" id="companyNameix$id" value="$companyName" /></font></td>
      <td><input type="text" id="companyAddress1ix$id" value="$companyAddress1" /></th>
      <td><input type="text" id="companyCityix$id" value="$companyCity" /></td>
      <td><input type="text" id="companyStateix$id" size="2" value="$companyState" /></td>
      <td><input type="text" id="companyZipix$id" size="10" value="$companyZip" /></td>
      <td><input type="text" id="lastContactedix$id" size="12" value="$lastContacted" class="datepicker" /></td>
      <td rowspan="2"></td>
      <td>$created</td>
HTML;
                $row2 = <<<HTML
      <td>$agencyList</td>
      <td><input type="text" id="companyAddress2ix$id" value="$companyAddress2" /></th>
      <td><input type="text" id="companyPhoneix$id" value="$companyPhone" /></td>
      <td colspan="3"><input type="text" id="companyUrlix$id" value="$encodedUrl" /></a></td>
      <td>$updated</td>
HTML;
                break;
            case 'delete':
                $noteCount = isset($this->_noteCounts[$id]) ? $this->_noteCounts[$id] : 0;
                $row1 = <<<HTML
      <td rowspan="2">
        <button type="button" id="DeleteButton$id" onclick="doDeleteCompany( '$id' )">Confirm Delete</button>
        <button type="button" id="CancelButton$id" onclick="cancelUpdateCompanyRow( '$id' )">Cancel</button>
        $warningMsg
      </td>
      <td><font size="+2">$companyName</font></td>
      <td>$companyAddress1</th>
      <td>$companyCity</td>
      <td>$companyState</td>
      <td>$companyZip</td>
      <td>$lastContacted</td>
      <td rowspan="2">$noteCount</td>
      <td>$created</td>
HTML;
                $row2 = <<<HTML
      <td>$agency</td>
      <td>$companyAddress2</th>
      <td>$companyPhone</td>
      <td colspan="3"><a href="$encodedUrl">$encodedUrl</a></td>
      <td>$updated</td>
HTML;
                break;
            case 'list':
                $noteCount = isset($this->_noteCounts[$id]) ? $this->_noteCounts[$id] : 0;
                $click = "onclick=\"updateCompany( '$id' )\" style=\"cursor: pointer;\"";
                $row1 = <<<HTML
      <td rowspan="2">
        <button type="button" id="UpdateButton$id" onclick="updateCompany( '$id' )">Update</button>
        <button type="button" id="DeleteButton$id" onclick="deleteCompany( '$id' )">Delete</button>
        <button type="button" id="ContactButton$id" onclick="doUpdateLastContacted( '$id' )">Contacted</button>
        $warningMsg
      </td>
      <td $click><font size="+2">$companyName</font></td>
      <td $click>$companyAddress1</td>
      <td $click>$companyCity</td>
      <td $click>$companyState</td>
      <td $click>$companyZip</td>
      <td $click>$lastContacted</td>
      <td rowspan="2"><a href="#" class="note-count-link" id="noteCount-company-$id" onclick="openNotesModal( 'company', '$id', '$companyName' ); return false;">$noteCount</a></td>
      <td>$created</td>
HTML;
                $row2 = <<<HTML
      <td $click>$agency</td>
      <td $click>$companyAddress2</td>
      <td $click>$companyPhone</td>
      <td colspan="3"><a href="$encodedUrl">$encodedUrl</a></td>
      <td>$updated</td>
HTML;
                break;
            case 'update':
                $noteCount = isset($this->_noteCounts[$id]) ? $this->_noteCounts[$id] : 0;
                $agencyList = $this->getAgencyList($id, $id);
                $row1 = <<<HTML
      <td rowspan="2">
        <button type="button" id="SaveButton$id" onclick="saveUpdateCompany( '$id' )">Save</button>
        <button type="button" id="CancelButton$id" onclick="cancelUpdateCompanyRow( '$id' )">Cancel</button>
        $warningMsg
      </td>
      <td><font size="+2"><input type="text" id="companyName$id" value="$companyName" /></font></td>
      <td><input type="text" id="companyAddress1$id" value="$companyAddress1" /></th>
      <td><input type="text" id="companyCity$id" value="$companyCity" /></td>
      <td><input type="text" id="companyState$id" size="2" value="$companyState" /></td>
      <td><input type="text" id="companyZip$id" size="10" value="$companyZip" /></td>
      <td><input type="text" id="lastContacted$id" size="12" value="$lastContacted" class="datepicker" /></td>
      <td rowspan="2"><a href="#" class="note-count-link" id="noteCount-company-$id" onclick="openNotesModal( 'company', '$id', '$companyName' ); return false;">$noteCount</a></td>
      <td>$created</td>
HTML;
                $row2 = <<<HTML
      <td>$agencyList</td>
      <td><input type="text" id="companyAddress2$id" value="$companyAddress2" /></th>
      <td><input type="text" id="companyPhone$id" value="$companyPhone" /></td>
      <td colspan="3"><input type="text" id="companyUrl$id" value="$encodedUrl" /></a></td>
      <td>$updated</td>
HTML;
                break;
        }
        return [
            $row1,
            $row2
        ];
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
     * @return CompanyModel[]
     */
    public function getCompanyModels()
    {
        return $this->_companyModels;
    }

    /**
     *
     * @param CompanyModel[] $companyModels
     */
    public function setCompanyModels($companyModels)
    {
        $this->_companyModels = $companyModels;
    }

    /**
     * Get company SELECT list
     *
     * @param string $id
     *            Field ID
     * @param integer $value
     *            The selected value
     * @return string
     */
    public function getCompanyList($id, $value)
    {
        $retVal = "<select id=\"companyId$id\" >\n  <option value=\"\">---</option>";
        $companyController = new CompanyController();
        $companies = $companyController->getAll();
        foreach ($companies as $company) {
            $cid = $company->getId();
            $cname = Tools::htmlOut($company->getCompanyName());
            $selected = ($cid === $value) ? "selected=\"selected\"" : "";
            $retVal .= "  <option value=\"$cid\" $selected>$cname</option>\n";
        }
        $retVal .= "</select>\n";
        $retVal .= "<a href=\"#\" class=\"quick-add-btn\" onclick=\"openAddCompanyModal( 'companyId$id' ); return false;\" title=\"Quick add company\">+</a>\n";
        return $retVal;
    }

    /**
     * Get Agency SELECT list
     *
     * @param string $id
     *            Field ID
     * @param integer $value
     *            Selected Value
     * @return string
     */
    public function getAgencyList($id, $value)
    {
        $retVal = "<select id=\"agencyCompanyId$id\" >\n  <option value=\"\">---</option>";
        $companyController = new CompanyController();
        $agencies = $companyController->getAll();
        foreach ($agencies as $agency) {
            $selected = ($agency->getId() === $value) ? "selected=\"selected\"" : "";
            $aname = Tools::htmlOut($agency->getCompanyName());
            $aid = $agency->getId();
            $retVal .= "  <option value=\"$aid\" $selected>$aname</option>\n";
        }
        $retVal .= "</select>\n";
        return $retVal;
    }
}
