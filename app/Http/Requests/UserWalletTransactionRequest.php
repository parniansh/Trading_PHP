<?php

namespace App\Http\Requests;

use App\Helpers\SerializeValidationErrorResponseHelper;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Resources\ErrorResource;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UserWalletTransactionRequest extends FormRequest
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
            'transKind' => ['required', 'regex:/^[0-1]+/'],
            'tokenType' => ['required', 'regex:/^[0-1]+/'],
            'amount' => ['required']
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
