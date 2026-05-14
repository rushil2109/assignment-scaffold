<?php

namespace App\Http\Requests;

class SetDailyUnitPricesRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'prices' => ['required', 'array', 'min:1'],
            'prices.*.assetCode' => ['required', 'string', 'in:Cash,Conservative,Balanced,Growth,HighGrowth'],
            'prices.*.unitPrice' => ['required', 'numeric', 'gt:0'],
        ];
    }
}
