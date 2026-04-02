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
 * Search List View
 */
class SearchListView extends ListViewBase
{

    /** @var string */
    private $_viewType;

    /** @var mixed */
    private $_supportedViewTypes = [
        'html' => 1
    ];

    /** @var SearchModel[] */
    private $searchModels;

    /**
     * Class constructor
     *
     * @param
     *            string View Type
     * @param SearchModel[] $searchModels
     * @throws ViewException
     */
    public function __construct($viewType = 'html', $searchModels)
    {
        parent::__construct();
        if (! isset($this->_supportedViewTypes[$viewType])) {
            throw new ViewException("Unsupported view type\n");
        }
        $this->_viewType = $viewType;
        $this->setSearchModels($searchModels);
    }

    /**
     * Return the HTML view
     *
     * @return string
     */
    private function _getHtmlView()
    {
        $body = <<<'HTML'
<button type="button" id="AddButton" onclick="addSearch()" >Add Search</button>
<table id="search">
  <caption>Current Searches</caption>
  <thead>
    <tr>
      <th>Actions</th>
      <th style="cursor: pointer;" onclick="sortSearchTable(1)" id="searchSortCol1">Engine <span class="sortIndicator">&loz;</span></th>
      <th style="cursor: pointer;" onclick="sortSearchTable(2)" id="searchSortCol2">Search Name <span class="sortIndicator">&loz;</span></th>
      <th style="cursor: pointer;" onclick="sortSearchTable(3)" id="searchSortCol3">Urgency <span class="sortIndicator">&loz;</span></th>
      <th style="cursor: pointer;" onclick="sortSearchTable(4)" id="searchSortCol4">Status <span class="sortIndicator">&loz;</span></th>
      <th>Link</th>
      <th>RSS<br>Feed</th>
      <th style="cursor: pointer;" onclick="sortSearchTable(7)" id="searchSortCol7">Feed Last<br>Checked <span class="sortIndicator">&loz;</span></th>
      <th>Created</th>
      <th style="cursor: pointer;" onclick="sortSearchTable(9)" id="searchSortCol9">Updated <span class="sortIndicator">&loz;</span></th>
    </tr>
  </thead>
  <tbody>

HTML;
        foreach ($this->getSearchModels() as $search) {
            $id = $search->getId();
            $row = $this->displaySearchRow($search, 'list');
            $body .= <<<HTML
    <tr id="ux$id">
      $row
    </tr>

HTML;
        }
        $body .= "  </tbody>\n</table>\n";
        return $body;
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
     * Return the display of a search table row
     *
     * @param SearchModel $searchModel
     * @param string $displayMode
     *            'add', 'edit', 'delete', 'list'
     * @param string $errMessage
     * @return string
     */
    public function displaySearchRow($searchModel, $displayMode, $errMessage = '')
    {
        $id = $searchModel->getId();
        $engineName = $searchModel->getEngineName();
        $searchName = $searchModel->getSearchName();
        $url = $searchModel->getUrl();
        $rssFeedUrl = $searchModel->getRssFeedUrl();
        $searchStatusId = $searchModel->getSearchStatusId();
        $urgency = $searchModel->getUrgency() ?: 'medium';
        $urgencyStyles = [
            'high' => 'background-color: hotpink;',
            'medium' => 'background-color: lightgreen;',
            'low' => 'background-color: cyan;'
        ];
        $urgencyStyle = isset($urgencyStyles[$urgency]) ? $urgencyStyles[$urgency] : '';
        $rssLastChecked = $searchModel->getRssLastChecked();
        $created = $searchModel->getCreated();
        $updated = $searchModel->getUpdated();
        $engineName = Tools::htmlOut($engineName);
        $searchName = Tools::htmlOut($searchName);
        $url = Tools::htmlOut($url);
        $rssFeedUrl = Tools::htmlOut($rssFeedUrl);
        $rssLastChecked = Tools::htmlOut($rssLastChecked);
        $created = Tools::htmlOut($created);
        $updated = Tools::htmlOut($updated);
        $statusValue = '---';
        $statusStyle = '';
        if ($searchStatusId >= 1) {
            $searchStatusController = new SearchStatusController('read');
            $searchStatusModel = $searchStatusController->get($searchStatusId);
            if ($searchStatusModel) {
                $statusValue = $searchStatusModel->getStatusValue();
                $statusStyle = $searchStatusModel->getStyle();
            }
        }
        $statusValue = Tools::htmlOut($statusValue);
        $selHigh = ($urgency === 'high') ? ' selected="selected"' : '';
        $selMed = ($urgency === 'medium') ? ' selected="selected"' : '';
        $selLow = ($urgency === 'low') ? ' selected="selected"' : '';
        $safeUrl = Tools::safeUrl($searchModel->getUrl());
        $safeRssFeedUrl = Tools::safeUrl($searchModel->getRssFeedUrl());
        $errMessage = Tools::htmlOut($errMessage);
        switch ($displayMode) {
            case 'add':
                return <<<HTML
      <td>
        <button id="SaveButtonix$id" onclick="doAddSearch( '$id' )">Save</a>
        <button id="CancelButtonix$id" onclick="deleteRow( 'ix$id' )">Cancel</a>
        $errMessage
      </td>
      <td><input type="text" id="engineNameix$id" value="$engineName" /></td>
      <td><input type="text" id="searchNameix$id" value="$searchName" /></td>
      <td><select id="urgencyix$id"><option value="high">high</option><option value="medium" selected="selected">medium</option><option value="low">low</option></select></td>
      <td style="$statusStyle">$statusValue</td>
      <td><input type="text" id="urlix$id" value="$url" /></td>
      <td><input type="text" id="rssFeedUrlix$id" value="$rssFeedUrl" /></td>
      <td><input type="text" id="rssLastCheckedix$id" value="$rssLastChecked" class="datepicker" /></td>
      <td>$created</td>
      <td>$updated</td>

HTML;
                break;
            case 'update':
                return <<<HTML
      <td>
        <button id="SaveButton$id" onclick="doUpdateSearch( '$id' )">Save</a>
        <button id="CancelButton$id" onclick="cancelUpdateSearch( '$id' )">Cancel</a>
        $errMessage
      </td>
      <td><input type="text" id="engineName$id" value="$engineName" /></td>
      <td><input type="text" id="searchName$id" value="$searchName" /></td>
      <td><select id="urgency$id"><option value="high"$selHigh>high</option><option value="medium"$selMed>medium</option><option value="low"$selLow>low</option></select></td>
      <td style="$statusStyle">$statusValue</td>
      <td><input type="text" id="url$id" value="$url" /></td>
      <td><input type="text" id="rssFeedUrl$id" value="$rssFeedUrl" /></td>
      <td><input type="text" id="rssLastChecked$id" value="$rssLastChecked" class="datepicker" /></td>
      <td>$created</td>
      <td>$updated</td>

HTML;
                break;
            case 'delete':
                return <<<HTML
      <td>
        <button id="DeleteButton$id" onclick="doDeleteSearch( '$id' )">Confirm Delete</a>
        <button id="CancelButton$id" onclick="cancelUpdateSearch( '$id' )">Cancel</a>
        $errMessage
      </td>
      <td>$engineName</td>
      <td>$searchName</td>
      <td style="$urgencyStyle">$urgency</td>
      <td style="$statusStyle">$statusValue</td>
      <td><a href="$safeUrl">$url</a></td>
      <td><a href="$safeRssFeedUrl">$rssFeedUrl</a></td>
      <td>$rssLastChecked</td>
      <td>$created</td>
      <td>$updated</td>

HTML;
                break;
            case 'list':
                $click = "onclick=\"updateSearch( '$id' )\" style=\"cursor: pointer;\"";
                return <<<HTML
      <td>
        <button id="UpdateButton$id" onclick="updateSearch( '$id' )">Update</a>
        <button id="DeleteButton$id" onclick="deleteSearch( '$id' )">Delete</a>
        $errMessage
      </td>
      <td $click>$engineName</td>
      <td $click>$searchName</td>
      <td style="$urgencyStyle" $click>$urgency</td>
      <td style="$statusStyle" $click>$statusValue</td>
      <td><a href="#" onclick="reviewSearch( '$id', '$safeUrl' ); return false;">Review</a> | <a href="$safeUrl" target="_blank">New Tab</a></td>
      <td><a href="$safeRssFeedUrl">$rssFeedUrl</a></td>
      <td $click>$rssLastChecked</td>
      <td>$created</td>
      <td>$updated</td>

HTML;
                break;
            default:
                throw new ViewException('Invalid display mode.');
                break;
        }
        // Should never get here.
    }

    /**
     *
     * @return SearchModel[]
     */
    public function getSearchModels()
    {
        return $this->_searchModels;
    }

    /**
     *
     * @param SearchModel[] $searchModels
     */
    public function setSearchModels($searchModels)
    {
        $this->_searchModels = $searchModels;
    }
}
