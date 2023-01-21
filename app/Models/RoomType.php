<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;
    protected $table = 'room_type';
    protected $primaryKey = 'room_type_id';
    public $timestamps = false;
    protected $fillable = ['room_type_name', 'price', 'description', 'image'];
}
