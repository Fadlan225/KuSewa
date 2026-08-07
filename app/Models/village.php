<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class village extends Model
{
    protected $table = 'villages';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';
}
