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

/**
 * Search List View
 */

class SearchListView extends ListViewBase {

    /** @var string */
    private $_viewType ;
    /** @var mixed */
    private $_supportedViewTypes = array( 'html' => 1 ) ;
    /** @var SearchModel[] */
    private $searchModels ;

    /**
     * Class constructor
     *
     * @param string View Type
     * @param SearchModel[] $searchModels
     * @throws ViewException
     */
    public function __construct( $viewType = 'html', $searchModels ) {
        parent::__construct() ;
        if ( ! isset( $this->_supportedViewTypes[ $viewType ] ) ) {
            throw new ViewException( "Unsupported view type\n" ) ;
        }
        $this->_viewType = $viewType ;
        $this->setSearchModels( $searchModels ) ;
    }

    /**
     * Return the HTML view
     *
     * @return string
     */
    private function _getHtmlView() {
        $body = <<<'HTML'
<button type="button" id="AddButton" onclick="addSearch()" >Add Search</button>
<table border="1" cellspacing="0" cellpadding="2" id="search">
  <caption>Current Searches</caption>
  <thead>
    <tr>
      <th>Actions</th>
      <th>Engine</th>
      <th>Search Name</th>
      <th>Link</th>
      <th>Feed</th>
      <th>Feed Last Checked</th>
      <th>Created</th>
      <th>Updated</th>
    </tr>
  </thead>
  <tbody>

HTML;
        foreach ( $this->getSearchModels() as $search ) {
            $id    = $search->getId() ;
            $row   = $this->displaySearchRow( $search, 'list' ) ;
            $body .= <<<HTML
    <tr id="ux$id">
      $row
    </tr>

HTML;
        }
        $body .= "  </tbody>\n</table>\n" ;
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
     * Return the display of a search table row
     *
     * @param SearchModel $searchModel
     * @param string $displayMode 'add', 'edit', 'delete', 'list'
     * @param string $errMessage
     * @return string
     */
    public function displaySearchRow( $searchModel, $displayMode, $errMessage = '' ) {
        $id             = $searchModel->getId() ;
        $engineName     = $searchModel->getEngineName() ;
        $searchName     = $searchModel->getSearchName() ;
        $url            = $searchModel->getUrl() ;
        $rssFeedUrl     = $searchModel->getRssFeedUrl() ;
        $rssLastChecked = $searchModel->getRssLastChecked() ;
        $created        = $searchModel->getCreated() ;
        $updated        = $searchModel->getUpdated() ;
        switch ( $displayMode ) {
            case 'add' :
                return <<<HTML
      <td>
        <button id="SaveButtonix$id" onclick="doAddSearch( '$id' )">Save</a>
        <button id="CancelButtonix$id" onclick="deleteRow( 'ix$id' )">Cancel</a>
        $errMessage
      </td>
      <td><input type="text" id="engineNameix$id" value="$engineName" /></td>
      <td><input type="text" id="searchNameix$id" value="$searchName" /></td>
      <td><input type="text" id="urlix$id" value="$url" /></td>
      <td><input type="text" id="rssFeedUrlix$id" value="$rssFeedUrl" /></td>
      <td><input type="text" id="rssLastCheckedix$id" value="$rssLastChecked" class="datepicker" /></td>
      <td>$created</td>
      <td>$updated</td>

HTML;
                break ;
            case 'update' :
                return <<<HTML
      <td>
        <button id="SaveButton$id" onclick="doUpdateSearch( '$id' )">Save</a>
        <button id="CancelButton$id" onclick="cancelUpdateSearch( '$id' )">Cancel</a>
        $errMessage
      </td>
      <td><input type="text" id="engineName$id" value="$engineName" /></td>
      <td><input type="text" id="searchName$id" value="$searchName" /></td>
      <td><input type="text" id="url$id" value="$url" /></td>
      <td><input type="text" id="rssFeedUrl$id" value="$rssFeedUrl" /></td>
      <td><input type="text" id="rssLastChecked$id" value="$rssLastChecked" class="datepicker" /></td>
      <td>$created</td>
      <td>$updated</td>

HTML;
                break ;
            case 'delete' :
                return <<<HTML
      <td>
        <button id="DeleteButton$id" onclick="doDeleteSearch( '$id' )">Confirm Delete</a>
        <button id="CancelButton$id" onclick="cancelUpdateSearch( '$id' )">Cancel</a>
        $errMessage
      </td>
      <td>$engineName</td>
      <td>$searchName</td>
      <td><a href="$url">$url</a></td>
      <td><a href="$rssFeedUrl">$rssFeedUrl</a></td>
      <td>$rssLastChecked</td>
      <td>$created</td>
      <td>$updated</td>

HTML;
                break ;
            case 'list' :
                return <<<HTML
      <td>
        <button id="UpdateButton$id" onclick="updateSearch( '$id' )">Update</a>
        <button id="DeleteButton$id" onclick="deleteSearch( '$id' )">Delete</a>
        $errMessage
      </td>
      <td>$engineName</td>
      <td>$searchName</td>
      <td><a href="$url">$url</a></td>
      <td><a href="$rssFeedUrl">$rssFeedUrl</a></td>
      <td>$rssLastChecked</td>
      <td>$created</td>
      <td>$updated</td>
                
HTML;
                break ;
            default :
                throw new ViewException( 'Invalid display mode.' ) ;
                break ;
        }
        // Should never get here.
    }

    /**
     * @return SearchModel[]
     */
    public function getSearchModels() {
        return $this->_searchModels ;
    }

    /**
     * @param SearchModel[] $searchModels
     */
    public function setSearchModels( $searchModels ) {
        $this->_searchModels = $searchModels ;
    }

}
