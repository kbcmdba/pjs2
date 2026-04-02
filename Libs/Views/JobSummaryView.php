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
 * Job Summary View
 */
class JobSummaryView extends ListViewBase
{

    /** @var string */
    private $_viewType;

    /** @var mixed */
    private $_supportedViewTypes = [
        'html' => 1
    ];

    /** @var JobModel[] */
    private $_jobModels;

    /** @var string */
    private $_label;

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
        if (! isset($this->_supportedViewTypes[$viewType])) {
            throw new ViewException("Unsupported view type\n");
        }
        $this->_viewType = $viewType;
        $this->setJobModels($jobModels);
    }

    /**
     *
     * @return string
     */
    private function _getHtmlView()
    {
        $jobModels = $this->getJobModels();
        $label = Tools::htmlOut($this->getLabel());
        $count = count($jobModels);
        $output = "<table>\n" . "  <tr>\n" . "    <th>Urgency</th>\n" . "    <th>Title</th>\n" . "    <th>Company</th>\n" . "    <th>Status</th>\n" . "    <th>URL</th>\n" . "    <th>Next Action</th>\n" . "    <th>Due</th>\n" . "  </tr>\n";
        $applicationStatusController = new ApplicationStatusController('read');
        foreach ($jobModels as $jobModel) {
            $id = (int) $jobModel->getId();
            $cid = $jobModel->getCompanyId();
            $companyController = new CompanyController('read');
            $companyModel = $companyController->get($cid);
            $cName = Tools::htmlOut($companyModel->getCompanyName());
            $cCity = $companyModel->getCompanyCity();
            $cState = $companyModel->getCompanyState();
            $cLocation = '';
            if ($cCity !== '' && $cState !== '' && $cState !== 'XX') {
                $cLocation = ' (' . Tools::htmlOut($cCity) . ', ' . Tools::htmlOut($cState) . ')';
            } elseif ($cCity !== '') {
                $cLocation = ' (' . Tools::htmlOut($cCity) . ')';
            } elseif ($cState !== '' && $cState !== 'XX') {
                $cLocation = ' (' . Tools::htmlOut($cState) . ')';
            }
            $cUrl = Tools::safeUrl($companyModel->getCompanyUrl());
            $jobUrl = Tools::safeUrl($jobModel->getUrl());
            $jobTitle = Tools::htmlOut($jobModel->getPositionTitle());
            $jobNextAction = Tools::htmlOut($jobModel->getNextAction());
            $rawNextActDue = $jobModel->getNextActionDue();
            $jobNextActDue = Tools::htmlOut($rawNextActDue);
            $dueClass = (isset($rawNextActDue) && ($rawNextActDue !== '') && ($rawNextActDue < date('Y-m-d H:i:s'))) ? ' class="overdue"' : '';
            $jobUrgency = Tools::htmlOut($jobModel->getUrgency());
            $asId = $jobModel->getApplicationStatusId();
            $statusModel = $applicationStatusController->get($asId);
            $statusValue = $statusModel ? Tools::htmlOut($statusModel->getStatusValue()) : '---';
            $statusStyle = $statusModel ? $statusModel->getStyle() : '';
            $output .= "  <tr>\n" . "    <td>$jobUrgency</td>\n" . "    <td><a href=\"jobs.php#ux$id\">$jobTitle</a></td>\n" . "    <td>$cName$cLocation</td>\n" . "    <td style=\"$statusStyle\">$statusValue</td>\n" . "    <td><a href=\"#\" onclick=\"reviewJob( '$id', '$jobUrl' ); return false;\">Review</a> | <a href=\"$jobUrl\" target=\"_blank\">New Tab</a></td>\n" . "    <td>$jobNextAction</td>\n" . "    <td$dueClass>$jobNextActDue</td>\n" . "  </tr>\n";
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
        switch ($this->_viewType) {
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
        return $this->_jobModels;
    }

    /**
     *
     * @param JobModel[] $jobModels
     */
    public function setJobModels($jobModels)
    {
        $this->_jobModels = $jobModels;
    }

    /**
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->_label = $label;
    }

    /**
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }
}
