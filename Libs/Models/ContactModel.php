<?php

/**
 * Contact Model
 */
class ContactModel extends ModelBase {

    private $_contactId ;
    private $_contactCompanyId ;
    private $_contactName ;
    private $_contactEmail ;
    private $_contactPhone ;
    private $_contactAlternatePhone ;
    private $_created ;
    private $_updated ;

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
     * @todo Implement ContactModel::validateForAdd()
     */
    public function validateForAdd() {
        return 0 ;
    }

    /**
     * Validate model for update
     *
     * @return boolean
     * @todo Implement ContactModel::validateForUpdate()
     */
    public function validateForUpdate() {
        return 0 ;
    }

    public function populateFromForm() {
        $this->setContactId( Tools::param( 'contactId' ) ) ;
        $this->setContactCompanyId( Tools::param( 'contactCompanyId' ) ) ;
        $this->setContactName( Tools::param( 'contactName' ) ) ;
        $this->setContactEmail( Tools::param( 'contactEmail' ) ) ;
        $this->setContactPhone( Tools::param( 'contactPhone' ) ) ;
        $this->setContactAlternatePhone( Tools::param( 'contactAlternatePhone' ) ) ;
        $this->setCreated( Tools::param( 'created' ) ) ;
        $this->setUpdated( Tools::param( 'updated' ) ) ;
    }

    /**
     * @return integer
     */
    public function getContactId() {
        return $this->_contactId ;
    }

    /**
     * @param integer $contactId
     */
    public function setContactId( $contactId ) {
        $this->_contactId = $contactId ;
    }

    /**
     * @return integer
     */
    public function getContactCompanyId() {
        return $this->_contactCompanyId ;
    }

    /**
     * @param integer $contactCompanyId
     */
    public function setContactCompanyId( $contactCompanyId ) {
        $this->_contactCompanyId = $contactCompanyId ;
    }

    /**
     * @return string
     */
    public function getContactName() {
        return $this->_contactName ;
    }

    /**
     * @param string $contactName
     */
    public function setContactName( $contactName ) {
        $this->_contactName = $contactName ;
    }

    /**
     * @return string
     */
    public function getContactEmail() {
        return $this->_contactEmail ;
    }

    /**
     * @param string $contactEmail
     */
    public function setContactEmail( $contactEmail ) {
        $this->_contactEmail = $contactEmail ;
    }

    /**
     * @return string
     */
    public function getContactPhone() {
        return $this->_contactPhone ;
    }

    /**
     * @param string $contactPhone
     */
    public function setContactPhone( $contactPhone ) {
        $this->_contactPhone = $contactPhone ;
    }

    /**
     * @return string
     */
    public function getContactAlternatePhone() {
        return $this->_contactAlternatePhone ;
    }

    /**
     * @param string $contactAlternatePhone
     */
    public function setContactAlternatePhone( $contactAlternatePhone ) {
        $this->_contactAlternatePhone = $contactAlternatePhone ;
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

}
