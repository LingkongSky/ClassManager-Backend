<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Utils\ResponseTool;
use App\Utils\RequestTool;

class Request extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $firstError = $validator->errors()->first();
        $response = ResponseTool::jsonResponse(
            1,
            $firstError,
            ['errors' => $validator->errors()->toArray()],
            422
        );
        throw new HttpResponseException($response);
    }

    public function passedValidation(): void
    {
        $input = $this->all();

        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $input[$key] = RequestTool::xxs($value);
            }
        }

        $this->replace($input);
    }
}
