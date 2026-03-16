<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;

class InstrumentConditionRequest extends FormRequest
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
            'rental_id' => 'required|exists:rentals,id',
            'instrument_id' => 'required|exists:instruments,id',
            'condition' => 'required|in:good,minor_damage,major_damage',
            'note' => 'nullable|string|required_if:condition,minor_damage,major_damage',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5048',
            'damage_cost' => 'nullable|required_if:condition,minor_damage,major_damage|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'rental_id.required'   => 'Rental wajib dipilih.',
            'rental_id.exists'     => 'Rental tidak ditemukan.',

            'instrument_id.required' => 'Instrumen wajib dipilih.',
            'instrument_id.exists'   => 'Instrumen tidak ditemukan.',

            'condition.required' => 'Kondisi instrumen wajib diisi.',
            'condition.in'       => 'Kondisi instrumen harus berupa good, minor_damage, atau major_damage.',

            'note.string' => 'Catatan harus berupa teks.',

            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp.',
            'image.max'   => 'Ukuran gambar maksimal 5 MB.',
        ];
    }


    protected function failedValidation(Validator $validator): JsonResponse
    {
        throw new HttpResponseException(Response::Error("Kesalahan dalam validasi", $validator->errors()));
    }
}
