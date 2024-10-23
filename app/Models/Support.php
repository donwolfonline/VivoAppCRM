<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
    protected $fillable = [
        'subject',
        'user',
        'priority',
        'end_date',
        'ticket_code',
        'ticket_created',
        'status',
        'created_by',
    ];

    public static $priority = [
        'Low',
        'Medium',
        'High',
        'Critical',
    ];

    public function createdBy()
    {
        return $this->hasOne('App\Models\user', 'id', 'ticket_created');
    }

    public static $status = [
        'Open',
        'Close',
        'On Hold',
    ];

    public function replyUnread()
    {

        if(\Auth::user()->type == 'employee')
        {
            return SupportReply:: where('support_id', $this->id)->where('is_read', 0)->where('user', '!=', \Auth::user()->id)->count('id');
        }
        else
        {
            return SupportReply:: where('support_id', $this->id)->where('is_read', 0)->count('id');
        }
    }
}
