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
namespace com\kbcmdba\pjs2;

/**
 * ApplicationStatus Model
 */
class ApplicationStatusModel extends ModelBase
{

    private $_id;

    private $_statusValue;

    private $_isActive;

    private $_sortKey;

    private $_style;

    private $_summaryCount;

    private $_created;

    private $_updated;

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
        return ((Tools::isNullOrEmptyString(Tools::param('id'))) && (! Tools::isNullOrEmptyString(Tools::param('statusValue'))) && (! Tools::isNullOrEmptyString(Tools::param('sortKey'))) && (Tools::isNullOrEmptyString(Tools::param('summaryCount'))));
    }

    /**
     * Validate model for update
     *
     * @return boolean
     */
    public function validateForUpdate()
    {
        return ((! Tools::isNullOrEmptyString(Tools::param('id'))) && (! Tools::isNullOrEmptyString(Tools::param('statusValue'))) && (! Tools::isNullOrEmptyString(Tools::param('sortKey'))) && (Tools::isNullOrEmptyString(Tools::param('summaryCount'))));
    }

    public function populateFromForm()
    {
        $this->setId(Tools::param('id'));
        $this->setStatusValue(Tools::param('statusValue'));
        $this->setIsActive(Tools::param('isActive'));
        $this->setSortKey(Tools::param('sortKey'));
        $this->setStyle(Tools::param('style'));
        $this->setSummaryCount(Tools::param('summaryCount'));
        $this->setCreated(Tools::param('created'));
        $this->setUpdated(Tools::param('updated'));
    }

    /**
     *
     * @return integer
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     *
     * @return string
     */
    public function getStatusValue()
    {
        return $this->_statusValue;
    }

    /**
     *
     * @param string $statusValue
     */
    public function setStatusValue($statusValue)
    {
        $this->_statusValue = $statusValue;
    }

    /**
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->_isActive;
    }

    /**
     *
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->_isActive = $isActive;
    }

    /**
     *
     * @return integer
     */
    public function getSortKey()
    {
        return $this->_sortKey;
    }

    /**
     *
     * @param integer $sortKey
     */
    public function setSortKey($sortKey)
    {
        $this->_sortKey = $sortKey;
    }

    /**
     *
     * @return string
     */
    public function getStyle()
    {
        return $this->_style;
    }

    /**
     *
     * @param string $style
     */
    public function setStyle($style)
    {
        $this->_style = $style;
    }

    /**
     *
     * @return int
     */
    public function getSummaryCount()
    {
        return $this->_summaryCount;
    }

    /**
     *
     * @param int $summaryCount
     */
    public function setSummaryCount($summaryCount)
    {
        $this->_summaryCount = $summaryCount;
    }

    /**
     *
     * @return string
     */
    public function getCreated()
    {
        return $this->_created;
    }

    /**
     *
     * @param string $created
     */
    public function setCreated($created)
    {
        $this->_created = $created;
    }

    /**
     *
     * @return string
     */
    public function getUpdated()
    {
        return $this->_updated;
    }

    /**
     *
     * @param string $updated
     */
    public function setUpdated($updated)
    {
        $this->_updated = $updated;
    }
}
