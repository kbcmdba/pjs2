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
 * Search Model
 */
class SearchModel extends ModelBase
{
    private $id;
    private $engineName;
    private $searchName;
    private $url;
    private $rssFeedUrl;
    private $rssLastChecked;
    private $created;
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
     */
    public function validateForAdd()
    {
        return (Tools::isNullOrEmptyString(Tools::param('id')) && ! Tools::isNullOrEmptyString(Tools::param('engineName')) && ! Tools::isNullOrEmptyString(Tools::param('searchName')) && ! Tools::isNullOrEmptyString(Tools::param('url')));
    }

    /**
     * Validate model for update
     *
     * @return boolean
     */
    public function validateForUpdate()
    {
        return (! Tools::isNullOrEmptyString(Tools::param('id')) && ! Tools::isNullOrEmptyString(Tools::param('engineName')) && ! Tools::isNullOrEmptyString(Tools::param('searchName')) && ! Tools::isNullOrEmptyString(Tools::param('url')));
    }

    public function populateFromForm()
    {
        $this->setId(Tools::param('id'));
        $this->setEngineName(Tools::param('engineName'));
        $this->setSearchName(Tools::param('searchName'));
        $this->setUrl(Tools::param('url'));
        $this->setCreated(Tools::param('created'));
        $this->setUpdated(Tools::param('updated'));
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
    public function getEngineName()
    {
        return $this->engineName;
    }

    /**
     *
     * @param string $engineName
     */
    public function setEngineName($engineName)
    {
        $this->engineName = $engineName;
    }

    /**
     *
     * @return string
     */
    public function getSearchName()
    {
        return $this->searchName;
    }

    /**
     *
     * @param string $searchName
     */
    public function setSearchName($searchName)
    {
        $this->searchName = $searchName;
    }

    /**
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     *
     * @return string
     */
    public function getRssFeedUrl()
    {
        return $this->rssFeedUrl;
    }

    /**
     *
     * @param string $rssFeedUrl
     */
    public function setRssFeedUrl($rssFeedUrl)
    {
        $this->rssFeedUrl = $rssFeedUrl;
    }

    /**
     *
     * @return string
     */
    public function getRssLastChecked()
    {
        return $this->rssLastChecked;
    }

    /**
     *
     * @param string $rssLastChecked
     */
    public function setRssLastChecked($rssLastChecked)
    {
        $this->rssLastChecked = $rssLastChecked;
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
}
