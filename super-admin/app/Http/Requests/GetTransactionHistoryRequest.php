<?php

namespace App\Http\Requests;

class GetTransactionHistoryRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'userId' => ['required', 'string'],
            'memberId' => ['required', 'string'],
            'accountId' => ['required', 'string'],
            'fromDate' => ['sometimes', 'date'],
            'toDate' => ['sometimes', 'date'],
        ];
    }
}
