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
namespace com\kbcmdba\pjs2;

/**
 * User Authorization Class
 */
class Auth
{

    private static $_userId = null;

    private static $_userRole = null;

    private static $_authTicket = null;

    private static $_userValidated = null;

    /** @var Config */
    private static $_config = null;

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
        self::$_config = $config;
        // Users are always authorized if the configuration tells us to skip authentication.
        if ($config->getSkipAuth()) {
            self::$_userRole = 'admin';
            $this->_ensureCsrfToken();
            return;
        }
        if ($this->isAuthorized($readOnly)) {
            if (isset($_POST['auth_username']) && isset($_POST['auth_password']) && ! $readOnly && self::validateCsrfToken()) {
                // User is logging in.
                $authTicket = bin2hex(openssl_random_pseudo_bytes(32));
                $atc = new AuthTicketController();
                $atm = new AuthTicketModel();
                $atm->setAuthTicket($authTicket);
                $atc->add($atm);
                $userId = self::$_userId;
                $now = date("Y-m-d H:i:s");
                $out = "$now: Login detected for $userId." . PHP_EOL;
                file_put_contents("login.log", $out, FILE_APPEND);
                self::$_authTicket = $authTicket;
                $_SESSION['auth_ticket'] = self::$_authTicket;
                $_SESSION['user_role'] = self::$_userRole;
                $this->_ensureCsrfToken();
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
        if (self::$_config->getSkipAuth()) {
            return true;
        }
        // Has this user already been validated during this transaction?
        if (isset(self::$_userValidated)) {
            return self::$_userValidated;
        }
        if (isset($_SESSION['auth_ticket'])) {
            // Verify that the user's session is valid.
            try {
                $atc = new AuthTicketController();
                $atc->cleanExpiredTickets();
                $atm = $atc->get($_SESSION['auth_ticket']);
                if (FALSE === $atm) {
                    // Ticket expired or missing. Check for a new login attempt.
                    return $this->_validateCredentials();
                }
            } catch (ControllerException $e) {
                return $this->_validateCredentials();
            }
            if (! $readOnly) {
                $atc->update($atm);
            }
            // Restore role from session
            if (isset($_SESSION['user_role'])) {
                self::$_userRole = $_SESSION['user_role'];
            }
            $this->_ensureCsrfToken();
            self::$_userValidated = true;
            return self::$_userValidated;
        }
        return $this->_validateCredentials();
    }

    /**
     * Validate username/password from POST against the user table.
     *
     * @return boolean
     */
    private function _validateCredentials()
    {
        if (! isset($_POST['auth_username']) || ! isset($_POST['auth_password'])) {
            self::$_userValidated = false;
            return false;
        }
        $username = $_POST['auth_username'];
        $password = $_POST['auth_password'];
        try {
            $uc = new UserController('read');
            $user = $uc->getByUsername($username);
            if ($user && password_verify($password, $user->getPassword())) {
                self::$_userId = $user->getUserName();
                self::$_userRole = $user->getRole();
                self::$_userValidated = true;
                return true;
            }
        } catch (ControllerException $e) {
            // Fall through to false
        }
        self::$_userValidated = false;
        return false;
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
        $this->_ensureCsrfToken();
        $csrfToken = Tools::htmlOut($_SESSION['csrf_token']);
        $body = <<<HTML
<form action="index.php" method="POST">
<input type="hidden" name="csrf_token" value="$csrfToken" />
Login Page
  <table>
    <tr>
      <th>Login</th>
      <td><input type="text" name="auth_username" /></td>
    </tr>
    <tr>
      <th>Password</th>
      <td><input type="password" name="auth_password" /></td>
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
            $user = self::$_userId;
            file_put_contents("login.log", "$now: Logout detected for $user." . PHP_EOL, FILE_APPEND);
        }
        if (isset($_SESSION['auth_username'])) {
            unset($_SESSION['auth_username']);
        }
        if (isset($_SESSION['auth_password'])) {
            unset($_SESSION['auth_password']);
        }
        if (isset($_SESSION['user_role'])) {
            unset($_SESSION['user_role']);
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
        return self::$_authTicket;
    }

    /**
     * Get the current user's role.
     *
     * @return string|null 'admin', 'user', or 'viewer'
     */
    public function getUserRole()
    {
        return self::$_userRole;
    }

    /**
     * Check if the current user has at least the given role level.
     * admin > user > viewer
     *
     * @param string $requiredRole
     * @return boolean
     */
    public function hasRole($requiredRole)
    {
        $roleLevels = [
            'viewer' => 1,
            'user' => 2,
            'admin' => 3
        ];
        $userLevel = isset($roleLevels[self::$_userRole]) ? $roleLevels[self::$_userRole] : 0;
        $requiredLevel = isset($roleLevels[$requiredRole]) ? $roleLevels[$requiredRole] : 99;
        return $userLevel >= $requiredLevel;
    }

    /**
     * Ensure a CSRF token exists in the session.
     */
    private function _ensureCsrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
    }

    /**
     * Get the current CSRF token.
     *
     * @return string
     */
    public function getCsrfToken()
    {
        $this->_ensureCsrfToken();
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate a CSRF token from the request.
     * Checks both POST parameter and X-CSRF-Token header.
     *
     * @return boolean
     */
    public static function validateCsrfToken()
    {
        if (self::$_config !== null && self::$_config->getSkipAuth()) {
            return true;
        }
        $token = null;
        if (isset($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
        } elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        if ($token === null || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
