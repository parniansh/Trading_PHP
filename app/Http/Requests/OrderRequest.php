<?php

namespace App\Http\Requests;

use App\Helpers\SerializeValidationErrorResponseHelper;
use App\Http\Resources\ErrorResource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;


class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'amount' => ['required'],
            'orderType' => ['required']
            //
        ];
    }

    protected function failedValidation(Validator $validator)
    {
            $error =  new ErrorResource((object)[
                'error' => __('validation.RequestValidation'),
                'message' => (new SerializeValidationErrorResponseHelper((object)$validator->errors()))->result,
            ]);
            throw new HttpResponseException(response()->json($error,422));
    }
}
