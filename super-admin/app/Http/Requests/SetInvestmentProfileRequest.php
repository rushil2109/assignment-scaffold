<?php

namespace App\Http\Requests;

use App\Rules\ValidAllocations;

class SetInvestmentProfileRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'userId' => ['required', 'string'],
            'memberId' => ['required', 'string'],
            'accountId' => ['required', 'string'],
            'allocations' => ['required', 'array', new ValidAllocations],
        ];
    }
}
