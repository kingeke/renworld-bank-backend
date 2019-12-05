<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransferRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'password' => 'Required',
            'from_account' => 'Required',
            'to_account' => 'Required',
            'amount' => 'Required|Numeric',
            'bank_name' => 'nullable|required_if:to_account,others',
            'account_name' => 'nullable|required_if:to_account,others|Min:3',
            'account_number' => 'nullable|required_if:to_account,others|Numeric|digits_between:10,10',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => $validator->errors()->first()
        ], 422));
    }
}
