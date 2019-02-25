<?php

namespace Project;

class Consultant {
    var $unitNumber;
    var $number;
    var $name;
    var $mailingName;
    var $firstName;
    var $nsdUnitNumber;
    var $recruiterNumber;

    public function isDirector() {
        $consultantService = new ConsultantService();
        return $consultantService->isDirector($this->number);
    }
}