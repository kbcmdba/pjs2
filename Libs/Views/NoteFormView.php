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
 * Note Form View
 */
class NoteFormView extends FormViewBase {
    /** @var NoteModel */
    private $_noteModel ;

    /**
     * Class constructor
     *
     * @param string Page title and action button
     * @param NoteModel The populated model or null
     */
    public function __construct( $title = "Add Note", $noteModel = null ) {
        parent::__construct( $title ) ;
        if ( NULL !== $noteModel ) {
            $this->_noteModel = $noteModel ;
        }
        else {
            $this->_noteModel = new NoteModel ;
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
        $noteModel       = $this->_noteModel ;
        $title           = $this->getTitle() ;
        $id              = $noteModel->getId() ;
        $appliesToTable  = $noteModel->getAppliesToTable() ;
        $appliesToId     = $noteModel->getAppliesToId() ;
        $noteText        = htmlspecialchars( $noteModel->getNoteText() ) ;
        $buttonLabel     = $this->getButtonLabel() ;
        $returnValue     = <<<HTML
    <h2>$title</h2>
    <form name="note" onsubmit="return validateNote()" method="GET">
      <table border="1" cellspacing="1" cellpadding="2">
        <tr>
          <th>ID</th>
          <td><input type="text" name="id" value="$id" readonly="readonly" /></td>
        </tr>
        <tr>
          <th>Applies To Table</th>
          <td><input type="text" name="appliesToTable" value="$appliesToTable" readonly="readonly" /></td>
        </tr>
        <tr>
          <th>Applies To ID</th>
          <td><input type="text" name="appliesToId" value="$appliesToId" readonly="readonly" /></td>
        </tr>
        <tr>
          <th>Note Text</th>
          <td><textarea name="noteText" $RO>$noteText</textarea></td>
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
