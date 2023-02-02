<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    protected $table = 'room';
    protected $primaryKey = 'room_id';
    public $timestamps = false;
    protected $fillable = ['room_number', 'room_type_id'];
}
