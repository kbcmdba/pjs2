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
 * Search Form View
 *
 * Change search to your model's name
 * Change Search to your model's Name
 * Edit Search to your class description (used for a button)
 */
class SearchFormView extends FormViewBase {
    /** @var SearchModel */
    private $_searchModel ;

    /**
     * Class constructor
     *
     * @param string Page title and action button
     * @param SearchModel The populated model or null
     */
    public function __construct( $title = "Add Search", $searchModel = null ) {
        parent::__construct( $title ) ;
        if ( NULL !== $searchModel ) {
            $this->_searchModel = $searchModel ;
        }
        else {
            $this->_searchModel = new SearchModel ;
        }
    }

    /**
     * magic method
     * @return string
     */
    public function __toString() {
        return $this->getForm() ;
    }

    /**
     *
     * @return string
     */
    public function getForm( $readOnly = 'readwrite' ) {
        $RO              = ( 'readonly' === $readOnly ) ? 'READONLY="READONLY" ' : '' ;
        $searchModel     = $this->_searchModel ;
        $title           = $this->getTitle() ;
        $id              = $searchModel->getId() ;
        $engineName      = htmlspecialchars( $searchModel->getEngineName() ) ;
        $searchName      = htmlspecialchars( $searchModel->getSearchName() ) ;
        $url             = htmlspecialchars( $searchModel->getUrl() ) ;
        $created         = $searchModel->getCreated() ;
        $updated         = $searchModel->getUpdated() ;
        $buttonLabel     = $this->getButtonLabel() ;
        $returnValue     = <<<HTML
    <h2>$title</h2>
    <form method="GET">
      <table border="1" cellspacing="1" cellpadding="2">
        <tr>
          <th>ID</th>
          <td><input type="text" name="id" value="$id" readonly="readonly" /></td>
        </tr>
        <tr>
          <th>Engine Name</th>
          <td><input type="text" name="engineName" value="$engineName" $RO /></td>
        </tr>
        <tr>
          <th>Search Name</th>
          <td><input type="text" name="searchName" value="$searchName" $RO /></td>
        </tr>
        <tr>
          <th>URL</th>
          <td><input type="text" name="url" value="$url" $RO /></td>
        </tr>
        <tr>
          <th>Created</th>
          <td><input type="text" name="created" value="$created" disabled="disabled" /></td>
        </tr>
        <tr>
          <th>Updated</th>
          <td><input type="text" name="updated" value="$updated" disabled="disabled" /></td>
        </tr>
        <tr>
          <td colspan="2">
            <center>
              <input type="reset" /> &nbsp; &nbsp; <input type="submit" name="act" value="$buttonLabel" />
            </center>
          </td>
        </tr>
      </table>
    </form>
HTML;
        return $returnValue ;
    }

}