<?php
/**
 * Created by PhpStorm.
 * User: nik
 * Date: 21.11.2019
 * Time: 12:22
 */

namespace App\Models\Yogagraphy;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomerExcelImport implements ToModel, WithHeadingRow
{
    use Importable;
    /**
     * @param array $row
     *
     * @return CustomerExcelModel|null
     */
    public function model(array $row)
    {
        if (!isset($row[0])) {
            return null;
        }

        $customer_excel_model = new CustomerExcelModel();
        $customer_excel_model->setMailAddress($row['mail_address']);
        $customer_excel_model->setGender($row['gender']);
        $customer_excel_model->setFirstName($row['first_name']);

        return $customer_excel_model;
    }
}
