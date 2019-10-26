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

use com\kbcmdba\pjs2\Libs\Exceptions\ViewException;
use com\kbcmdba\pjs2\Libs\Models\NoteModel;

/**
 * Note List View
 */
class NoteListView extends ListViewBase
{

    /** @var string */
    private $noteAppliesToTable;

    /** @var integer */
    private $noteAppliesToId;

    /** @var string */
    private $viewType;

    /** @var mixed */
    private $supportedViewTypes = [
        'html' => 1
    ];

    /** @var NoteModel[] */
    private $noteModels;

    /**
     * Class constructor
     *
     * @param
     *            string View Type
     * @param NoteModel[] $noteModels
     * @throws ViewException
     */
    public function __construct($viewType = 'html', $noteModels)
    {
        parent::__construct();
        if (! isset($this->supportedViewTypes[$viewType])) {
            throw new ViewException("Unsupported view type\n");
        }
        $this->viewType = $viewType;
        $this->setNoteModels($noteModels);
    }

    /**
     * Return the HTML view
     *
     * @return string
     */
    private function _getHtmlView()
    {
        $noteAppliesToTable = $this->getNoteAppliesToTable();
        $noteAppliesToId = $this->getNoteAppliesToId();
        $body = <<<HTML
<a href="addNote.php?appliesToTable=$noteAppliesToTable&appliesToId=$noteAppliesToId">Add new $noteAppliesToTable note</a><br />
<table border="1" cellspacing="0" cellpadding="2">
  <caption>Current Notes</caption>
  <tr>
    <th>Actions</th>
    <th>ID</th>
    <th>Note</th>
    <th>Created</th>
    <th>Updated</th>
  </tr>
HTML;
        foreach ($this->getNoteModels() as $noteModel) {
            $id = $noteModel->getId();
            $created = $noteModel->getCreated();
            $updated = $noteModel->getUpdated();
            $noteText = htmlspecialchars($noteModel->getNoteText());
            $body .= <<<HTML
  <tr>
    <td>
        <a href="editNote.php?id=$id">Edit</a>
      | <a href="deleteNote.php?id=$id">Delete</a>
    </td>
    <td>$id</td>
    <td>$noteText</td>
    <td>$created</td>
    <td>$updated</td>
  </tr>
HTML;
        }
        $body .= "</table>\n";
        return $body;
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
     * @return string
     */
    public function getNoteAppliesToTable()
    {
        return $this->noteAppliesToTable;
    }

    /**
     *
     * @param string $noteAppliesToTable
     */
    public function setAppliesToTable($noteAppliesToTable)
    {
        $this->noteAppliesToTable = $noteAppliesToTable;
    }

    /**
     *
     * @return integer
     */
    public function getNoteAppliesToId()
    {
        return $this->noteAppliesToId;
    }

    /**
     *
     * @param string $noteAppliesToId
     */
    public function setAppliesToId($noteAppliesToId)
    {
        $this->noteAppliesToId = $noteAppliesToId;
    }

    /**
     *
     * @return NoteModel[]
     */
    public function getNoteModels()
    {
        return $this->noteModels;
    }

    /**
     *
     * @param NoteModel[] $noteModels
     */
    public function setNoteModels($noteModels)
    {
        $this->noteModels = $noteModels;
    }
}
