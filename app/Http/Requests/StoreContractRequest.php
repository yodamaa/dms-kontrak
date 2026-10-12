<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->isStaff();
    }

    public function rules()
    {
        return [
            'activation_date' => 'required|date',
            'documents' => 'required|array|min:1|max:5',
            'documents.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
        ];
    }

    public function messages()
    {
        return [
            'activation_date.required' => 'Tanggal aktivasi wajib diisi.',
            'activation_date.date' => 'Tanggal aktivasi harus berupa tanggal yang valid.',
            'documents.required' => 'Dokumen pendukung wajib diunggah.',
            'documents.min' => 'Minimal 1 dokumen pendukung harus diunggah.',
            'documents.max' => 'Maksimal 5 dokumen pendukung yang dapat diunggah.',
            'documents.*.required' => 'File dokumen wajib diisi.',
            'documents.*.file' => 'Dokumen harus berupa file.',
            'documents.*.mimes' => 'Dokumen harus berformat PDF, JPG, JPEG, atau PNG.',
            'documents.*.max' => 'Ukuran dokumen maksimal 5 MB per file.',
        ];
    }
}
