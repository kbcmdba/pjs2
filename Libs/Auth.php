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
namespace com\kbcmdba\pjs2\Libs;

use com\kbcmdba\pjs2\Libs\Config;
use com\kbcmdba\pjs2\Libs\Controllers\AuthTicketController;
use com\kbcmdba\pjs2\Libs\Exceptions\ControllerException;
use com\kbcmdba\pjs2\Libs\Models\AuthTicketModel;

/**
 * User Authorization Class
 *
 * Note: This is *NOT* secure enough to use over a public internet connection.
 * At this point, it is a stub to be improved at a future time.
 */
class Auth
{
    private static $userId = null;
    private static $password = null;
    private static $authTicket = null;
    private static $userValidated = null;

    /** @var Config */
    private static $config = null;

    /**
     * Class constructor
     *
     * @param string $readOnly
     *            If readOnly is true, don't refresh the user's expire time.
     */
    public function __construct($readOnly = false)
    {
        session_start();
        $config = new Config();
        self::$config = $config;
        // Users are always authorized if the configuration tells us to skip authentication.
        if ($config->getSkipAuth()) {
            return;
        }
        self::$userId = $config->getUserId();
        self::$password = $config->getUserPassword();
        if ($this->isAuthorized($readOnly)) {
            if (isset($_POST['auth_username']) && isset($_POST['authpassword']) && ! $readOnly) {
                // User is logging in.
                $authTicket = bin2hex(openssl_random_pseudo_bytes(32));
                $atc = new AuthTicketController();
                $atm = new AuthTicketModel();
                $atm->setAuthTicket($authTicket);
                $atc->add($atm);
                $userId = self::$userId;
                $now = date("Y-m-d H:i:s");
                $out = "$now: Login detected for $userId with $authTicket." . PHP_EOL;
                file_put_contents("login.log", $out, FILE_APPEND);
                self::$authTicket = $authTicket;
                $_SESSION['auth_ticket'] = self::$authTicket;
            }
        }
    }

    /**
     * Checks to make sure that the user's session is logged in.
     *
     * @param string $readOnly
     *            If readOnly is true, don't refresh the user's expire time.
     * @return boolean
     */
    public function isAuthorized($readOnly = false)
    {
        // Users are always authorized if the configuration tells us to skip authentication.
        if (self::$config->getSkipAuth()) {
            return true;
        }
        // Has this user already been validated during this transaction?
        if (isset(self::$userValidated)) {
            return self::$userValidated;
        }
        if (isset($_SESSION['auth_ticket'])) {
            // Verify that the user's session is valid.
            try {
                $atc = new AuthTicketController();
                $atc->cleanExpiredTickets();
                $atm = $atc->get($_SESSION['auth_ticket']);
                if (false === $atm) {
                    return false;
                }
            } catch (ControllerException $e) {
                // No matching record found. User can't be validated through
                // the auth_ticket. If the user has an expired ticket and is
                // trying to log in, we need to check for a login attempt.
                self::$userValidated = (isset($_POST['auth_username']) && isset($_POST['authpassword']) && (self::$userId === $_POST['auth_username']) && (self::$password === $_POST['authpassword']));
                return self::$userValidated;
            }
            if (! $readOnly) {
                $atc->update($atm);
            }
            self::$userValidated = true;
            return self::$userValidated;
        }
        self::$userValidated = (isset($_POST['auth_username']) && isset($_POST['authpassword']) && (self::$userId === $_POST['auth_username']) && (self::$password === $_POST['authpassword']));
        return self::$userValidated;
    }

    /**
     * Sends HTTP 403 error code, Forbidden error message and exits from the program.
     */
    public function forbidden()
    {
        header("HTTP/1.0 403 Forbidden");
        echo "Forbidden";
        exit();
    }

    /**
     * Get the login page string.
     */
    public function getLoginPage()
    {
        $body = <<<HTML
<form action="index.php" method="POST">
Login Page
  <table>
    <tr>
      <th>Login</th>
      <td><input type="text" name="auth_username" /></td>
    </tr>
    <tr>
      <th>Password</th>
      <td><input type="password" name="authpassword" /></td>
    </tr>
    <tr>
      <th colspan="2"><input type="submit" value="Log In" /></td>
    </tr>
  </table>
</form>
HTML;
        return ($body);
    }
    // END OF function loginPage()
    
    /**
     * Destroy session variables that keep the user logged in.
     */
    public function doLogOut()
    {
        if ($this->isAuthorized()) {
            $now = date("Y-m-d H:i:s");
            $user = self::$userId;
            file_put_contents("login.log", "$now: Logout detected for $user." . PHP_EOL, FILE_APPEND);
        }
        if (isset($_SESSION['auth_username'])) {
            unset($_SESSION['auth_username']);
        }
        if (isset($_SESSION['authpassword'])) {
            unset($_SESSION['authpassword']);
        }
        if (isset($_SESSION['auth_ticket'])) {
            $atm = new AuthTicketModel();
            $atm->setAuthTicket($_SESSION['auth_ticket']);
            $atc = new AuthTicketController();
            $atc->delete($atm);
            unset($_SESSION['auth_ticket']);
        }
    }

    public function getAuthTicket()
    {
        return self::$authTicket;
    }
}
