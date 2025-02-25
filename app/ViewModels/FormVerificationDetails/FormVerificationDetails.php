<?php

namespace App\ViewModels\FormVerificationDetails;

use App\Enums\FormLabelingType;
use Carbon\Carbon;

final class FormVerificationDetails
{
    /**
     * @var bool
     */
    private $verified;
    /**
     * @var string
     */
    private $formUuid;
    /**
     * @var string
     */
    private $formId;
    /**
     * @var FormLabelingType
     */
    private $formType;
    /**
     * @var string|null
     */
    private $formNumber;
    /**
     * @var string|null
     */
    private $companyName;
    /**
     * @var Carbon|null
     */
    private $formDate;
    /**
     * @var Carbon|null
     */
    private $formPeriod;
    /**
     * @var string|null
     */
    private $driverName;
    /**
     * @var string|null
     */
    private $carGosNumber;
    /**
     * @var TripTicketDetails|null
     */
    private $tripTicketDetails;

    /**
     * @param bool $verified
     * @param string $formUuid
     * @param string $formId
     * @param FormLabelingType $formType
     * @param string|null $formNumber
     * @param string|null $companyName
     * @param Carbon|null $formDate
     * @param Carbon|null $formPeriod
     * @param string|null $driverName
     * @param string|null $carGosNumber
     * @param TripTicketDetails|null $tripTicketDetails
     */
    public function __construct(
        bool               $verified,
        string             $formUuid,
        string             $formId,
        FormLabelingType   $formType,
        ?string            $formNumber,
        ?string            $companyName,
        ?Carbon            $formDate,
        ?Carbon            $formPeriod,
        ?string            $driverName,
        ?string            $carGosNumber,
        ?TripTicketDetails $tripTicketDetails
    )
    {
        $this->verified = $verified;
        $this->formUuid = $formUuid;
        $this->formId = $formId;
        $this->formType = $formType;
        $this->formNumber = $formNumber;
        $this->companyName = $companyName;
        $this->formDate = $formDate;
        $this->formPeriod = $formPeriod;
        $this->driverName = $driverName;
        $this->carGosNumber = $carGosNumber;
        $this->tripTicketDetails = $tripTicketDetails;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function getFormUuid(): string
    {
        return $this->formUuid;
    }

    public function getFormId(): string
    {
        return $this->formId;
    }

    public function getFormType(): FormLabelingType
    {
        return $this->formType;
    }

    public function getFormNumber(): ?string
    {
        return $this->formNumber;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function getFormDate(): ?Carbon
    {
        return $this->formDate;
    }

    public function getFormPeriod(): ?Carbon
    {
        return $this->formPeriod;
    }

    public function getTripTicketDetails(): ?TripTicketDetails
    {
        return $this->tripTicketDetails;
    }

    public function getFormattedFormPeriod(): ?string
    {
        if (!$this->formPeriod) {
            return '';
        }

        $months = [
            '1' => 'Январь',
            '2' => 'Февраль',
            '3' => 'Март',
            '4' => 'Апрель',
            '5' => 'Май',
            '6' => 'Июнь',
            '7' => 'Июль',
            '8' => 'Август',
            '9' => 'Сентябрь',
            '10' => 'Октябрь',
            '11' => 'Ноябрь',
            '12' => 'Декабрь',
        ];

        if (array_key_exists($this->formPeriod->month, $months)) {
            return $months[$this->formPeriod->month] . ' ' . $this->formPeriod->year;
        }

        return '';
    }

    public function getDriverName(): ?string
    {
        return $this->driverName;
    }

    public function getCarGosNumber(): ?string
    {
        return $this->carGosNumber;
    }
}
