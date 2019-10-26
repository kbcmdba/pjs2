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

use com\kbcmdba\pjs2\Libs\Controllers\CompanyController;
use com\kbcmdba\pjs2\Libs\Exceptions\ViewException;
use com\kbcmdba\pjs2\Libs\Models\CompanyModel;

/**
 * Company List View
 */
class CompanyListView extends ListViewBase
{

    /** @var string */
    private $viewType;

    /** @var mixed */
    private $supportedViewTypes = [
        'html' => 1
    ];

    /** @var CompanyModel[] */
    private $companyModels;

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
        if (! isset($this->supportedViewTypes[$viewType])) {
            throw new ViewException("Unsupported view type\n");
        }
        $this->viewType = $viewType;
        $this->setCompanyModels($companyModels);
    }

    /**
     * Return the HTML view
     *
     * @return string
     */
    private function _getHtmlView()
    {
        $rowStyle = "even";
        $body = <<<'HTML'
<button id="AddButton" onclick="addCompany()">Add Company</button><br />
<table border="1" cellspacing="0" cellpadding="2" id="companies">
  <caption>Current Companies</caption>
  <thead>
    <tr class="thead">
      <th rowspan="2">Actions</th>
      <th><font size="+2">Company</font></th>
      <th>Address 1</th>
      <th>City</th>
      <th>State</th>
      <th>Zip</th>
      <th>Last Contacted</th>
      <th>Created</th>
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
        foreach ($this->getCompanyModels() as $companyModel) {
            $id = $companyModel->getId();
            $rowStyle = ("even" === $rowStyle) ? 'odd' : 'even';
            $rows = $this->displayCompanyRow($companyModel, 'list', $rowStyle);
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
            $warningMsg = "<br /><span style=\"color: red;\">$warningMsg</span>";
        }
        $id = $companyModel->getId();
        $agencyCompanyId = $companyModel->getAgencyCompanyId();
        $companyName = $companyModel->getCompanyName();
        $companyAddress1 = $companyModel->getCompanyAddress1();
        $companyAddress2 = $companyModel->getCompanyAddress2();
        $companyCity = $companyModel->getCompanyCity();
        $companyState = $companyModel->getCompanyState();
        $companyZip = $companyModel->getCompanyZip();
        $companyPhone = $companyModel->getCompanyPhone();
        $companyUrl = $companyModel->getCompanyUrl();
        $created = $companyModel->getCreated();
        $updated = $companyModel->getUpdated();
        $lastContacted = $companyModel->getLastContacted();
        $encodedUrl = htmlspecialchars($companyUrl);
        if ($agencyCompanyId > 0) {
            $agencyCompanyController = new CompanyController();
            $agencyCompanyModel = $agencyCompanyController->get($agencyCompanyId);
            $agency = $agencyCompanyModel->getCompanyName();
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
                $row1 = <<<HTML
      <td rowspan="2">
        <button type="button" id="UpdateButton$id" onclick="updateCompany( '$id' )">Update</button>
        <button type="button" id="DeleteButton$id" onclick="deleteCompany( '$id' )">Delete</button>
        <button type="button" id="ContactButton$id" onclick="doUpdateLastContacted( '$id' )">Contacted</button>
        $warningMsg
      </td>
      <td><font size="+2">$companyName</font></td>
      <td>$companyAddress1</th>
      <td>$companyCity</td>
      <td>$companyState</td>
      <td>$companyZip</td>
      <td>$lastContacted</td>
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
            case 'update':
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
        switch ($this->viewType) {
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
        return $this->companyModels;
    }

    /**
     *
     * @param CompanyModel[] $companyModels
     */
    public function setCompanyModels($companyModels)
    {
        $this->companyModels = $companyModels;
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
            $cname = $company->getCompanyName();
            $selected = ($cid === $value) ? "selected=\"selected\"" : "";
            $retVal .= "  <option value=\"$cid\" $selected>$cname</option>\n";
        }
        $retVal .= "</select>\n";
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
            $aname = $agency->getCompanyName();
            $aid = $agency->getId();
            $retVal .= "  <option value=\"$aid\" $selected>$aname</option>\n";
        }
        $retVal .= "</select>\n";
        return $retVal;
    }
}
