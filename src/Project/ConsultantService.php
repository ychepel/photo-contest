<?php

namespace Project;

use MeekroDBException;

class ConsultantService extends Service{

    public function getUserConsultantNumber() {
        if(isset($_COOKIE['Consultant'])) {
            if($_COOKIE['Consultant'] != '') {
                return $_COOKIE['Consultant'];
            }
        }
        echo '<meta http-equiv="refresh" content="0; url=https://www.marykayintouch.ua/newspage?newsid='.Config::MARYKAYINTOUCH_PAGE_ID.'" />';
        exit();
    }

    public function getConsultant($consultantNumber) {
        try {
            if ($dbConsultant = $this->dbConnection->queryFirstRow('SELECT * FROM ' . Config::CONSULTANT_TABLE . ' WHERE ConsultantNumber=%s', $consultantNumber)) {
                $consultant = new Consultant();
                $consultant->unitNumber = $dbConsultant['UnitNumber'];
                $consultant->number = $dbConsultant['ConsultantNumber'];
                $consultant->name = $dbConsultant['ConsultantName'];
                $consultant->mailingName = $dbConsultant['MailingName'];
                $consultant->firstName = $dbConsultant['FirstName'];
                $consultant->nsdUnitNumber = $dbConsultant['NSDUnitNumber'];
                $consultant->recruiterNumber = $dbConsultant['RecruiterConsultantNumber'];
                return $consultant;
            } else {
                $this->log->error('Cannot find consultant with number=' . $consultantNumber);
                return false;
            }
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get consultant data', ['exception' => $e]);
        }
        return false;
    }

    /**
     * @param String $consultantNumber
     * @return bool
     */
    public function isDirector($consultantNumber) {
        try {
            if($this->dbConnection->query('SELECT ConsultantNumber FROM '.Config::DIRECTOR_TABLE.' WHERE ConsultantNumber=%s', $consultantNumber)) {
                return $this->dbConnection->count() ? true : false;
            }
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get director data', ['exception' => $e]);
        }
        return false;
    }


}