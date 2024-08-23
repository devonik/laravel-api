<?php
/**
 * Created by PhpStorm.
 * User: nik
 * Date: 21.11.2019
 * Time: 12:10
 */

namespace App\Models\Yogagraphy;

class CustomerExcelModel
{
    private $mailAddress;
    private $gender;
    private $firstName;

    /**
     * @return mixed
     */
    public function getMailAddress()
    {
        return $this->mailAddress;
    }

    /**
     * @param mixed $mailAddress
     */
    public function setMailAddress($mailAddress): void
    {
        $this->mailAddress = $mailAddress;
    }



    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender): void
    {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }




}
