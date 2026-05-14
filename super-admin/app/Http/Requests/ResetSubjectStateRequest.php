<?php

namespace App\Http\Requests;

class ResetSubjectStateRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'userId' => ['required', 'string'],
        ];
    }
}
