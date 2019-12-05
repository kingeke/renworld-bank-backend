<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\{Validator};

trait CustomTraits
{
    /**
     * Validate data based on rules
     * 
     * @param array $data
     * 
     * @param array $rules
     * 
     * @return mixed
     */
    public function validation($data, $rules)
    {

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {

            return [
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'code' => 422
            ];
        }

        return true;
    }

    /**
     * Json response codes
     * 
     * @param string $type
     * 
     * @param string $message
     * 
     * @param int $code
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function responseCodes($type, $message, $code = 200)
    {
        return response()->json([
            'status' => $type,
            'message' => $message
        ], $code);
    }

    /**
     * Validation error
     * 
     * @param array $validation
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function validatorError($validation)
    {
        return $this->responseCodes($validation['status'], $validation['message'], $validation['code']);
    }

    /**
     * Parse number
     * 
     * @param string $number
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function formatNumber($number)
    {
        return 'N' . number_format($number);
    }

    /**
     * Not found error
     * 
     * @param string $title
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function notFound($title)
    {
        return $this->responseCodes('error', "$title not found", 404);
    }

    /**
     * Random number generator
     * 
     * @param int $length
     * 
     * @return string
     */
    public function generateRandomNumber($length)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789', ceil($length / strlen($x)))), 1, $length);
    }

    /**
     * Random character generator
     * 
     * @param int $length
     * 
     * @return string
     */
    public function generateRandomCharacter($length)
    {
        return substr(str_shuffle(str_repeat($x = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }
}
