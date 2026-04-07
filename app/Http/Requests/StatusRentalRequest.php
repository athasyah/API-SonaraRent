<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;

class StatusRentalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|string|in:reserved,cancelled,ongoing,returned',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status rental wajib diisi.',
            'status.string'   => 'Status rental harus berupa teks.',
            'status.in'       => 'Status rental tidak valid. Pilihan yang diperbolehkan: reserved, cancelled, ongoing, atau returned.',
        ];
    }


    protected function failedValidation(Validator $validator): JsonResponse
    {
        throw new HttpResponseException(Response::Error("Kesalahan dalam validasi", $validator->errors()));
    }
}
