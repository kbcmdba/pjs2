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
 * Form View Base
 */
abstract class FormViewBase {
    private $_title ;
    private $_buttonLabel ;

    public function __construct( $title ) {
        $this->setTitle( $title ) ;
        $this->setButtonLabel( $title ) ;
    }

    /**
     * @param string $readOnly 'readwrite' or 'readonly'
     */
    abstract public function getForm( $readOnly = 'readwrite' ) ;

    /**
     * @return string
     */
    public function getTitle() {
        return( $this->_title ) ;
    }

    /**
     * @param string $title
     */
    public function setTitle( $title ) {
        $this->_title = $title ;
    }

    /**
     * @return string
     */
    public function getButtonLabel() {
        return( $this->_buttonLabel ) ;
    }

    /**
     * @param string $title
     */
    public function setButtonLabel( $buttonLabel ) {
        $this->_buttonLabel = $buttonLabel ;
    }

}
