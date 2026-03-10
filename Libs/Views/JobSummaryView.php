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
        $output = "<table>\n" . "  <tr>\n" . "    <th>Urgency</th>\n" . "    <th>Title</th>\n" . "    <th>Company</th>\n" . "    <th>URL</th>\n" . "    <th>Next Action</th>\n" . "    <th>Due</th>\n" . "  </tr>\n";
        foreach ($jobModels as $jobModel) {
            $cid = $jobModel->getCompanyId();
            $companyController = new CompanyController();
            $companyModel = $companyController->get($cid);
            $cName = Tools::htmlOut($companyModel->getCompanyName());
            $cCity = Tools::htmlOut($companyModel->getCompanyCity());
            $cState = Tools::htmlOut($companyModel->getCompanyState());
            $cUrl = Tools::safeUrl($companyModel->getCompanyUrl());
            $jobTitle = Tools::htmlOut($jobModel->getPositionTitle());
            $jobNextAction = Tools::htmlOut($jobModel->getNextAction());
            $jobNextActDue = Tools::htmlOut($jobModel->getNextActionDue());
            $jobUrgency = Tools::htmlOut($jobModel->getUrgency());
            $output .= "  <tr>\n" . "    <td>$jobUrgency</td>\n" . "    <td>$jobTitle</td>\n" . "    <td>$cName ($cCity, $cState)</td>\n" . "    <td><a href=\"$cUrl\">Link</a></td>\n" . "    <td>$jobNextAction</td>\n" . "    <td>$jobNextActDue</td>\n" . "  </tr>\n";
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
