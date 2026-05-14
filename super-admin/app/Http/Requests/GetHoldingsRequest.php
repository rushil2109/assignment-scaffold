<?php

namespace App\Http\Requests;

class GetHoldingsRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'userId' => ['required', 'string'],
            'memberId' => ['required', 'string'],
            'accountId' => ['required', 'string'],
            'asOfDate' => ['sometimes', 'date'],
        ];
    }
}
