<?php

namespace Project;

use DateTime;
use MeekroDBException;

class SessionService extends Service
{
    private static $tokenName = 'ViitaminToken';
    private $adminTokenName = 'VitaminAdminToken';

    public static function registerConsultantSession($consultantNumber) {
        $sessionService = new SessionService();
        if(!isset($_COOKIE[static::$tokenName])) {
            $sessionService->saveCurrentSession($consultantNumber);
            return true;
        }

        $lastSession = $sessionService->getLastSessionTime($consultantNumber);
        if($lastSession === false || $lastSession->modify('+1 hour') < new DateTime()) {
            $sessionService->saveCurrentSession($consultantNumber);
            return true;
        }
    }

    private function getLastSessionTime($consultantNumber) {
        try {
            if ($lastSession = $this->dbConnection->queryFirstField("SELECT LastSession FROM ".Config::USER_TABLE." WHERE ConsultantNumber=%s", $consultantNumber)) {
                return new DateTime($lastSession);
            } else {
                return false;
            }
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get user session data', ['exception' => $e]);
        }
        return false;
    }

    private function saveCurrentSession($consultantNumber) {
        try {
            $hash = $this->generateHash($consultantNumber);
            $this->dbConnection->insertUpdate(Config::USER_TABLE, [
                'ConsultantNumber' => $consultantNumber,
                'LastSession' => date('Y-m-d H:i:s'),
                'Token' => $hash
            ]);
            setcookie(static::$tokenName, $hash, 60*60);
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot save current session to DB', ['exception' => $e]);
        }
    }

    private function generateHash($consultantNumber) {
        return md5(time().$consultantNumber.rand(0,100));
    }

    public function getToken($consultantNumber) {
        try {
            if ($token = $this->dbConnection->queryFirstField("SELECT Token FROM ".Config::USER_TABLE." WHERE ConsultantNumber=%s", $consultantNumber)) {
                return $token;
            } else {
                return false;
            }
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get token', ['exception' => $e]);
        }
        return false;
    }

    public function checkAdminToken($adminToken) {
        try {
            $lastSession = $this->dbConnection->queryFirstField("SELECT CreatedAt FROM ".Config::ADMIN_SESSION_TABLE." WHERE Token=%s AND Application='".Config::APP_NAME."'", $adminToken);
            $lastDateTimeSession = new DateTime($lastSession);
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get admin session data', ['exception' => $e]);
            return false;
        }
        if($lastSession === false || $lastDateTimeSession->modify('+12 hour') < new DateTime()) {
            return false;
        }
        return true;
    }

    public function checkAuthentication($user, $password) {
        $correct = false;
        if($user == Config::ADMIN_USER_NAME && $password == Config::ADMIN_PASSWORD) {
            $this->registerAdminSession($user);
            $correct = true;
        }
        elseif($user == Config::SUPERADMIN_USER_NAME && $password == Config::SUPERADMIN_PASSWORD) {
            $this->registerAdminSession($user);
            $correct = true;
        }
        $this->registerAdminLoginAttempt($user, $password, $correct);
        return $correct;
    }

    private function registerAdminSession($user) {
        try {
            $token = $this->generateHash(getenv("REMOTE_ADDR"));
            $this->dbConnection->insert(Config::ADMIN_SESSION_TABLE, [
                'Application' => Config::APP_NAME,
                'IpAddress' => getenv("REMOTE_ADDR"),
                'CreatedAt' => date('Y-m-d H:i:s'),
                'Token' => $token,
                'UserName' => $user
            ]);
            setcookie($this->adminTokenName, $token, time()+60*60*12);
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot save current admin session to DB', ['exception' => $e]);
        }
    }

    private function registerAdminLoginAttempt($user, $password, $correct) {
        try {
            $token = $this->generateHash(getenv("REMOTE_ADDR"));
            $this->dbConnection->insert(Config::ADMIN_LOGIN_ATTEMPT, [
                'Application' => Config::APP_NAME,
                'IpAddress' => getenv("REMOTE_ADDR"),
                'CreatedAt' => date('Y-m-d H:i:s'),
                'User' => $user,
                'Password' => $password,
                'Success' => $correct
            ]);
            setcookie($this->adminTokenName, $token, time()+60*60*12);
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot save admin login attempt to DB', ['exception' => $e]);
        }
    }

    public function isUserSuperAdmin($user) {
        return $user == Config::SUPERADMIN_USER_NAME;
    }

    public function isSessionOfSuperAdmin($adminToken) {
        try {
            $lastSession = $this->dbConnection->queryFirstRow("SELECT CreatedAt, UserName FROM ".Config::ADMIN_SESSION_TABLE." WHERE Token=%s AND Application='".Config::APP_NAME."'", $adminToken);
            $lastDateTimeSession = new DateTime($lastSession['CreatedAt']);
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get admin session data', ['exception' => $e]);
            return false;
        }
        if($lastSession === false || $lastDateTimeSession->modify('+12 hour') < new DateTime()) {
            return false;
        }
        return $lastSession['UserName'] == Config::SUPERADMIN_USER_NAME;
    }
}