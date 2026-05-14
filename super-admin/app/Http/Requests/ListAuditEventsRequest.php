<?php

namespace App\Http\Requests;

class ListAuditEventsRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'userId' => ['required', 'string'],
        ];
    }
}
