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

class SearchStatusModel extends ModelBase
{

    private $_id;

    private $_statusValue;

    private $_isActive;

    private $_sortKey;

    private $_style;

    private $_created;

    private $_updated;

    public function __construct()
    {
        parent::__construct();
    }

    public function validateForAdd()
    {
        return (Tools::isNullOrEmptyString(Tools::param('id')) && ! Tools::isNullOrEmptyString(Tools::param('statusValue')));
    }

    public function validateForUpdate()
    {
        return (! Tools::isNullOrEmptyString(Tools::param('id')) && ! Tools::isNullOrEmptyString(Tools::param('statusValue')));
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getStatusValue()
    {
        return $this->_statusValue;
    }

    public function setStatusValue($statusValue)
    {
        $this->_statusValue = $statusValue;
    }

    public function getIsActive()
    {
        return $this->_isActive;
    }

    public function setIsActive($isActive)
    {
        $this->_isActive = $isActive;
    }

    public function getSortKey()
    {
        return $this->_sortKey;
    }

    public function setSortKey($sortKey)
    {
        $this->_sortKey = $sortKey;
    }

    public function getStyle()
    {
        return $this->_style;
    }

    public function setStyle($style)
    {
        $this->_style = $style;
    }

    public function getCreated()
    {
        return $this->_created;
    }

    public function setCreated($created)
    {
        $this->_created = $created;
    }

    public function getUpdated()
    {
        return $this->_updated;
    }

    public function setUpdated($updated)
    {
        $this->_updated = $updated;
    }
}
