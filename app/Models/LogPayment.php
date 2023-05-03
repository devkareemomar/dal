<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogPayment extends Model
{
    use HasFactory;
    
    protected $table="log_payments";


    protected $fillable =
    [
        'order_id',
        'payment_status',
        'payment_details',
        'payment_method',
        'updated_at',
        'created_at'

    ];
}
