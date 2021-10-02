<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_payment_id',
        'payment_gateway_id',
        'vendor_payment_id',
        'payment_gateway_id',
        'status',
        'user_id',
    ];
    // Transaction
    const PENDING = 0;
    const COMPLETED = 1;



}
