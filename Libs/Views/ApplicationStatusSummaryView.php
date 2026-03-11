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
 * Application Status Summary View
 */
class ApplicationStatusSummaryView extends SummaryViewBase
{

    /** @var string */
    private $_viewType;

    /** @var mixed */
    private $_supportedViewTypes = [
        'html' => 1
    ];

    /** @var ApplicationStatusModel[] */
    private $_applicationStatusModels;

    /**
     * Class constructor
     *
     * @param
     *            string View Type
     * @throws ViewException
     */
    public function __construct($viewType = 'html', $applicationStatusModels = null)
    {
        parent::__construct();
        if (! isset($this->_supportedViewTypes[$viewType])) {
            throw new ViewException("Unsupported view type\n");
        }
        $this->_viewType = $viewType;
        $this->_applicationStatusModels = $applicationStatusModels;
    }

    /**
     * Return the HTML view
     *
     * @return string
     */
    private function _getHtmlView()
    {
        $jobController = new JobController('read');
        $appStatusController = new ApplicationStatusController('read');
        $body = "<h3>Application Statuses</h3>\n";
        foreach ($this->_applicationStatusModels as $applicationStatus) {
            $id = $applicationStatus->getId();
            $label = Tools::htmlOut($applicationStatus->getStatusValue());
            $style = Tools::htmlOut($applicationStatus->getStyle());
            $count = (int) $applicationStatus->getSummaryCount();
            $isAct = $applicationStatus->getIsActive() ? "Active" : "Inactive";
            $body .= "<details id=\"ux$id\">\n";
            $body .= "  <summary style=\"$style cursor: pointer; padding: 6px 10px; margin: 2px 0;\">$label &mdash; $count job(s) &mdash; $isAct</summary>\n";
            if ($count > 0) {
                $jobs = $jobController->getByApplicationStatus($id);
                if (count($jobs) > 0) {
                    $view = new JobSummaryView('html', $jobs);
                    $view->setLabel($label);
                    $body .= $view->getView();
                }
            } else {
                $body .= "  <p style=\"padding-left: 20px;\">No jobs with this status.</p>\n";
            }
            $body .= "</details>\n";
        }
        return $body;
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
     * @return ApplicationStatusModel[]
     */
    public function getApplicationStatusModels()
    {
        return $this->_applicationStatusModels;
    }

    /**
     *
     * @param ApplicationStatusModel[] $applicationStatusModels
     */
    public function setApplicationStatusModels($applicationStatusModels)
    {
        $this->_applicationStatusModels = $applicationStatusModels;
    }
}
