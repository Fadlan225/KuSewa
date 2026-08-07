<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class district extends Model
{
    protected $table = 'districts';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';
}
