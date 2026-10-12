<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isPdf()
    {
        return strtolower($this->mime_type) === 'application/pdf';
    }

    public function isImage()
    {
        return strpos(strtolower($this->mime_type), 'image/') === 0;
    }

    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
