<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ViewBookingTransactionRequest extends FormRequest
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
            'booking_trx_id' => 'required|exists:booking_transactions,booking_trx_id',
            'phone_number' => 'required|numeric',
        ];
    }
}
