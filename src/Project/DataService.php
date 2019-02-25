<?php

namespace Project;

use MeekroDBException;

class DataService extends Service {

    public function getUnitData($unitNumber) {
        $result = array();
        try {
            if ($data = $this->dbConnection->query('SELECT c.ConsultantNumber, c.ConsultantName, p.CountPhoto, 
                IF(ISNULL(d.ConsultantNumber), 0, 1) as Purchaser  
                FROM '.Config::CONSULTANT_TABLE.' c
                INNER JOIN (
                    SELECT ConsultantNumber, count(Id) as CountPhoto
                    FROM '.Config::FILE_TABLE.'
                    WHERE PhotoApproved=1
                    GROUP BY ConsultantNumber
                ) p ON c.ConsultantNumber=p.ConsultantNumber
                LEFT JOIN '.Config::DATA_TABLE.' d ON c.ConsultantNumber=d.ConsultantNumber
                WHERE c.UnitNumber=%s
                ORDER BY c.ConsultantName', $unitNumber)) {
                foreach ($data as $record) {
                    $unitRecord = new UnitRecord();
                    $unitRecord->consultantNumber = $record['ConsultantNumber'];
                    $unitRecord->consultantName = $record['ConsultantName'];
                    $unitRecord->countPhoto = $record['CountPhoto'];
                    $unitRecord->isPurchaser = $record['Purchaser'];
                    $result[] = $unitRecord;
                }
            }
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get unit data', ['exception' => $e]);
            throw new ServiceException('Cannot get unit data');
        }
        return $result;
    }

}