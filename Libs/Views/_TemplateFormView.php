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
 * _Template Form View
 *
 * Edit Template to your class description (used for a button)
 */
class _TemplateFormView extends FormViewBase {
    /** @var _TemplateModel */
    private $_model ;

    /**
     * Class constructor
     *
     * @param string Page title and action button
     * @param _TemplateModel The populated model or null
     */
    public function __construct( $title = "Add Template", $model = null ) {
        parent::__construct( $title ) ;
        if ( NULL !== $model ) {
            $this->_model = $model ;
        }
        else {
            $this->_model = new _TemplateModel ;
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
        $model           = $this->_model ;
        $title           = $this->getTitle() ;
        // @todo Get variables for view
        $buttonLabel     = $this->getButtonLabel() ;
        // @todo Show the form for this view
        $returnValue     = <<<HTML
    <h2>$title</h2>
    <form method="GET">
      <table border=1>
        <tr>
          <th>ColumnLabel</th>
          <td><input type="text name="someName" value="$someValue" $RO /></td>
        </tr>
        <tr>
          <th>DateColumnLabel</th>
          <td><input type="text" name="someDate" value="$someDate" class="datepicker" $RO/></td>
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
