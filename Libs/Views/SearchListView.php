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
<a href="addSearch.php">Add new search</a><br />
<table border="1" cellspacing="0" cellpadding="2">
  <caption>Current Searches</caption>
  <tr><th>Actions</th><th>Engine</th><th>Search Name</th><th>Link</th></tr>
HTML;
        foreach ( $this->getSearchModels() as $search ) {
            $id          = $search->getId() ;
            $engineName  = htmlspecialchars( $search->getEngineName() ) ;
            $searchName  = htmlspecialchars( $search->getSearchName() ) ;
            $url         = htmlspecialchars( $search->getUrl() ) ;
            $body       .= <<<HTML
  <tr>
    <td>
        <a href="editSearch.php?id=$id">Edit</a>
      | <a href="deleteSearch.php?id=$id">Delete</a>
    </td>
    <td>$engineName</td>
    <td>$searchName</td>
    <td><a href="$url">$url</a></td>
  </tr>
HTML;
        }
        $body .= "</table>\n" ;
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
