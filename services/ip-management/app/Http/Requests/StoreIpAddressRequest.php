<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreIpAddressRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ip_address' => ['required', 'ip', 'unique:ip_addresses,ip_address'],
            'label' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:255'],
        ];
    }
}
