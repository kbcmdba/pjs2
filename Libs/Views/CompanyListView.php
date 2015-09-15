<?php

/**
 * phpjobseeker
 *
 * Copyright (C) 2009, 2015 Kevin Benton - kbenton at bentonfam dot org
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
 * Company List View
 */

class CompanyListView extends ListViewBase {

    /** @var string */
    private $_viewType ;
    /** @var mixed */
    private $_supportedViewTypes = array( 'html' => 1 ) ;
    /** @var CompanyModel[] */
    private $_companyModels ;

    /**
     * Class constructor
     *
     * @param string View Type
     * @param CompanyModel[] $companyModels
     * @throws ViewException
     */
    public function __construct( $viewType = 'html', $companyModels ) {
        parent::__construct() ;
        if ( ! isset( $this->_supportedViewTypes[ $viewType ] ) ) {
            throw new ViewException( "Unsupported view type\n" ) ;
        }
        $this->_viewType = $viewType ;
        $this->setCompanyModels( $companyModels ) ;
    }

    /**
     * Return the HTML view
     *
     * @return string
     */
    private function _getHtmlView() {
        $body = <<<'HTML'
<a href="addCompany.php">Add new company</a><br />
<table border="1" cellspacing="0" cellpadding="2">
  <caption>Current Companies</caption>
  <tr><th>Actions</th><th>Company</th><th>Agency Of</th><th>City</th><th>State</th></tr>
HTML;
        foreach ( $this->getCompanyModels() as $companyModel ) {
            $id              = $companyModel->getId() ;
            $isAnAgency      = $companyModel->getIsAnAgency() ;
            $agencyCompanyId = $companyModel->getAgencyCompanyId() ;
            $companyName     = $companyModel->getCompanyName() ;
            $companyAddress1 = $companyModel->getCompanyAddress1() ;
            $companyAddress2 = $companyModel->getCompanyAddress2() ;
            $companyCity     = $companyModel->getCompanyCity() ;
            $companyState    = $companyModel->getCompanyState() ;
            $companyZip      = $companyModel->getCompanyZip() ;
            $companyUrl      = $companyModel->getCompanyUrl() ;
            $created         = $companyModel->getCreated() ;
            $updated         = $companyModel->getUpdated() ;
            if ( $isAnAgency ) {
                $agencyCompanyController = new CompanyController() ;
                $agencyCompanyModel      = $agencyCompanyController->get( $agencyCompanyId ) ;
                $agencyOf                = $agencyCompanyModel->getCompanyName() ;
            }
            else {
                $agencyOf                = 'None' ;
            }
            $body .= <<<HTML
  <tr>
    <td>
        <a href="editCompany.php?id=$id">Edit</a>
      | <a href="deleteCompany.php?id=$id">Delete</a>
    </td>
    <td>$companyName</td>
    <td>$agencyOf</td>
    <td>$companyCity</td>
    <td>$companyState</td>
  </tr>
HTML;
        }

        $body .= '</table>' ;

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
     * @return CompanyModel[]
     */
    public function getCompanyModels() {
        return $this->_companyModels ;
    }

    /**
     * @param CompanyModel[] $companyModels
     */
    public function setCompanyModels( $companyModels ) {
        $this->_companyModels = $companyModels ;
    }

}