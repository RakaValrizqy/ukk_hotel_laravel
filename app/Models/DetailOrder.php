<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailOrder extends Model
{
    use HasFactory;
    protected $table = 'detail_order';
    protected $primaryKey = 'detail_order_id';
    public $timestamps = false;
    protected $fillable = ['order_id', 'room_id', 'access_date', 'price'];
}
