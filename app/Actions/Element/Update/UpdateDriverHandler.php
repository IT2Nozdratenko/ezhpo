<?php

namespace App\Actions\Element\Update;

use Exception;

class UpdateDriverHandler extends UpdateElementHandler
{
    /**
     * @throws Exception
     */
    public function handle($id, array $data)
    {
        $this->setData($data);
        $this->findElement($id);
        $this->validatePhone($data['phone']);
        $this->wrapNullFieldsToEmptyString();
        $this->updateFiles();
        $this->updateFields();
        $this->syncCompanyFields();
        $this->resetEmptyFields();
        $this->element->save();
        $this->attachContracts();
    }

    private function validatePhone(string $phone = null)
    {
        if ($phone !== null && preg_match("/^[+\-\d\s]+$/", $phone) !== 1) {
            throw new Exception('Ошибка! Неправильный формат телефона: '.$phone);
        }
    }
}
