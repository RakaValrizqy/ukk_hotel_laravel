<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    use HasFactory;

    protected $table = 'user';
    protected $primaryKey = 'user_id';
    public $timestamps = false;
    protected $fillable = ['user_name', 'image', 'email', 'password', 'role'];
}
