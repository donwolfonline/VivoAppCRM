<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectNote extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'created_by',
    ];
}
