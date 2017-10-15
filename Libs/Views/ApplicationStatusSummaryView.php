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

/**
 * Application Status Summary View
 */
class ApplicationStatusSummaryView extends SummaryViewBase {

    /** @var string */
    private $_viewType ;
    /** @var mixed */
    private $_supportedViewTypes = array( 'html' => 1 ) ;
    /** @var ApplicationStatusModel[] */
    private $_applicationStatusModels ;

    /**
     * Class constructor
     *
     * @param string View Type
     * @throws ViewException
     */
    public function __construct( $viewType = 'html', $applicationStatusModels = null ) {
        parent::__construct() ;
        if ( ! isset( $this->_supportedViewTypes[ $viewType ] ) ) {
            throw new ViewException( "Unsupported view type\n" ) ;
        }
        $this->_viewType = $viewType ;
        $this->_applicationStatusModels = $applicationStatusModels ;
    }

    /**
     * Return the HTML view
     *
     * @return string
     */
    private function _getHtmlView() {
        $body = <<<'HTML'
<table border="1" cellspacing="0" cellpadding="2" id="applicationStatus" >
  <caption>Current Application Statuses</caption>
  <thead>
    <tr>
      <th>Status</th>
      <th>Count</th>
      <th>Is Active?</th>
    </tr>
  </thead>
  <tbody>

HTML;
        foreach ( $this->_applicationStatusModels as $applicationStatus ) {
            $id   = $applicationStatus->getId() ;
            $label = $applicationStatus->getStatusValue() ;
            $style = $applicationStatus->getStyle() ;
            $count = $applicationStatus->getSummaryCount() ;
            $isAct = $applicationStatus->getIsActive() ? "Yes" : "No" ;
            $body .= <<<HTML
    <tr id="ux$id">
      <td style="$style">$label</td>
      <td>$count</td>
      <td>$isAct</td>
    </tr>

HTML;
        }

        $body .= "  </tbody>\n</table>\n" ;
        return $body ;
    }

    /**
     *
     * @return string
     * @throws ViewException
     */
    public function getView() {
        switch ( $this->_viewType ) {
            case 'html' :
                return $this->_getHtmlView() ;
            default :
                throw new ViewException( "Unsupported view type." ) ;
        }
    }

    /**
     * @return ApplicationStatusModel[]
     */
    public function getApplicationStatusModels() {
        return $this->_applicationStatusModels ;
    }

    /**
     * @param ApplicationStatusModel[] $applicationStatusModels
     */
    public function setApplicationStatusModels( $applicationStatusModels ) {
        $this->_applicationStatusModels = $applicationStatusModels ;
    }

}
