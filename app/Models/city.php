<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class city extends Model
{
    protected $table = 'cities';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';
}
