<?php

namespace App\Http\Controllers;

use App\Anketa;
use App\Car;
use App\Company;
use App\Discount;
use App\Driver;
use App\Exports\ReportJournalExport;
use App\Models\Service;
use App\Product;
use App\Req;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportControllerContract extends Controller
{
    public $reports
        = [
            'journal'  => 'Отчет по услугам компании[Договор]',
            'graph_pv' => 'График работы пунктов выпуска',
        ];

    public function GetReport(Request $request)
    {

        $data        = $request->all();
        $isApi       = isset($_GET['api']);
        $type_report = $request->type_report;
        $indexC      = new IndexController();

        $company_fields                = $indexC->elements['Driver']['fields']['company_id'];
        $company_fields['getFieldKey'] = 'hash_id';

        $pv_fields                = $indexC->elements['Company']['fields']['pv_id'];
        $pv_fields['getFieldKey'] = 'name';
        $pv_fields['multiple']    = 1;

        $date_field     = 'date';
        $date_from      = $data['date_from'] ?? Carbon::now()->startOfYear();
        $date_to        = $data['date_to'] ?? Carbon::now();
        $date_from_time = $request->get('date_from_time', '00:00:00');
        $date_to_time   = $request->get('date_from_time', '23:59:59');

        $pv_id = $data['pv_id'] ?? [0];

        $dopData = [];

        $reports  = null;
        $reports2 = null;

        if (isset($data['filter'])) {
            $period_def = CarbonPeriod::create($date_from, $date_to)->month();
        }

        return view('pages.reports.all', [
            'title'          => $this->reports[$type_report],
            'reports'        => $reports,
            'reports2'       => $reports2,
            'company_fields' => $company_fields,
            'pv_fields'      => $pv_fields,
            'type_report'    => $type_report,
            'date_from'      => $date_from,
            'date_to'        => $date_to,
            'date_field'     => $date_field,
            'company_id'     => $data['company_id'] ?? 0,
            'pv_id'          => $data['pv_id'] ?? 0,
            'data'           => $dopData,
        ]);
    }


    public function exportJournalData(Request $request)
    {
        return Excel::download(new ReportJournalExport($this->getJournalData($request)), 'export.xlsx');
    }

    // поиск услуг для компании Отчет по услугам компании
    public function getContractsForCompany(Request $request)
    {
        $company = Anketa::with('contract')
                         ->where('company_id', $request->id)
                         ->whereNotNull('contract_id')
                         ->whereHas('contract')
                         ->groupBy('contract_id')
                         ->get()
                         ->pluck('contract')
                         ->map(function ($q) {
                             return [
                                 'name' => $q->name,
                                 'id'   => $q->id,
                             ];
                         });

        return response($company);
    }

    public function getJournalData(Request $request)
    {
        $company             = $request->company_id;
        $this->contracts_ids = $request->contracts_ids ?? [];

        if ($request->has('month')) {
            $date_from = Carbon::parse($request->month)->startOfMonth();
            $date_to   = Carbon::parse($request->month)->endOfMonth();
        } else {
            $date_from = Carbon::parse($request->date_from)->startOfDay();
            $date_to   = Carbon::parse($request->date_to)->endOfDay();
        }

        if ( !$company || !$date_to || !$date_from) {
            return response(null, 404);
        }

        $company = Company::with([
            'contracts.services',
            'contracts' => function ($q) {
                $q->whereIn('contracts.id', $this->contracts_ids);
            },
        ])->select('id', 'hash_id', 'name', 'products_id')
                          ->where('hash_id', $company)
                          ->first();

        $services  = Service::all();
        $discounts = Discount::all();

        return [
            'medics'       => $this->getJournalMedic($company, $date_from, $date_to, $services, $discounts),
            'techs'        => $this->getJournalTechs($company, $date_from, $date_to, $services, $discounts),
            'medics_other' => $this->getJournalMedicsOther($company, $date_from, $date_to, $services, $discounts),
            'techs_other'  => $this->getJournalTechsOther($company, $date_from, $date_to, $services, $discounts),
            'other'        => $this->getJournalOther($company, $services),
        ];
    }

    public function getJournalMedic($company, $date_from, $date_to, $services, $discounts)
    {
        // ->whereIn('anketas.contract_id', $this->contracts_ids)
        $medics = Anketa::whereIn('type_anketa', [
            'medic',
            'bdd',
            'report_cart',
            'pechat_pl',
        ])
                        ->whereHas('contract')
                        ->whereIn('anketas.contract_id', $this->contracts_ids)
                        ->with([// 'services_snapshot',
                                'driver',
                                'contract.services',
                        ])
                        ->where(function ($query) use ($company) {
                            $query->where('company_id', $company->hash_id)
                                  ->orWhere('company_name', $company->name);
                        })
                        ->where('in_cart', 0)
                        ->where(function ($q) use ($date_from, $date_to) {
                            $q->where(function ($q) use ($date_from, $date_to) {
                                $q->whereNotNull('date')
                                  ->whereBetween('date', [
                                      $date_from,
                                      $date_to,
                                  ]);
                            })
                              ->orWhere(function ($q) use ($date_from, $date_to) {
                                  $q->whereNull('date')->whereBetween('period_pl', [
                                      $date_from->format('Y-m'),
                                      $date_to->format('Y-m'),
                                  ]);
                              });
                        })
                        ->get();

        $result = [];

        $drivers = $medics
            ->pluck('driver')
            ->keyBy('id')
            ->values();

        $types_view = $medics
            ->pluck('type_view')
            ->unique();

        $servicesForMedics = $medics
            ->pluck('contract')
            ->pluck('services')
            ->flatten()
            ->keyBy('id')
            ->values();

        foreach ($drivers as $driver) {
//            $driver_id  = $driver->hash_id;
//            $driver_fio = $driver->fio;

            $result[$driver->hash_id]['driver_fio'] = $driver->fio;
            $result[$driver->hash_id]['pv_id']      = $medics
                ->where('car_id', $driver->hash_id)
                ->pluck('pv_id')
                ->unique()
                ->implode('; ');


            foreach ($medics
                         ->where('type_anketa', 'medic')
                         ->pluck('type_view')
                         ->unique() as $type_view
            ) {

                $total_for_type_view = $medics->where('type_view', $type_view)
                                              ->where('driver_id', $driver->hash_id)
                                              ->count();

                $result[$driver->hash_id]['types'][$type_view]['total'] = $total_for_type_view;

                $type_explode = explode('/', $type_view);

                foreach ($servicesForMedics as $service) {
                    $service->price = $service->pivot->service_cost;


                    if ($discountsForTech = $discounts->where('products_id', $service->id)) {
                        foreach ($discountsForTech as $discount) {
                            $disSum = $discount->getDiscount($total_for_type_view);
                            if ($disSum) {
                                $service->price                                            = $service->pivot->service_cost
                                                                                             - ($service->pivot->service_cost
                                                                                                * $disSum / 100);
                                $result[$driver->hash_id]['types'][$type_view]['discount'] = 1 * $disSum;
                            }
                        }
                    }
                    $result[$driver->hash_id]['types'][$type_view]['sync'] = in_array($service->id,
                        explode(',', $company->products_id));

                    $vt = $service->type_view;

                    foreach ($type_explode as $mini_type) {
                        if (strpos($vt, $mini_type) !== false) {
                            if ($service->type_product === 'Разовые осмотры') {
                                $result[$driver->hash_id]['types'][$type_view]['sum'] = $service->price
                                                                                        * $total_for_type_view;
                            } else {
                                $result[$driver->hash_id]['types'][$type_view]['sum'] = $service->price;
                            }
                        }
                    }
//                    else{
//                        if ($service->type_product === 'Разовые осмотры') {
//                            $result[$driver->hash_id]['types'][$type_view]['sum'] = $service->price * $total_for_type_view;
//                        } else {
//                            $result[$driver->hash_id]['types'][$type_view]['sum'] = $service->price;
//                        }
////                    }
                }
            }
            foreach ($medics
                         ->pluck('type_anketa')
                         ->unique() as $type_anketa
            ) {

                $total_for_type_view                                    = $medics->where('type_anketa', $type_anketa)
                                                                                 ->count();
                $result[$driver->hash_id]['types'][$type_anketa]['total'] = $total_for_type_view;

                $type_explode = explode('/', $type_anketa);

                foreach ($servicesForMedics as $service) {
                    $service->price = $service->pivot->service_cost;


                    if ($discountsForTech = $discounts->where('products_id', $service->id)) {
                        foreach ($discountsForTech as $discount) {
                            $disSum = $discount->getDiscount($total_for_type_view);
                            if ($disSum) {
                                $service->price                                            = $service->pivot->service_cost
                                                                                             - ($service->pivot->service_cost
                                                                                                * $disSum / 100);
                                $result[$driver->hash_id]['types'][$type_anketa]['discount'] = 1 * $disSum;
                            }
                        }
                    }
                    $result[$driver->hash_id]['types'][$type_anketa]['sync'] = in_array($service->id,
                        explode(',', $company->products_id));

                    if ($service->type_product === 'Разовые осмотры') {
                        $result[$driver->hash_id]['types'][$type_anketa]['sum'] = $service->price * $total_for_type_view;
                    } else {
                        $result[$driver->hash_id]['types'][$type_anketa]['sum'] = $service->price;
                    }
                }
            }

            $result[$driver->hash_id]['types']['is_dop']['total'] = $medics->where('type_anketa', 'medic')
                                                                           ->where('car_id', $driver->hash_id)
                                                                           ->where('result_dop', null)
                                                                           ->where('is_dop', 1)
                                                                           ->count();
        }

        return $result;
    }

    public function getJournalTechs($company, $date_from, $date_to, $products, $discounts)
    {
        $techs
            = Anketa::where('type_anketa', 'tech')//
                    ->whereIn('contract_id', $this->contracts_ids)
                    ->with([// 'services_snapshot',
                            'car',
                            'contract.services',
                    ])
                    ->whereHas('contract')
                    ->where(function ($query) use ($company) { // ohyenno..
                        $query->where('anketas.company_id', $company->hash_id)
                              ->orWhere('anketas.company_name', $company->name);
                    })
                    ->where('anketas.in_cart', 0)
                    ->where(function ($q) use ($date_from, $date_to) { // DATES
                        $q->where(function ($q) use ($date_from, $date_to) {
                            $q->whereNotNull('anketas.date')
                              ->whereBetween('anketas.date', [
                                  $date_from,
                                  $date_to,
                              ]);
                        })
                          ->orWhere(function ($q) use ($date_from, $date_to) {
                              $q->whereNull('anketas.date')->whereBetween('anketas.period_pl', [
                                  $date_from->format('Y-m'),
                                  $date_to->format('Y-m'),
                              ]);
                          });
                    })
                    ->get();

        $result = [];

        $cars = $techs
            ->pluck('car')
            ->keyBy('id')
            ->values();

        $types_view = $techs
            ->pluck('type_view')
            ->unique();

        $servicesForTech = $techs
            ->pluck('contract')
            ->pluck('services')
            ->flatten()
            ->keyBy('id')
            ->values();

        foreach ($techs as $tech) {
            $result[$tech->car->hash_id]['car_gos_number'] = $tech->car->gos_number;
            $result[$tech->car->hash_id]['type_auto']      = $tech->car->type_auto;
            $result[$tech->car->hash_id]['pv_id']          = $techs
                ->where('car_id', $tech->car->hash_id)
                ->pluck('pv_id')
                ->unique()
                ->implode('; ');

            foreach ($types_view as $type_view) {
                $result[$tech->car->hash_id]['types'][$type_view]['total'] = $total_for_type_view
                    = $techs->where('type_view', $type_view)
                            ->where('car_id', $tech->car->hash_id)
                            ->count();

                $type_explode = explode('/', $type_view);

                foreach ($servicesForTech as $service) {
                    $service->price = $service->pivot->service_cost;


                    if ($discountsForTech = $discounts->where('products_id', $service->id)) {
                        foreach ($discountsForTech as $discount) {
                            $disSum = $discount->getDiscount($total_for_type_view);
                            if ($disSum) {
                                $service->price                                               = $service->pivot->service_cost
                                                                                                - ($service->pivot->service_cost
                                                                                                   * $disSum / 100);
                                $result[$tech->car->hash_id]['types'][$type_view]['discount'] = 1 * $disSum;
                            }
                        }
                    }

                    $vt = $service->type_view;

                    foreach ($type_explode as $mini_type) {
                        if (strpos($vt, $mini_type) !== false) {
                            $result[$tech->car->hash_id]['types'][$type_view]['sync'] = in_array($service->id,
                                explode(',', $company->products_id));

                            if ($service->type_product === 'Разовые осмотры') {
                                $result[$tech->car->hash_id]['types'][$type_view]['sum'] = $service->price
                                                                                           * $total_for_type_view;
                            } else {
                                $result[$tech->car->hash_id]['types'][$type_view]['sum'] = $service->price;
                            }
                        }
                    }
                }
            }

            $result[$tech->car->hash_id]['types']['is_dop']['total'] = $techs->where('type_anketa', 'tech')
                                                                             ->where('car_id', $tech->car->hash_id)
                                                                             ->where('result_dop', null)
                                                                             ->where('is_dop', 1)
                                                                             ->count();
        }

        return $result;
    }

    public function getJournalMedicsOther($company, $date_from, $date_to, $products, $discounts)
    {
        $reports = Anketa::whereIn('type_anketa', ['medic', 'bdd', 'report_cart', 'pechat_pl'])
//                         ->leftJoin('drivers', 'anketas.driver_id', '=', 'drivers.hash_id')
                         ->whereIn('contract_id', $this->contracts_ids)
                         ->with([
                             'driver',
                             'contract.services',
                         ])
                         ->whereHas('contract')
                         ->where(function ($query) use ($company) {
                             $query->where('anketas.company_id', $company->hash_id)
                                   ->orWhere('anketas.company_name', $company->name);
                         })
                         ->where('in_cart', 0)
                         ->whereBetween('anketas.created_at', [
                             $date_from,
                             $date_to,
                         ])
                         ->where(function ($q) use ($date_from, $date_to) {
                             $q->where(function ($q) use ($date_from, $date_to) {
                                 $q->whereNotNull('anketas.date')
                                   ->whereNotBetween('anketas.date', [
                                       $date_from,
                                       $date_to,
                                   ]);
                             })
                               ->orWhere(function ($q) use ($date_from, $date_to) {
                                   $q->whereNull('anketas.date')->whereNotBetween('anketas.period_pl', [
                                       $date_from->format('Y-m'),
                                       $date_to->format('Y-m'),
                                   ]);
                               });
                         })
//                         ->select('driver_id', 'period_pl', 'type_view', 'driver_fio', 'date', 'is_dop', 'pv_id',
//                             'products_id', 'result_dop', 'type_anketa')
                         ->get();
//dd($reports->toArray(), $this->contracts_ids);
        $result = [];

//        $drivers_services = Driver::with(['contract', 'contract.services'])
//                                  ->whereIn('contract_id', $this->contracts_ids)
//                                  ->where('company_id', $company->id)
//                                  ->get();

        $companyProdsID = $company
            ->contracts
            ->pluck('services')
            ->flatten()
            ->pluck('id')
            ->toArray();

        foreach ($reports as $report) {
            try {
                if ($report->period_pl) {
                    $date = Carbon::parse($report->period_pl);
                } else {
                    $date = Carbon::parse($report->date);
                }
            } catch (Exception $e) {
                continue;
            }
            $key = $date->year.'-'.$date->month; // key by date

            $result[$key]['year']                                      = $date->year;
            $result[$key]['month']                                     = $date->month;
            $result[$key]['reports'][$report->driver_id]['driver_fio'] = $report->driver->fio;
            $result[$key]['reports'][$report->driver_id]['pv_id']      = implode('; ',
                array_unique($reports->where('driver_id', $report->driver_id)->pluck('pv_id')->toArray()));

            $total
                = $result[$key]['reports'][$report->driver_id]['types'][$report->type_view]['total']
                = ($result[$key]['reports'][$report->driver_id]['types'][$report->type_view]['total'] ?? 0) + 1;

            $result[$key]['reports'][$report->driver_id]['types'][$report->type_anketa]['total']
                = ($result[$key]['reports'][$report->driver_id]['types'][$report->type_anketa]['total'] ?? 0) + 1;

            if ($report->is_dop && $report->result_dop == null) {
                $result[$key]['reports'][$report->driver_id]['types']['is_dop']['total']
                    = ($result[$key]['reports'][$report->driver_id]['types']['is_dop']['total'] ?? 0) + 1;
            }

            $services = $report
                ->contract
                ->services;

            $types = explode('/', $report->type_view);

            if ($services->count() > 0) {
                foreach ($services as $service) {
                    $disc              = $discounts->where('products_id', $service->id);
                    $service->price    = $service->pivot->service_cost;
                    $service->discount = 0;

                    if ($disc->count()) {
                        foreach ($disc as $discount) {
                            $disSum = $discount->getDiscount($total);
                            if ($disSum) {
                                $service->price    = $service->pivot->service_cost - ($service->pivot->service_cost
                                                                                      * $disSum / 100);
                                $service->discount = 1 * $disSum;
                            }
                        }
                    }

                    if ($service->type_anketa === 'medic') {
                        $vt = $service->type_view;

                        foreach ($types as $type_view) {
                            if (strpos($vt, $type_view) !== false) {
                                $result[$key]['reports'][$report->driver_id]['types'][$report->type_view]['sync']
                                    = in_array($service->id, $companyProdsID);

                                if ($service->type_product === 'Разовые осмотры') {
                                    $result[$key]['reports'][$report->driver_id]['types'][$report->type_view]['sum']
                                        = $service->price * $total;
                                } else {
                                    $result[$key]['reports'][$report->driver_id]['types'][$report->type_view]['sum']
                                        = $service->price;
                                }

                                if ($service->discount) {
                                    $result[$key]['reports'][$report->driver_id]['types'][$report->type_view]['discount']
                                        = $service->discount;
                                }
                            }
                        }
                    } else {
                        if (isset($result[$key]['reports'][$report->driver_id]['types'][$service->type_anketa])) {
                            $result[$key]['reports'][$report->driver_id]['types'][$service->type_anketa]['sync']
                                = in_array($service->id, $companyProdsID);

                            if ($service->type_product === 'Разовые осмотры') {
                                $result[$key]['reports'][$report->driver_id]['types'][$service->type_anketa]['sum']
                                    = $service->price * $total;
                            } else {
                                $result[$key]['reports'][$report->driver_id]['types'][$service->type_anketa]['sum']
                                    = $service->price;
                            }

                            if ($service->discount) {
                                $result[$key]['reports'][$report->driver_id]['types'][$service->type_anketa]['discount']
                                    = $service->discount;
                            }
                        }
                    }
                }
            }
        }

        return array_reverse($result);
    }

    public function getJournalTechsOther($company, $date_from, $date_to, $products, $discounts)
    {
        $reports = Anketa::whereIn('type_anketa', ['tech', 'bdd', 'type_anketa', 'pechat_pl'])
//                         ->leftJoin('cars', 'anketas.car_id', '=', 'cars.hash_id')
                         ->whereIn('contract_id', $this->contracts_ids)
                         ->with([
                             'car',
                             'contract.services',
                         ])
                         ->whereHas('contract')
                         ->where(function ($query) use ($company) {
                             $query->where('anketas.company_id', $company->hash_id)
                                   ->orWhere('anketas.company_name', $company->name);
                         })
                         ->where('in_cart', 0)
                         ->whereBetween('anketas.created_at', [
                             $date_from,
                             $date_to,
                         ])
                         ->where(function ($q) use ($date_from, $date_to) {
                             $q->where(function ($q) use ($date_from, $date_to) {
                                 $q->whereNotNull('anketas.date')
                                   ->whereNotBetween('anketas.date', [
                                       $date_from,
                                       $date_to,
                                   ]);
                             })
                               ->orWhere(function ($q) use ($date_from, $date_to) {
                                   $q->whereNull('anketas.date')->whereNotBetween('anketas.period_pl', [
                                       $date_from->format('Y-m'),
                                       $date_to->format('Y-m'),
                                   ]);
                               });
                         })
                         ->get();

        $result = [];

        $cars_services = Car::with(['contract', 'contract.services'])
                            ->where('company_id', $company->id)
                            ->whereIn('contract_id', $this->contracts_ids)
                            ->get();


        $companyProdsID = $company
            ->contracts
            ->pluck('services')
            ->flatten()
            ->pluck('id')
            ->toArray();

        foreach ($reports as $report) {
            try {
                if ($report->period_pl) {
                    $date = Carbon::parse($report->period_pl);
                } else {
                    $date = Carbon::parse($report->date);
                }
            } catch (Exception $e) {
                continue;
            }
            $key = $date->year.'-'.$date->month; // key by date

            $result[$key]['year']                                       = $date->year;
            $result[$key]['month']                                      = $date->month;
            $result[$key]['reports'][$report->car_id]['car_gos_number'] = $report->car->gos_number;
            $result[$key]['reports'][$report->car_id]['type_auto']      = $report->car->type_auto;
            $result[$key]['reports'][$report->car_id]['pv_id']          = implode('; ',
                array_unique($reports->where('car_id', $report->car_id)->pluck('pv_id')->toArray()));

            $total = $result[$key]['reports'][$report->car_id]['types'][$report->type_view]['total']
                = ($result[$key]['reports'][$report->car_id]['types'][$report->type_view]['total'] ?? 0) + 1;

            if ($report->is_dop && $report->result_dop == null) {
                $result[$key]['reports'][$report->car_id]['types']['is_dop']['total']
                    = ($result[$key]['reports'][$report->car_id]['types']['is_dop']['total'] ?? 0) + 1;
            }

            $services = $report
                ->contract
                ->services;

//            if ($services = $cars_services
//                ->where('hash_id', $report->car_id)
//                ->first()) {
//                $services = $services
//                    ->contract
//                    ->services
////                    ->pluck('id')
////                    ->toArray()
//                ;
//            } else {
//                $services = collect();
//            }

//            if ($report->products_id == null) {
//                $services = explode(',', $company->products_id);
//            } else {
//                $services = explode(',', $report->products_id);
//            }

            $types = explode('/', $report->type_view);
//            $prods = $services;

            if ($services->count() > 0) {
                foreach ($services as $service) {
                    $disc              = $discounts->where('products_id', $service->id);
                    $service->price    = $service->pivot->service_cost;
                    $service->discount = 0;

                    if ($disc->count()) {
                        foreach ($disc as $discount) {
                            $disSum = $discount->getDiscount($total);
                            if ($disSum) {
                                $service->price    = $service->pivot->service_cost - ($service->pivot->service_cost
                                                                                      * $disSum / 100);
                                $service->discount = 1 * $disSum;
                            }
                        }
                    }

                    if ($service->type_anketa === 'tech') {
                        $vt = $service->type_view;

                        foreach ($types as $type_view) {
                            if (strpos($vt, $type_view) !== false) {
                                $result[$key]['reports'][$report->car_id]['types'][$report->type_view]['sync']
                                    = in_array($service->id, $companyProdsID);

                                if ($service->type_product === 'Разовые осмотры') {
                                    $result[$key]['reports'][$report->car_id]['types'][$report->type_view]['sum']
                                        = $service->price * $total;
                                } else {
                                    $result[$key]['reports'][$report->car_id]['types'][$report->type_view]['sum']
                                        = $service->price;
                                }

                                if ($service->discount) {
                                    $result[$key]['reports'][$report->car_id]['types'][$report->type_view]['discount']
                                        = $service->discount;
                                }
                            }
                        }
                    }
                }
            }
        }

        return array_reverse($result);
    }

    public function getJournalOther($company, $services)
    {
        $result = [];
//        $companyProdsID = explode(',', $company->products_id);

        $companyServices = $company
            ->contracts
            ->pluck('services')
            ->flatten()
//            ->pluck('id')
//            ->toArray()
        ;

        $services = $services->where('type_product', 'Абонентская плата без реестров');

        $drivers = Driver::with(['contract.services'])
                         ->whereIn('contract_id', $this->contracts_ids)
                         ->where('company_id', $company->id)
                         ->get();

        $cars = Car::with(['contract.services'])
                   ->whereIn('contract_id', $this->contracts_ids)
                   ->where('company_id', $company->id)
                   ->get();

        foreach ($companyServices->where('essence', 0) as $service) {
            $result['company'][$service->name] = $service->pivot->service_cost;
        }

        foreach ($drivers as $driver) {
//            $driverProdsID = explode(',', $driver->products_id);
            $driverProdsID = $driver->contract->services;
            foreach ($driverProdsID->whereIn('essence', [
                Product::ESSENCE_DRIVER,
                Product::ESSENCE_CAR_DRIVER,
            ]) as $service) {
                if ($service->type_product === 'Абонентская плата без реестров') {
                    $result['drivers'][] = [
                        'driver_fio' => $driver->fio,
                        'name'       => $service->name,
                        'sum'        => 1 * $service->pivot->service_cost,
                    ];
                }
            }
        }

        foreach ($cars as $car) {
//            $carProdsID = explode(',', $car->products_id);
            $carProdsID = $car->contract->services;
            foreach ($carProdsID->whereIn('essence', [2, 3]) as $service) {
                if ($service->type_product === 'Абонентская плата без реестров') {
                    $result['cars'][] = [
                        'gos_number' => $car->gos_number,
                        'type_auto'  => $car->type_auto,
                        'name'       => $service->name,
                        'sum'        => 1 * $service->pivot->service_cost,
                    ];
                }
            }
        }

        return $result;
    }

    public function ApiGetReport(Request $request)
    {
        $report = $this->GetReport($request);

        return response()->json($report);
    }

}
