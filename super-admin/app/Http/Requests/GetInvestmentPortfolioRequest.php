<?php

namespace App\Http\Requests;

class GetInvestmentPortfolioRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'userId' => ['required', 'string'],
            'memberId' => ['required', 'string'],
            'accountId' => ['required', 'string'],
        ];
    }
}
