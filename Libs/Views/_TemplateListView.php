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
 * _Template List View
 *
 * Edit "_Template" appropriately
 * Edit "_template" appropriately.
 * Edit "Template" to your class description (used for a button)
 * Verify all template values for labels
 */

class _Template extends ListViewBase {

    /** @var string */
    private $_viewType ;
    /** @var mixed */
    private $_supportedViewTypes = array( 'html' => 1 ) ;
    /** @var _TemplateModel[] */
    private $_templateModels ;

    /**
     * Class constructor
     *
     * @param string View Type
     * @throws ViewException
     */
    public function __construct( $viewType = 'html' ) {
        parent::__construct() ;
        if ( ! isset( $this->_supportedViewTypes[ $viewType ] ) ) {
            throw new ViewException( "Unsupported view type\n" ) ;
        }
        $this->_viewType = $viewType ;
    }

    /**
     * Return the HTML view
     *
     * @return string
     */
    private function _getHtmlView() {
        $body = <<<'HTML'
<a href="add_Template.php">Add new template</a><br />
<table border="1" cellspacing="0" cellpadding="2">
  <caption>Current Templates</caption>
  <tr><th>Actions</th><th>Blah</th><th>Blah</th><th>Blah</th></tr>
HTML;
        foreach ( $this->_templateModels as $template ) {
            $id = $template->getId() ;
            $name = $template->get_TemplateName() ;
            $primaryPhone = $template->getPrimaryPhone() ;
            $backupPhone = $template->getBackupPhone() ;
            $body .= <<<HTML
  <tr>
    <td>
        <a href="edit_Template.php?templateId=$id">Edit</a>
        <a href="delete_Template.php?templateId=$id">Delete</a>
    </td>
    <td>$blah</td>
    <td>$blah</td>
    <td>$blah</td>
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
     * @return _TemplateModel[]
     */
    public function get_TemplateModels() {
        return $this->_templateModels ;
    }

    /**
     * @param _TemplateModel[] $templateModels
     */
    public function set_TemplateModels( $templateModels ) {
        $this->_templateModels = $templateModels ;
    }

}