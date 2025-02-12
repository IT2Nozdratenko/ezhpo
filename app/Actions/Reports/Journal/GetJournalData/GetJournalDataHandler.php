<?php

namespace App\Actions\Reports\Journal\GetJournalData;

use App\Contractcs\GetServicesReportForCompanyByPeriodInterface;
use App\Enums\FormTypeEnum;
use App\Models\Forms\Form;
use Carbon\Carbon;

class GetJournalDataHandler implements GetServicesReportForCompanyByPeriodInterface
{
    /**
     * @var \Illuminate\Support\Carbon
     */
    private $dateFrom;
    /**
     * @var \Illuminate\Support\Carbon
     */
    private $dateTo;

    private $companyHashId;

    public function handle(GetJournalDataAction $action): array
    {
        $this->dateFrom = $action->getDateFrom();
        $this->dateTo = $action->getDateTo();
        $this->companyHashId = $action->getCompanyHashId();

        return [
            'medics' => $this->getJournalMedic(),
            'techs' => $this->getJournalTechs(),
            'medics_other' => $this->getJournalMedicsOther(),
            'techs_other' => $this->getJournalTechsOther(),
            'other' => [
                'company' => [],
                'drivers' => [],
                'cars' => []
            ],
        ];
    }

    private function getJournalMedic(): array
    {
        $dateTo = $this->dateTo;
        $dateFrom = $this->dateFrom;

        $nonTechForms = Form::query()
            ->select([
                'forms.driver_id',
                'points.name as pv_id',
                'drivers.fio as driver_fio',
                'forms.type_anketa',
                'medic_forms.type_view',
                'medic_forms.result_dop',
                'medic_forms.is_dop',
                'medic_forms.admitted',
                'print_pl_forms.count_pl'
            ])
            ->whereIn('type_anketa', [
                FormTypeEnum::MEDIC,
                FormTypeEnum::BDD,
                FormTypeEnum::REPORT_CARD,
                FormTypeEnum::PRINT_PL
            ])
            ->leftJoin('points', 'points.id', '=', 'forms.point_id')
            ->leftJoin('medic_forms', 'forms.uuid', '=', 'medic_forms.forms_uuid')
            ->leftJoin('print_pl_forms', 'forms.uuid', '=', 'print_pl_forms.forms_uuid')
            ->leftJoin('drivers', 'forms.driver_id', '=', 'drivers.hash_id')
            ->where('forms.company_id', $this->companyHashId)
            ->where(function ($query) use ($dateFrom, $dateTo) {
                $query
                    ->where(function ($subQuery) use ($dateFrom, $dateTo) {
                        $subQuery->whereNotNull('print_pl_forms.period_pl')
                            ->whereBetween('print_pl_forms.period_pl', [
                                $dateFrom->format('Y-m'),
                                $dateTo->format('Y-m'),
                            ]);
                    })
                    ->orWhere(function ($subQuery) use ($dateFrom, $dateTo) {
                        $subQuery->whereNotNull('forms.date')
                            ->where('type_anketa', '!=', FormTypeEnum::PRINT_PL)
                            ->whereBetween('forms.date', [
                                $dateFrom,
                                $dateTo,
                            ]);
                    })
                    ->orWhere(function ($subQuery) use ($dateFrom, $dateTo) {
                        $subQuery->whereNull('forms.date')
                            ->whereBetween('medic_forms.period_pl', [
                                $dateFrom->format('Y-m'),
                                $dateTo->format('Y-m'),
                            ]);
                    });
            })
            ->get();

        $result = [];

        foreach ($nonTechForms->groupBy('driver_id') as $driverForms) {
            $firstDriverForm = $driverForms->first();
            $driverId = $firstDriverForm->driver_id;
            $result[$driverId]['driver_fio'] = $firstDriverForm->driver_fio;
            $result[$driverId]['pv_id'] = implode('; ', array_unique($driverForms->pluck('pv_id')->toArray()));

            $driverMedicFormsWithoutNotIdentifiedGroupedByTypeView = $driverForms
                ->where('type_anketa', FormTypeEnum::MEDIC)
                ->where('admitted', '!=', 'Не идентифицирован')
                ->groupBy(['type_view']);

            foreach ($driverMedicFormsWithoutNotIdentifiedGroupedByTypeView as $driverMedicFormsGroupedByType) {
                $formTypeView = $driverMedicFormsGroupedByType->first()->type_view;
                $total = $driverMedicFormsGroupedByType->count();

                $result[$driverId]['types'][$formTypeView]['total'] = $total;
            }

            foreach ($driverForms->groupBy(['type_anketa']) as $driverFormsGroupedByType) {
                $formTypeView = $driverFormsGroupedByType->first()->type_anketa;
                if ($formTypeView === FormTypeEnum::PRINT_PL) {
                    $total = $driverFormsGroupedByType->sum('count_pl');
                } else {
                    $total = $driverFormsGroupedByType->count();
                }

                $result[$driverId]['types'][$formTypeView]['total'] = $total;
            }

            $result[$driverId]['types']['is_dop']['total'] = $driverForms
                ->where('type_anketa', FormTypeEnum::MEDIC)
                ->where('result_dop', null)
                ->where('is_dop', 1)
                ->count();

            $result[$driverId]['types']['Не идентифицирован']['total'] = $driverForms
                ->where('type_anketa', FormTypeEnum::MEDIC)
                ->where('admitted', 'Не идентифицирован')
                ->count();
        }

        return $result;
    }

    private function getJournalTechs(): array
    {
        $dateTo = $this->dateTo;
        $dateFrom = $this->dateFrom;

        $techs = Form::query()
            ->select([
                'cars.gos_number as car_gos_number',
                'cars.type_auto',
                'forms.type_anketa',
                'points.name as pv_id',
                'tech_forms.car_id',
                'tech_forms.is_dop',
                'tech_forms.result_dop',
                'tech_forms.type_view',
                'tech_forms.car_type_auto as form_type_auto',
            ])
            ->where('type_anketa', FormTypeEnum::TECH)
            ->leftJoin('points', 'points.id', '=', 'forms.point_id')
            ->join('tech_forms', 'tech_forms.forms_uuid', '=', 'forms.uuid')
            ->leftJoin('cars', 'tech_forms.car_id', '=', 'cars.hash_id')
            ->where('forms.company_id', $this->companyHashId)
            ->where(function ($query) use ($dateFrom, $dateTo) {
                $query->where(function ($subQuery) use ($dateFrom, $dateTo) {
                    $subQuery->whereNotNull('forms.date')
                        ->whereBetween('forms.date', [
                            $dateFrom,
                            $dateTo,
                        ]);
                })->orWhere(function ($subQuery) use ($dateFrom, $dateTo) {
                    $subQuery->whereNull('forms.date')
                        ->whereBetween('tech_forms.period_pl', [
                            $dateFrom->format('Y-m'),
                            $dateTo->format('Y-m'),
                    ]);
                });
            })
            ->get();

        $result = [];

        foreach ($techs->groupBy('car_id') as $carForms) {
            $car = $carForms->first();
            $carId = $car->car_id;

            if ($carId) {
                $result[$carId]['car_gos_number'] = $car->car_gos_number;
                $result[$carId]['type_auto'] = $car->type_auto;
                $result[$carId]['pv_id'] = implode('; ', array_unique($carForms->pluck('pv_id')->toArray()));

                foreach ($carForms->groupBy(['type_view']) as $carFormsGroupedByType) {
                    $formTypeView = $carFormsGroupedByType->first()->type_view;
                    $result[$carId]['types'][$formTypeView]['total'] = $carFormsGroupedByType->count();
                }

                $result[$carId]['types']['is_dop']['total'] = $carForms
                    ->where('result_dop', null)
                    ->where('is_dop', 1)
                    ->count();
            } else {
                $result['empty_id']['car_gos_number'] = null;

                foreach ($carForms->groupBy(['form_type_auto']) as $formCarTypeGroups) {
                    $carType = $formCarTypeGroups->first()->form_type_auto;
                    if (! isset($result['empty_id']['type_auto'][$carType])) {
                        $result['empty_id']['type_auto'][$carType] = [];
                    }
                    $result['empty_id']['type_auto'][$carType]['pv_id'] = implode('; ', array_unique($carForms->pluck('pv_id')->toArray()));

                    foreach ($formCarTypeGroups->groupBy(['type_view']) as $carFormsGroupedByType) {
                        $formTypeView = $carFormsGroupedByType->first()->type_view;

                        if (! isset($result['empty_id']['type_auto'][$carType]['types'][$formTypeView])) {
                            $result['empty_id']['type_auto'][$carType]['types'][$formTypeView] = [];
                        }

                        $result['empty_id']['type_auto'][$carType]['types'][$formTypeView]['total'] = $carFormsGroupedByType->count();
                    }

                    $result['empty_id']['type_auto'][$carType]['types']['is_dop']['total'] = $formCarTypeGroups
                        ->where('result_dop', null)
                        ->where('is_dop', 1)
                        ->count();
                }
            }
        }

        return $result;
    }

    private function getJournalMedicsOther(): array
    {
        $dateTo = $this->dateTo;
        $dateFrom = $this->dateFrom;

        $forms = Form::query()
            ->select([
                'forms.date',
                'forms.driver_id',
                'points.name as pv_id',
                'drivers.fio as driver_fio',
                'forms.type_anketa',
                'medic_forms.type_view',
                'medic_forms.result_dop',
                'medic_forms.is_dop',
                'medic_forms.admitted',
            ])
            ->whereIn('type_anketa', [
                FormTypeEnum::MEDIC,
                FormTypeEnum::BDD,
                FormTypeEnum::REPORT_CARD,
                FormTypeEnum::PRINT_PL
            ])
            ->leftJoin('points', 'points.id', '=', 'forms.point_id')
            ->leftJoin('medic_forms', 'forms.uuid', '=', 'medic_forms.forms_uuid')
            ->leftJoin('drivers', 'forms.driver_id', '=', 'drivers.hash_id')
            ->leftJoin('print_pl_forms', 'forms.uuid', '=', 'print_pl_forms.forms_uuid')
            ->where('forms.company_id', $this->companyHashId)
            ->whereBetween('forms.created_at', [
                $dateFrom,
                $dateTo
            ])
            ->where(function ($query) use ($dateFrom, $dateTo) {
                $query
                    ->where(function ($subQuery) use ($dateFrom, $dateTo) {
                        $subQuery->whereNotNull('print_pl_forms.period_pl')
                            ->whereNotBetween('print_pl_forms.period_pl', [
                                $dateFrom->format('Y-m'),
                                $dateTo->format('Y-m'),
                            ]);
                    })
                    ->orWhere(function ($subQuery) use ($dateFrom, $dateTo) {
                        $subQuery->whereNotNull('forms.date')
                            ->where('type_anketa', '!=', FormTypeEnum::PRINT_PL)
                            ->whereNotBetween('forms.date', [
                                $dateFrom,
                                $dateTo,
                            ]);
                    })
                    ->orWhere(function ($query) use ($dateFrom, $dateTo) {
                        $query->whereNull('forms.date')
                            ->whereNotBetween('medic_forms.period_pl', [
                                $dateFrom->format('Y-m'),
                                $dateTo->format('Y-m'),
                            ]);
                    });
            })
            ->get();

        $result = [];

        foreach ($forms as $form) {
            if ($form->date) {
                $date = Carbon::parse($form->date);
            } else {
                $date = Carbon::parse($form->period_pl);
            }
            $key = $date->year . '-' . $date->month;

            $driverId = $form->driver_id;

            $result[$key]['year'] = $date->year;
            $result[$key]['month'] = $date->month;

            $formType = $form->type_anketa;
            $typeView = $form->type_view;

            if (!isset($result[$key]['reports'][$driverId])) {
                $points = implode('; ', array_unique($forms->where('driver_id', $driverId)->pluck('pv_id')->toArray()));
                $result[$key]['reports'][$driverId] = [
                    'driver_fio' => $form->driver_fio,
                    'pv_id' => $points,
                    'types' => [
                        'is_dop' => [
                            'total' => 0
                        ]
                    ]
                ];
            }

            if (!isset($result[$key]['reports'][$driverId]['types'][$typeView])) {
                $result[$key]['reports'][$driverId]['types'][$typeView] = [
                    'total' => 0
                ];
            }

            $result[$key]['reports'][$driverId]['types'][$typeView]['total'] += 1;

            if (!isset($result[$key]['reports'][$driverId]['types'][$formType])) {
                $result[$key]['reports'][$driverId]['types'][$formType] = [
                    'total' => 0
                ];
            }

            $result[$key]['reports'][$driverId]['types'][$formType]['total'] += 1;

            if ($form->is_dop && $form->result_dop === null) {
                $result[$key]['reports'][$driverId]['types']['is_dop']['total'] += 1;
            }
        }

        return array_reverse($result);
    }

    private function getJournalTechsOther(): array
    {
        $dateTo = $this->dateTo;
        $dateFrom = $this->dateFrom;

        $forms = Form::query()
            ->select([
                'forms.date',
                'cars.gos_number as car_gos_number',
                'cars.type_auto',
                'forms.type_anketa',
                'points.name as pv_id',
                'tech_forms.car_id',
                'tech_forms.is_dop',
                'tech_forms.result_dop',
                'tech_forms.type_view',
                'tech_forms.car_type_auto as form_type_auto',
            ])
            ->where('type_anketa', FormTypeEnum::TECH)
            ->leftJoin('points', 'points.id', '=', 'forms.point_id')
            ->join('tech_forms', 'tech_forms.forms_uuid', '=', 'forms.uuid')
            ->leftJoin('cars', 'tech_forms.car_id', '=', 'cars.hash_id')
            ->where('forms.company_id', $this->companyHashId)
            ->whereBetween('forms.created_at', [
                $dateFrom,
                $dateTo
            ])
            ->where(function ($query) use ($dateFrom, $dateTo) {
                $query
                    ->where(function ($subQuery) use ($dateFrom, $dateTo) {
                        $subQuery->whereNotNull('forms.date')
                            ->whereNotBetween('forms.date', [
                                $dateFrom,
                                $dateTo,
                            ]);
                    })->orWhere(function ($subQuery) use ($dateFrom, $dateTo) {
                        $subQuery->whereNull('forms.date')
                            ->whereNotBetween('tech_forms.period_pl', [
                                $dateFrom->format('Y-m'),
                                $dateTo->format('Y-m'),
                            ]);
                    });
            })
            ->get();

        $result = [];

        foreach ($forms as $form) {
            if ($form->date) {
                $date = Carbon::parse($form->date);
            } else {
                $date = Carbon::parse($form->period_pl);
            }
            $key = $date->year . '-' . $date->month;

            $carId = $form->car_id;

            if ($carId) {
                if (! isset($result[$key]['reports'][$carId])) {
                    $points = implode('; ', array_unique($forms->where('car_id', $carId)->pluck('pv_id')->toArray()));
                    $result[$key]['reports'][$carId] = [
                        'car_gos_number' => $form->car_gos_number,
                        'type_auto' => $form->type_auto,
                        'pv_id' => $points,
                        'types' => [
                            'is_dop' => [
                                'total' => 0
                            ]
                        ]
                    ];
                }

                $result[$key]['year'] = $date->year;
                $result[$key]['month'] = $date->month;
                $typeView = $form->type_view;

                if (!isset($result[$key]['reports'][$carId]['types'][$typeView])) {
                    $result[$key]['reports'][$carId]['types'][$typeView] = [
                        'total' => 0
                    ];
                }

                $result[$key]['reports'][$carId]['types'][$form->type_view]['total'] += 1;

                if ($form->is_dop && $form->result_dop == null) {
                    $result[$key]['reports'][$carId]['types']['is_dop']['total'] += 1;
                }
            } else {
                $carType = $form->form_type_auto;
                if (! isset($result[$key]['reports']['empty_id']['type_auto'][$carType])) {
                    $points = implode('; ', array_unique($forms->where('car_id', $carId)->pluck('pv_id')->toArray()));
                    $result[$key]['reports']['empty_id']['type_auto'][$carType] = [
                        'pv_id' => $points,
                        'types' => [
                            'is_dop' => [
                                'total' => 0
                            ]
                        ]
                    ];
                }

                $result[$key]['year'] = $date->year;
                $result[$key]['month'] = $date->month;
                $typeView = $form->type_view;

                if (!isset($result[$key]['reports']['empty_id']['type_auto'][$carType]['types'][$typeView])) {
                    $result[$key]['reports']['empty_id']['type_auto'][$carType]['types'][$typeView] = [
                        'total' => 0
                    ];
                }

                $result[$key]['reports']['empty_id']['type_auto'][$carType]['types'][$typeView]['total'] += 1;

                if ($form->is_dop && $form->result_dop == null) {
                    $result[$key]['reports']['empty_id']['type_auto'][$carType]['types']['is_dop']['total'] += 1;
                }
            }
        }

        return array_reverse($result);
    }
}
