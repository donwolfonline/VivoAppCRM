<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticeBoard extends Model
{
    protected $fillable = [
        'heading',
        'type',
        'department',
        'notice_detail',
        'created_by',
    ];

    public function departments()
    {
        return $this->hasOne('App\Models\Department', 'id', 'department');
    }
}
