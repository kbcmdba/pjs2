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

use com\kbcmdba\pjs2\Libs\Controllers\ApplicationStatusController;
use com\kbcmdba\pjs2\Libs\Tools;

/**
 * Job Model
 */
class JobModel extends ModelBase
{
    private $id;
    private $primaryContactId;
    private $companyId;
    private $applicationStatusId;
    private $isActiveSummary;
    private $lastStatusChange;
    private $urgency;
    private $created;
    private $updated;
    private $nextActionDue;
    private $nextAction;
    private $positionTitle;
    private $location;
    private $url;

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
        return ((Tools::isNullOrEmptyString(Tools::param('id'))) && (! Tools::isNullOrEmptyString(Tools::param('companyId'))) && (! Tools::isNullOrEmptyString(Tools::param('applicationStatusId'))) && (! Tools::isNullOrEmptyString(Tools::param('urgency'))) && (! Tools::isNullOrEmptyString(Tools::param('positionTitle'))) && (! Tools::isNullOrEmptyString(Tools::param('location'))));
    }

    /**
     * Validate model for update
     *
     * @return boolean
     */
    public function validateForUpdate()
    {
        return ((! Tools::isNullOrEmptyString(Tools::param('id'))) && (! Tools::isNullOrEmptyString(Tools::param('contactId'))) && (! Tools::isNullOrEmptyString(Tools::param('companyId'))) && (! Tools::isNullOrEmptyString(Tools::param('applicationStatusId'))) && (! Tools::isNullOrEmptyString(Tools::param('urgency'))) && (! Tools::isNullOrEmptyString(Tools::param('positionTitle'))) && (! Tools::isNullOrEmptyString(Tools::param('location'))));
    }

    public function populateFromForm()
    {
        $this->setId(Tools::param('id'));
        $this->setPrimaryContactId(Tools::param('contactId'));
        $this->setCompanyId(Tools::param('companyId'));
        $this->setApplicationStatusId(Tools::param('applicationStatusId'));
        $this->setLastStatusChange(Tools::param('lastStatusChange'));
        $this->setUrgency(Tools::param('urgency'));
        $this->setCreated(Tools::param('created'));
        $this->setUpdated(Tools::param('updated'));
        $this->setNextActionDue(Tools::param('nextActionDue'));
        $this->setNextAction(Tools::param('nextAction'));
        $this->setPositionTitle(Tools::param('positionTitle'));
        $this->setLocation(Tools::param('location'));
        $this->setUrl(Tools::param('url'));
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
     * @return integer
     */
    public function getPrimaryContactId()
    {
        return $this->primaryContactId;
    }

    /**
     *
     * @param integer $primaryContactId
     */
    public function setPrimaryContactId($primaryContactId)
    {
        $this->primaryContactId = $primaryContactId;
    }

    /**
     *
     * @return integer
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     *
     * @param integer $companyId
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     *
     * @return integer
     */
    public function getApplicationStatusId()
    {
        return $this->applicationStatusId;
    }

    /**
     *
     * @param integer $applicationStatusId
     */
    public function setApplicationStatusId($applicationStatusId)
    {
        $this->applicationStatusId = $applicationStatusId;
        $applicationStatusController = new ApplicationStatusController('read');
        $applicationStatusModel = $applicationStatusController->get($applicationStatusId);
        $this->_setIsActiveSummary($applicationStatusModel->getIsActive());
    }

    /**
     *
     * @param boolean $isActive
     */
    private function _setIsActiveSummary($isActive)
    {
        $this->isActiveSummary = $isActive;
    }

    /**
     *
     * @return boolean
     */
    public function getIsActiveSummary()
    {
        return $this->isActiveSummary;
    }

    /**
     *
     * @return string
     */
    public function getLastStatusChange()
    {
        if ("0000-00-00 00:00:00" === $this->lastStatusChange) {
            return "";
        } else {
            return $this->lastStatusChange;
        }
    }

    /**
     *
     * @param string $lastStatusChange
     */
    public function setLastStatusChange($lastStatusChange)
    {
        $this->lastStatusChange = $lastStatusChange;
    }

    /**
     *
     * @return string
     */
    public function getUrgency()
    {
        return $this->urgency;
    }

    /**
     *
     * @param string $urgency
     */
    public function setUrgency($urgency)
    {
        $this->urgency = $urgency;
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
    public function getNextActionDue()
    {
        if ((! isset($this->nextActionDue)) || ($this->nextActionDue === '0000-00-00 00:00:00')) {
            return '';
        }
        return $this->nextActionDue;
    }

    /**
     *
     * @param string $nextActionDue
     */
    public function setNextActionDue($nextActionDue)
    {
        if ($nextActionDue === '') {
            $nextActionDue = null;
        }
        $this->nextActionDue = $nextActionDue;
    }

    /**
     *
     * @return string
     */
    public function getNextAction()
    {
        if (! isset($this->nextAction)) {
            return '';
        }
        return $this->nextAction;
    }

    /**
     *
     * @param string $nextAction
     */
    public function setNextAction($nextAction)
    {
        if (! isset($nextAction)) {
            $nextAction = '';
        }
        $this->nextAction = $nextAction;
    }

    /**
     *
     * @return string
     */
    public function getPositionTitle()
    {
        return $this->positionTitle;
    }

    /**
     *
     * @param string $positionTitle
     */
    public function setPositionTitle($positionTitle)
    {
        $this->positionTitle = $positionTitle;
    }

    /**
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     *
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
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
}
