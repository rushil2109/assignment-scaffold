<?php

namespace App\Http\Requests;

class MoveDayForwardRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'days' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
