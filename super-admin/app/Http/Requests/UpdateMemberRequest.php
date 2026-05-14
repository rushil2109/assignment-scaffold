<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;

class UpdateMemberRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'userId' => ['required', 'string'],
            'memberId' => ['required', 'string'],
            'email' => ['sometimes', 'string', 'email'],
            'mobile' => ['sometimes', 'string'],
            'preferredName' => ['sometimes', 'string'],
            'residentialAddress' => ['sometimes', 'array'],
            'postalAddress' => ['sometimes', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $updatable = ['email', 'mobile', 'preferredName', 'residentialAddress', 'postalAddress'];
            $hasUpdatable = false;

            foreach ($updatable as $field) {
                if ($this->has($field)) {
                    $hasUpdatable = true;
                    break;
                }
            }

            if (! $hasUpdatable) {
                $validator->errors()->add('_', 'At least one updatable field is required.');
            }
        });
    }
}
