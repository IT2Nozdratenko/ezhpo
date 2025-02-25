<?php

namespace App\Services\CompanyReqsChecker;

use App\Company;
use App\ValueObjects\CompanyReqs;

class CompanyRepository
{
    public function findByReqs(CompanyReqs $companyReqs, $excludeId = null): ?Company
    {
        /** @var Company|null $company */
        $company = Company::query()
            ->when($excludeId, function ($query) use ($excludeId) {
                $query->where('id', '!=', $excludeId);
            })
            ->where(function ($query) use ($companyReqs) {
                $query
                    ->when($companyReqs->getOgrn(), function ($query) use ($companyReqs) {
                        $query->where('ogrn', $companyReqs->getOgrn());
                    })
                    ->orWhere(function ($subQuery) use ($companyReqs) {
                        $subQuery
                            ->where('inn', $companyReqs->getInn())
                            ->when($companyReqs->getKpp(), function ($query) use ($companyReqs) {
                                $query->where('kpp', $companyReqs->getKpp());
                            });
                    });
            })
            ->first();

        return $company;
    }
}
