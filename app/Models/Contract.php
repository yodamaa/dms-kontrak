<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_number',
        'activation_date',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejection_reason',
        'decided_at',
    ];

    protected $casts = [
        'activation_date' => 'date',
        'approved_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    /**
     * Generate a unique contract number with format INV/YYYY/MM/NNNN.
     * Uses database locking to prevent duplicates under concurrent requests.
     */
    public static function generateContractNumber()
    {
        $prefix = config('app.contract_prefix', 'INV');
        $year = date('Y');
        $month = date('m');
        $pattern = "{$prefix}/{$year}/{$month}/";

        // Use database-level locking to ensure uniqueness
        $lastContract = static::withTrashed()
            ->where('contract_number', 'like', $pattern . '%')
            ->orderBy('contract_number', 'desc')
            ->lockForUpdate()
            ->first();

        if ($lastContract) {
            $lastNumber = (int) substr($lastContract->contract_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $pattern . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if the contract can be edited.
     */
    public function canBeEdited()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the contract can be decided (approved/rejected).
     */
    public function canBeDecided()
    {
        return $this->status === 'pending';
    }

    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'approved' => 'badge-approved',
            'rejected' => 'badge-rejected',
            'pending' => 'badge-pending',
        ];
        return $classes[$this->status] ?? 'badge-secondary';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'pending' => 'Pending',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    // Relationships

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function documents()
    {
        return $this->hasMany(ContractDocument::class);
    }

    public function activities()
    {
        return $this->hasMany(ContractActivity::class);
    }
}
