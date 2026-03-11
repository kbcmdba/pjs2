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
 * Keyword List View
 */
class KeywordListView extends ListViewBase
{

    /** @var string */
    private $_viewType;

    /** @var mixed */
    private $_supportedViewTypes = [
        'html' => 1
    ];

    /** @var KeywordModel[] */
    private $_keywordModels;

    /**
     * Class constructor
     *
     * @param string $viewType
     * @param KeywordModel[] $keywordModels
     * @throws ViewException
     */
    public function __construct($viewType = 'html', $keywordModels = null)
    {
        parent::__construct();
        if (! isset($this->_supportedViewTypes[$viewType])) {
            throw new ViewException("Unsupported view type\n");
        }
        $this->_viewType = $viewType;
        $this->_keywordModels = $keywordModels;
    }

    /**
     * Return the HTML view
     *
     * @return string
     */
    private function _getHtmlView()
    {
        $body = <<<'HTML'
<button type="button" id="AddButton" onclick="addKeyword()">Add Keyword</button><br />
<table id="keywords">
  <caption>Current Keywords</caption>
  <thead>
    <tr>
      <th>Actions</th>
      <th>Keyword</th>
      <th>Sort Key</th>
      <th>Created</th>
      <th>Updated</th>
    </tr>
  </thead>
  <tbody>

HTML;
        foreach ($this->_keywordModels as $keywordModel) {
            $id = $keywordModel->getId();
            $row = $this->displayKeywordRow($keywordModel, 'list');
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
     * Return the display of a keyword table row
     *
     * @param KeywordModel $keywordModel
     * @param string $displayMode
     * @param string $errMessage
     * @return string
     */
    public function displayKeywordRow($keywordModel, $displayMode, $errMessage = '')
    {
        $id = $keywordModel->getId();
        $keywordValue = Tools::htmlOut($keywordModel->getKeywordValue());
        $sortKey = Tools::htmlOut($keywordModel->getSortKey());
        $created = Tools::htmlOut($keywordModel->getCreated());
        $updated = Tools::htmlOut($keywordModel->getUpdated());
        $errMessage = Tools::htmlOut($errMessage);
        switch ($displayMode) {
            case 'add':
                return <<<HTML
      <td><button type="button" id="SaveButtonix$id" onclick="saveAddKeyword( '$id' )">Save</button>
          <button type="button" id="CancelButtonix$id" onclick="deleteRow( 'ix$id' )">Cancel</button>
          $errMessage
      </td>
      <td><input type="text" id="keywordValueix$id" value="$keywordValue" /></td>
      <td><input type="text" id="sortKeyix$id" value="$sortKey" size="5" /></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>

HTML;
            case 'update':
                return <<<HTML
      <td><button type="button" id="SaveButton$id" onclick="saveUpdateKeyword( '$id' )">Save</button>
          <button type="button" id="CancelButton$id" onclick="cancelUpdateKeywordRow( '$id' )">Cancel</button>
          $errMessage
      </td>
      <td><input type="text" id="keywordValue$id" value="$keywordValue" /></td>
      <td><input type="text" id="sortKey$id" value="$sortKey" size="5" /></td>
      <td>$created</td>
      <td>$updated</td>

HTML;
            case 'delete':
                return <<<HTML
      <td><button type="button" id="DeleteButton$id" onclick="doDeleteKeyword( '$id' )">Confirm Delete</button>
          <button type="button" id="CancelButton$id" onclick="cancelUpdateKeywordRow( '$id' )">Cancel</button>
          $errMessage
      </td>
      <td>$keywordValue</td>
      <td>$sortKey</td>
      <td>$created</td>
      <td>$updated</td>

HTML;
            case 'list':
                $click = "onclick=\"updateKeyword( '$id' )\" style=\"cursor: pointer;\"";
                return <<<HTML
      <td><button type="button" id="UpdateButton$id" onclick="updateKeyword( '$id' )">Update</button>
          <button type="button" id="DeleteButton$id" onclick="deleteKeyword( '$id' )">Delete</button>
          $errMessage
      </td>
      <td $click>$keywordValue</td>
      <td $click>$sortKey</td>
      <td $click>$created</td>
      <td $click>$updated</td>

HTML;
            default:
                throw new ViewException('Invalid display mode.');
        }
        // Should never get here.
    }

    /**
     * @return KeywordModel[]
     */
    public function getKeywordModels()
    {
        return $this->_keywordModels;
    }

    /**
     * @param KeywordModel[] $keywordModels
     */
    public function setKeywordModels($keywordModels)
    {
        $this->_keywordModels = $keywordModels;
    }
}
