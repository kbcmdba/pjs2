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

use com\kbcmdba\pjs2\Libs\Controllers\ApplicationStatusController;
use com\kbcmdba\pjs2\Libs\Controllers\CompanyController;
use com\kbcmdba\pjs2\Libs\Exceptions\ViewException;
use com\kbcmdba\pjs2\Libs\Models\JobModel;

/**
 * Job Summary View
 */
class JobSummaryView extends ListViewBase
{

    /** @var string */
    private $viewType;

    /** @var mixed */
    private $supportedViewTypes = [
        'html' => 1
    ];

    /** @var JobModel[] */
    private $jobModels;

    /** @var string */
    private $label;

    /**
     * Class constructor
     *
     * @param
     *            string View Type
     * @param JobModel[] $jobModels
     * @throws ViewException
     */
    public function __construct($viewType = 'html', $jobModels)
    {
        parent::__construct();
        if (! isset($this->supportedViewTypes[$viewType])) {
            throw new ViewException("Unsupported view type\n");
        }
        $this->viewType = $viewType;
        $this->setJobModels($jobModels);
    }

    /**
     *
     * @return string
     */
    private function _getHtmlView()
    {
        $jobModels = $this->getJobModels();
        $label = $this->getLabel();
        $count = count($jobModels);
        $output = "<table>\n" . "  <caption>$label ($count)</caption>\n" . "  <tr>\n" . "    <th>ID</th>\n" . "    <th>Status / Urgency</th>\n" . "    <th>Title</th>\n" . "    <th>Company</th>\n" . "    <th>URL</th>\n" . "    <th>Next Action / Due</th>\n" . "  </tr>\n";
        $appStatusController = new ApplicationStatusController('read');
        foreach ($jobModels as $jobModel) {
            $id = $jobModel->getId();
            $cid = $jobModel->getCompanyId();
            $companyController = new CompanyController();
            $companyModel = $companyController->get($cid);
            $cName = $companyModel->getCompanyName();
            $cCity = $companyModel->getCompanyCity();
            $cState = $companyModel->getCompanyState();
            $cUrl = $companyModel->getCompanyUrl();
            $jobTitle = $jobModel->getPositionTitle();
            $jobAppId = $jobModel->getApplicationStatusId();
            $jobStatus = $appStatusController->get($jobAppId)->getStatusValue();
            $jobNextAction = $jobModel->getNextAction();
            $jobNextActDue = $jobModel->getNextActionDue();
            $jobUrgency = $jobModel->getUrgency();
            $output .= "  <tr>\n" . "    <th>$id</th>\n" . "    <td>$jobStatus / $jobUrgency</td>\n" . "    <td>$jobTitle</td>\n" . "    <td>$cName ($cCity, $cState)</td>\n" . "    <td><a href=\"$cUrl\">Link</a></td>\n" . "    <td>$jobNextAction / $jobNextActDue</td>\n" . "  </tr>\n";
        }
        $output .= "</table>\n";
        return $output;
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
     * @return JobModel[]
     */
    public function getJobModels()
    {
        return $this->jobModels;
    }

    /**
     *
     * @param JobModel[] $jobModels
     */
    public function setJobModels($jobModels)
    {
        $this->jobModels = $jobModels;
    }

    /**
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
}
