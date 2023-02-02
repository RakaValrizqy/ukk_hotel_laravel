<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'order';
    protected $primaryKey = 'order_id';
    public $timestamps = false;
    protected $timestamp = true;
    protected $fillable = ['order_number', 'customer_name', 'customer_email', 'order_date', 'check_in_date', 'check_out_date', 'guest_name', 'room_qty', 'room_type_id', 'order_status', 'user_id'];
}
