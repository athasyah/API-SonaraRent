<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class InstrumentRequest extends FormRequest
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
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('type', 'instrument'),
            ],
            'brand_id' => [
                'required',
                Rule::exists('categories', 'id')->where('type', 'brand'),
            ],
            'name' => 'required|string|max:255',
            'price_per_day' => 'required|integer',
            'status' => 'required|in:available,rented,maintenance,damaged',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:20000',
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori instrumen wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid atau bukan tipe instrumen.',

            'brand_id.required' => 'Brand wajib dipilih.',
            'brand_id.exists' => 'Brand yang dipilih tidak valid atau bukan tipe brand.',

            'name.required' => 'Nama instrumen wajib diisi.',
            'name.string' => 'Nama instrumen harus berupa teks.',
            'name.max' => 'Nama instrumen maksimal 255 karakter.',

            'price_per_day.required' => 'Harga sewa per hari wajib diisi.',
            'price_per_day.integer' => 'Harga sewa per hari harus berupa angka.',

            'status.required' => 'Status instrumen wajib dipilih.',
            'status.in' => 'Status harus salah satu dari: available, rented, maintenance, atau damaged.',

            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp.',
            'image.max' => 'Ukuran gambar maksimal 20 MB.',
        ];
    }


    protected function failedValidation(Validator $validator): JsonResponse
    {
        throw new HttpResponseException(Response::Error("Kesalahan dalam validasi", $validator->errors()));
    }
}
