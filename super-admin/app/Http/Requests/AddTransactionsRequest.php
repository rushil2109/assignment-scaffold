<?php

namespace App\Http\Requests;

class AddTransactionsRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'userId' => ['required', 'string'],
            'accountId' => ['required', 'string'],
            'transactions' => ['required', 'array', 'min:1'],
            'transactions.*.effectiveDate' => ['required', 'date'],
            'transactions.*.type' => ['required', 'string'],
            'transactions.*.amount' => ['required', 'numeric'],
        ];
    }
}
