<?php

namespace App\Contracts;

interface AdminApiInterface
{
    public function createMember(string $adminId, array $data): array;

    public function updateMember(string $adminId, array $data): array;

    public function setInvestmentProfile(string $adminId, array $allocations): array;

    public function getInvestmentPortfolio(string $adminId): array;

    public function getTransactionHistory(string $adminId, ?string $fromDate = null, ?string $toDate = null): array;

    public function getHoldings(string $adminId, ?string $asOfDate = null): array;
}
