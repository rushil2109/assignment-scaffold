<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreateMemberResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'ok' => true,
            'memberId' => $this->resource['memberId'],
            'accountId' => $this->resource['accountId'],
            'operationId' => $this->resource['operationId'],
        ];
    }
}
