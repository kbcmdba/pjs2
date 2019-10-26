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

use com\kbcmdba\pjs2\Libs\Tools;

/**
 * Version Model
 */
class VersionModel extends ModelBase
{
    private $versionValue;

    private $updated;

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
     * @todo Implement VersionModel::validateForAdd()
     */
    public function validateForAdd()
    {
        return 0;
    }

    /**
     * Validate model for update
     *
     * @return boolean
     * @todo Implement VersionModel::validateForUpdate()
     */
    public function validateForUpdate()
    {
        return 0;
    }

    public function populateFromForm()
    {
        $this->setVersionValue(Tools::param('versionValue'));
        $this->setUpdated(Tools::param('updated'));
    }

    /**
     *
     * @return string
     */
    public function getVersionValue()
    {
        return $this->versionValue;
    }

    /**
     *
     * @param string $versionValue
     */
    public function setVersionValue($versionValue)
    {
        $this->versionValue = $versionValue;
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
}
