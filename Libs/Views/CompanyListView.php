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
        $rowStyle = "even" ;
        $body = <<<'HTML'
<a href="addCompany.php">Add new company</a><br />
<table border="1" cellspacing="0" cellpadding="2">
  <caption>Current Companies</caption>
  <thead>
    <tr class="thead">
      <th rowspan="2">Actions</th>
      <th><font size="+2">Company</font></th>
      <th>Address 1</th>
      <th>City</th>
      <th>State</th>
      <th>Zip</th>
    </tr>
    <tr class="thead">
      <th>Agency Of</th>
      <th>Address 2</th>
      <th>Phone</th>
      <th colspan="2">URL</th>
    </tr>
  </thead>
  <tbody>
HTML;
        foreach ( $this->getCompanyModels() as $companyModel ) {
            $id       = $companyModel->getId() ;
            $rowStyle = ( "even" === $rowStyle ) ? 'odd' : 'even' ;
            $row      = $this->displayCompanyRow( $companyModel, 'list', $rowStyle ) ;
            $body    .= "    <tr id='ux$id'>\n      $row\n    </tr>\n" ;
        }
        $body .= "  </tbody>\n</table>\n" ;
        return $body ;
    }

    public function displayCompanyRow( $companyModel, $displayMode, $rowStyle ) {
        $id              = $companyModel->getId() ;
        $isAnAgency      = $companyModel->getIsAnAgency() ;
        $agencyCompanyId = $companyModel->getAgencyCompanyId() ;
        $companyName     = $companyModel->getCompanyName() ;
        $companyAddress1 = $companyModel->getCompanyAddress1() ;
        $companyAddress2 = $companyModel->getCompanyAddress2() ;
        $companyCity     = $companyModel->getCompanyCity() ;
        $companyState    = $companyModel->getCompanyState() ;
        $companyZip      = $companyModel->getCompanyZip() ;
        $companyPhone    = $companyModel->getCompanyPhone() ;
        $companyUrl      = $companyModel->getCompanyUrl() ;
        $created         = $companyModel->getCreated() ;
        $updated         = $companyModel->getUpdated() ;
        $encodedUrl      = htmlspecialchars( $companyUrl ) ;
        if ( $isAnAgency ) {
            $agencyCompanyController = new CompanyController() ;
            $agencyCompanyModel      = $agencyCompanyController->get( $agencyCompanyId ) ;
            $agencyOf                = $agencyCompanyModel->getCompanyName() ;
        }
        else {
            $agencyOf                = 'None' ;
        }
        switch ( $displayMode ) {
            case 'add'    :
                break ;
            case 'delete' :
                break ;
            case 'list'   :
                $body .= <<<HTML
    <tr class="tr$rowStyle" id="ux$id-1">
      <td rowspan="2">
        <button type="button" id="UpdateButton$id" onclick="updateCompany( '$id' )">Update</button>
        <button type="button" id="DeleteButton$id" onclick="deleteCompany( '$id' )">Delete</button>
      </td>
      <td><font size="+2">$companyName</font></td>
      <td>$companyAddress1</th>
      <td>$companyCity</td>
      <td>$companyState</td>
      <td>$companyZip</td>
    </tr>
    <tr class="tr$rowStyle" id="ux$id-2">
      <td>$agencyOf</td>
      <td>$companyAddress2</th>
      <td>$companyPhone</td>
      <td colspan="2"><a href="$encodedUrl">$encodedUrl</a></td>
    </tr>
HTML;
                break ;
            case 'update' :
                break ;
        }
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