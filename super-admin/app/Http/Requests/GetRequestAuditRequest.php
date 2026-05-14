<?php

namespace App\Http\Requests;

class GetRequestAuditRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'userId' => ['required', 'string'],
            'operationId' => ['required', 'string'],
        ];
    }
}
