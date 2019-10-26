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
namespace com\kbcmdba\pjs2\Libs\Models;

use com\kbcmdba\pjs2\Libs\Exceptions\ModelException;
use com\kbcmdba\pjs2\Libs\Tools;

/**
 * Template Model
 */
class NoteModel extends ModelBase
{
    private $id;
    private $appliesToTable;
    private $appliesToId;
    private $created;
    private $updated;
    private $noteText;
    private static $appliesToTables = [
        'job' => 1,
        'company' => 1,
        'contact' => 1,
        'keyword' => 1,
        'search' => 1
    ];

    /**
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Validate model for insert
     *
     * @return boolean
     */
    public function validateForAdd()
    {
        return ((Tools::isNullOrEmptyString(Tools::param('id'))) && (! Tools::isNullOrEmptyString(Tools::param('appliesToTable'))) && ((1 === self::$appliesToTables[Tools::param('appliesToTable')])) && (! Tools::isNullOrEmptyString(Tools::param('appliesToId'))) && (Tools::isNumeric(Tools::param('appliesToId'))) && (! Tools::isNullOrEmptyString(Tools::param('noteText'))));
    }

    /**
     * Validate model for update
     *
     * @return boolean
     */
    public function validateForUpdate()
    {
        return ((! Tools::isNullOrEmptyString(Tools::param('id'))) && (Tools::isNumeric(Tools::param('id'))) && (! Tools::isNullOrEmptyString(Tools::param('appliesToTable'))) && (('job' === Tools::param('appliesToTable')) || ('company' === Tools::param('appliesToTable')) || ('contact' === Tools::param('appliesToTable')) || ('keyword' === Tools::param('appliesToTable')) || ('search' === Tools::param('appliesToTable'))) && (! Tools::isNullOrEmptyString(Tools::param('appliesToId'))) && (Tools::isNumeric(Tools::param('appliesToId'))) && (! Tools::isNullOrEmptyString(Tools::param('noteText'))));
    }

    public function populateFromForm()
    {
        $this->setId(Tools::param('id'));
        $this->setAppliesToTable(Tools::param('appliesToTable'));
        $this->setAppliesToId(Tools::param('appliesToId'));
        $this->setCreated(Tools::param('created'));
        $this->setUpdated(Tools::param('updated'));
        $this->setNoteText(Tools::param('noteText'));
    }

    /**
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     *
     * @return string
     */
    public function getAppliesToTable()
    {
        return $this->appliesToTable;
    }

    /**
     *
     * @param string $appliesToTable
     */
    public function setAppliesToTable($appliesToTable)
    {
        if (('job' === $appliesToTable) || ('company' === $appliesToTable) || ('contact' === $appliesToTable) || ('keyword' === $appliesToTable) || ('search' === $appliesToTable)) {
            $this->appliesToTable = $appliesToTable;
        } else {
            throw new ModelException("Invalid appliesToTable: $appliesToTable");
        }
    }

    /**
     *
     * @return integer
     */
    public function getAppliesToId()
    {
        return $this->appliesToId;
    }

    /**
     *
     * @param integer $appliesToId
     */
    public function setAppliesToId($appliesToId)
    {
        $this->appliesToId = $appliesToId;
    }

    /**
     *
     * @return string
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     *
     * @param string $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     *
     * @return string
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     *
     * @param string $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     *
     * @return string
     */
    public function getNoteText()
    {
        return $this->noteText;
    }

    /**
     *
     * @param string $noteText
     */
    public function setNoteText($noteText)
    {
        $this->noteText = $noteText;
    }
}
