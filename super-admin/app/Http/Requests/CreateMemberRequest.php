<?php

namespace App\Http\Requests;

use App\Rules\ValidAllocations;

class CreateMemberRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'userId' => ['required', 'string'],
            'firstName' => ['required', 'string'],
            'lastName' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'mobile' => ['required', 'string'],
            'dateOfBirth' => ['required', 'date'],
            'initialInvestmentProfile' => ['required', 'array', new ValidAllocations],
        ];
    }
}
