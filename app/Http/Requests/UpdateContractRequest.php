<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->isStaff();
    }

    public function rules()
    {
        return [
            'activation_date' => 'required|date',
            'documents' => 'nullable|array|max:5',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
            'remove_documents' => 'nullable|array',
            'remove_documents.*' => 'integer|exists:contract_documents,id',
        ];
    }

    public function messages()
    {
        return [
            'activation_date.required' => 'Tanggal aktivasi wajib diisi.',
            'activation_date.date' => 'Tanggal aktivasi harus berupa tanggal yang valid.',
            'documents.max' => 'Maksimal 5 dokumen pendukung yang dapat diunggah.',
            'documents.*.file' => 'Dokumen harus berupa file.',
            'documents.*.mimes' => 'Dokumen harus berformat PDF, JPG, JPEG, atau PNG.',
            'documents.*.max' => 'Ukuran dokumen maksimal 5 MB per file.',
        ];
    }
}
