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

require_once('Libs/autoload.php') ;

/**
 * AuthTicket Controller
 */
class AuthTicketController extends ControllerBase
{
    private $_expireSeconds = 3600 ;

    /**
     * Class constructor
     *
     * @param string $readWriteMode "read", "write", or "admin"
     * @throws ControllerException
     */
    public function __construct($readWriteMode = 'write')
    {
        parent::__construct($readWriteMode) ;
    }

    /**
     * Drop the auth_ticket table
     *
     * @throws ControllerException
     */
    public function dropTable()
    {
        $sql = "DROP TABLE IF EXISTS auth_ticket" ;
        $this->_doDDL($sql) ;
    }

    /**
     * Create the auth_ticket table
     *
     * @throws ControllerException
     */
    public function createTable()
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS auth_ticket (
    auth_ticket CHAR( 64 ) NOT NULL
  , created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'
  , updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  , expires TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'
  , UNIQUE auth_ticket_udx ( auth_ticket )
  , KEY expires_idx ( expires )
)
SQL;
        $this->_doDDL($sql) ;
    }

    /**
     * Get data for a specific auth_ticket that hasn't expired yet.
     *
     * @param string $auth_ticket
     * @return AuthTicketModel
     * @throws ControllerException
     */
    public function get($auth_ticket)
    {
        $now = date('Y-m-d H:i:s', time()) ;
        $sql = <<<SQL
SELECT created
     , updated
     , expires
  FROM auth_ticket
 WHERE auth_ticket = ?
   AND expires >= '$now'
SQL;
        $stmt = $this->_dbh->prepare($sql) ;
        if (! $stmt) {
            throw new ControllerException('Prepared statement failed for ' . $sql) ;
        }
        if (! ($stmt->bind_param('s', $auth_ticket))) {
            throw new ControllerException('Binding parameters for prepared statement failed.') ;
        }
        if (! $stmt->execute()) {
            throw new ControllerException('Failed to execute SELECT statement. (' . $this->_dbh->error . ')') ;
        }
        $created = $updated = $expires = null ;
        $stmt->bind_result(
            $created,
            $updated,
            $expires
                          ) ;
        if (! $stmt->fetch()) {
            throw new ControllerException("Record missing: $auth_ticket") ;
        }
        $atm = new AuthTicketModel() ;
        $atm->setAuthTicket($auth_ticket) ;
        $atm->setCreated($created) ;
        $atm->setUpdated($updated) ;
        $atm->setExpires($expires) ;
        return $atm ;
    }

    /**
     * Returns all auth_ticket records even those that have expired but not removed yet.
     *
     * @return AuthTicketModel[]
     * @throws ControllerException
     * @SuppressWarnings indentation
     */
    public function getAll()
    {
        $models = [] ;
        $sql = <<<SQL
SELECT auth_ticket
     , created
     , updated
     , expires
  FROM auth_ticket
 ORDER BY expires DESC
SQL;
        $stmt = $this->_dbh->prepare($sql) ;
        if (! $stmt) {
            throw new ControllerException('Failed to prepare SELECT statement. (' . $this->_dbh->error . ')') ;
        }
        if (! $stmt->execute()) {
            throw new ControllerException('Failed to execute SELECT statement. (' . $this->_dbh->error . ')') ;
        }
        $auth_ticket = $created = $updated = $expires = null ;
        $stmt->bind_result(
            $auth_ticket,
            $created,
            $updated,
            $expires
                          ) ;
        while ($stmt->fetch()) {
            $model = new AuthTicketModel() ;
            $model->setAuthTicket($auth_ticket) ;
            $model->setCreated($created) ;
            $model->setUpdated($updated) ;
            $model->setExpires($expires) ;
            $models[] = $model ;
        }
        return($models) ;
    }

    /**
     * Add a new AuthTicket. Tickets will always expire in $this->_expireSeconds seconds.
     *
     * @param AuthTicketModel $model
     * @throws ControllerException
     * @return string auth_ticket added on success
     * @SuppressWarnings indentation
     */
    public function add($model)
    {
        if ($model->validateForAdd()) {
            try {
                $query = 'INSERT auth_ticket'
                           . ' ( auth_ticket'
                            . ', created'
                            . ', updated'
                            . ', expires'
                           . ' )'
                      . ' VALUES ( ?, NOW(), NOW(), ? )'
                      . ' ON DUPLICATE KEY UPDATE expires = ?';
                $stmt = $this->_dbh->prepare($query) ;
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $query) ;
                }
                $authTicket = $model->getAuthTicket() ;
                $expires    = date('Y-m-d H:i:m', time() + $this->_expireSeconds) ;
                if (! ($stmt->bind_param(
                    'sss',
                    $authTicket,
                    $expires,
                    $expires
                                          ))) {
                    throw new ControllerException('Binding parameters for prepared statement failed.') ;
                }
                if (! $stmt->execute()) {
                    throw new ControllerException('Failed to execute INSERT statement. Is this duplicate data? (' . $this->_dbh->error . ')') ;
                }
                /** @SuppressWarnings checkAliases */
                if (! $stmt->close()) {
                    throw new ControllerException('Something broke while trying to close the prepared statement.') ;
                }
                return $authTicket ;
            } catch (Exception $e) {
                throw new ControllerException($e->getMessage()) ;
            }
        } else {
            throw new ControllerException('Invalid data') ;
        }
    }

    /**
     * Update an AuthTicket. Note that every update to this record will always
     * reset the expires time to now + $this->_expireSeconds seconds.
     *
     * @param AuthTicketModel $model
     * @throws ControllerException
     * @return string authTicket on success
     * @SuppressWarnings indentation
     */
    public function update($model)
    {
        if ($model->validateForUpdate()) {
            try {
                $sql = 'UPDATE auth_ticket'
                     .   ' SET expires = ?'
                     . ' WHERE auth_ticket = ?'
                     ;
                $stmt = $this->_dbh->prepare($sql) ;
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $sql) ;
                }
                $authTicket  = $model->getAuthTicket() ;
                $expires     = date('Y-m-d H:i:s', time() + $this->_expireSeconds) ;
                if (! ($stmt->bind_param(
                    'ss',
                    $expires,
                    $authTicket
                                          ))) {
                    throw new ControllerException('Binding parameters for prepared statement failed.') ;
                }
                if (! $stmt->execute()) {
                    throw new ControllerException('Failed to execute INSERT statement. Is this duplicate data? (' . $this->_dbh->error . ')') ;
                }
                /** @SuppressWarnings checkAliases */
                if (! $stmt->close()) {
                    throw new ControllerException('Something broke while trying to close the prepared statement.') ;
                }
                return $authTicket ;
            } catch (Exception $e) {
                throw new ControllerException("Update failed." . $e->getMessage()) ;
            }
        } else {
            throw new ControllerException('Invalid data') ;
        }
    }

    /**
     * Get rid of an AuthTicket - usually at logout.
     *
     * @param AuthTicketModel $model
     * @throws ControllerException
     */
    public function delete($model)
    {
        $authTicket = $model->getAuthTicket() ;
        $sql        = "DELETE FROM auth_ticket"
                    . " WHERE auth_ticket = ?"
                    ;
        $stmt = $this->_dbh->prepare($sql) ;
        if (!$stmt) {
            throw new ControllerException('Prepared statement failed for ' . $sql) ;
        }
        if (! $stmt->bind_param('s', $authTicket)) {
            throw new ControllerException('Binding parameters for prepared statement failed.') ;
        }
        if (!$stmt->execute()) {
            throw new ControllerException('Failed to execute DELETE statement. (' . $this->_dbh->error . ')') ;
        }
        /**
         * @SuppressWarnings checkAliases
         */
        if (!$stmt->close()) {
            throw new ControllerException('Something broke while trying to close the prepared statement.') ;
        }
        return ;
    }

    /**
     * Remove expired tickets
     *
     * @throws ControllerException
     */
    public function cleanExpiredTickets()
    {
        $dateStr = date('Y-m-d H:i:s', time() - $this->_expireSeconds) ;
        $sql = "DELETE FROM auth_ticket WHERE expires < '$dateStr'" ;
        if (! $this->_dbh->query($sql)) {
            throw new ControllerException('Failed to execute DELETE statement. (' . $this->_dbh->error . ')') ;
        }
    }
}
