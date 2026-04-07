<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;

class RentalRequest extends FormRequest
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
            'rent_date'   => 'required|date|after_or_equal:now',
            'return_date' => 'required|date|after_or_equal:rent_date',
            'payment_method' => 'required|string|in:cash,gateway',
            'is_paid' => 'nullable|boolean',
            'details' => 'required|array|min:1',
            'details.*.instrument_id' => 'required|string|exists:instruments,id',
            'is_delivery' => 'nullable|boolean',
            'delivery_address' => 'required_if:is_delivery,true|string|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'rent_date.required' => 'Tanggal sewa harus diisi.',
            'rent_date.date' => 'Tanggal sewa harus berupa format tanggal yang valid.',

            'return_date.required' => 'Tanggal pengembalian harus diisi.',
            'return_date.date' => 'Tanggal pengembalian harus berupa format tanggal yang valid.',
            'return_date.after_or_equal' => 'Tanggal pengembalian harus sama atau setelah tanggal sewa.',

            'payment_method.required' => 'Metode pembayaran harus dipilih.',
            'payment_method.string' => 'Metode pembayaran harus berupa teks.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',

            'details.required' => 'Anda harus memilih minimal 1 alat untuk disewa.',
            'details.array' => 'Detail sewa harus berupa array.',
            'details.min' => 'Anda harus memilih minimal 1 alat untuk disewa.',

            'details.*.instrument_id.required' => 'ID alat harus diisi.',
            'details.*.instrument_id.string' => 'ID alat harus berupa teks.',
            'details.*.instrument_id.exists' => 'Alat yang dipilih tidak ditemukan.',

            'details.*.day.required' => 'Jumlah hari sewa harus diisi.',
            'details.*.day.integer' => 'Jumlah hari sewa harus berupa angka.',
            'details.*.day.min' => 'Jumlah hari sewa minimal 1 hari.',
            'delivery_address.required_if' => 'Alamat pengiriman harus diisi jika memilih layanan antar.',
        ];
    }


    protected function failedValidation(Validator $validator): JsonResponse
    {
        throw new HttpResponseException(Response::Error("Kesalahan dalam validasi", $validator->errors()));
    }
}
