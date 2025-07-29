<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReceipt extends Model
{
    protected $fillable = [
        'order_id',
        'receipt_number',
        'file_path',
        'file_type',
        'receipt_data',
        'is_generated',
        'generated_at',
        'metadata',
    ];

    protected $casts = [
        'receipt_data' => 'array',
        'metadata' => 'array',
        'is_generated' => 'boolean',
        'generated_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if ($this->file_path && $this->is_generated) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }

    public function getFormattedReceiptNumberAttribute(): string
    {
        return 'RCP-' . str_pad($this->id, 8, '0', STR_PAD_LEFT);
    }
}
