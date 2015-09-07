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
 * Template Model
 */
class TemplateModel extends ModelBase {

    private $_noteId ;
    private $_appliesToTable ;
    private $_appliesToId ;
    private $_created ;
    private $_updated ;
    private $_noteText ;

    /**
     * class constructor
     */
    public function __construct() {
        parent::__construct() ;
    }

    /**
     * Validate model for insert
     *
     * @return boolean
     * @todo Implement TemplateModel::validateForAdd()
     */
    public function validateForAdd() {
        return 0 ;
    }

    /**
     * Validate model for update
     *
     * @return boolean
     * @todo Implement TemplateModel::validateForUpdate()
     */
    public function validateForUpdate() {
        return 0 ;
    }

    // @todo Implement TemplateModel::populateFromForm()
    public function populateFromForm() {
        $this->setNoteId( Tools::param( 'noteId' ) ) ;
        $this->setAppliesToTable( Tools::param( 'appliesToTable' ) ) ;
        $this->setAppliesToId( Tools::param( 'appliesToId' ) ) ;
        $this->setCreated( Tools::param( 'created' ) ) ;
        $this->setUpdated( Tools::param( 'updated' ) ) ;
        $this->setNoteText( Tools::param( 'noteText' ) ) ;
    }

    /**
     * @return integer
     */
    public function getNoteId() {
        return $this->_noteId ;
    }

    /**
     * @param integer $noteId
     */
    public function setNoteId( $noteId ) {
        $this->_noteId = $noteId ;
    }

    /**
     * @return string
     */
    public function getAppliesToTable() {
        return $this->_appliesToTable ;
    }

    /**
     * @param string $appliesToTable
     */
    public function setAppliesToTable( $appliesToTable ) {
        $this->_appliesToTable = $appliesToTable ;
    }

    /**
     * @return integer
     */
    public function getAppliesToId() {
        return $this->_appliesToId ;
    }

    /**
     * @param integer $appliesToId
     */
    public function setAppliesToId( $appliesToId ) {
        $this->_appliesToId = $appliesToId ;
    }

    /**
     * @return string
     */
    public function getCreated() {
        return $this->_created ;
    }

    /**
     * @param string $created
     */
    public function setCreated( $created ) {
        $this->_created = $created ;
    }

    /**
     * @return string
     */
    public function getUpdated() {
        return $this->_updated ;
    }

    /**
     * @param string $updated
     */
    public function setUpdated( $updated ) {
        $this->_updated = $updated ;
    }

    /**
     * @return string
     */
    public function getNoteText() {
        return $this->_noteText ;
    }

    /**
     * @param string $noteText
     */
    public function setNoteText( $noteText ) {
        $this->_noteText = $noteText ;
    }

}
