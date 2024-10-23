<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook_settings extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'url',
        'method',
        'created_by',
    ];

    public static $module = [
        'New Holiday' => 'New Holiday',
        'New Meeting' => 'New Meeting',
        'Lead to Deal Conversion' => 'Lead to Deal Conversion',
        'New Estimation' => 'New Estimation',
        'New Milestone' => 'New Milestone',
        'New Support Ticket' =>'New Support Ticket',  
        'New Event' => 'New Event',
        'New Company Policy' =>'New Company Policy',  
        'New Award' => 'New Award',
        'New Project' => 'New Project',
        'New Project Status' => 'New Project Status',
        'New Invoice' => 'New Invoice',
        'Invoice Status Update' => 'Invoice Status Update',
        'New Lead' => 'New Lead',
        'New Deal' => 'New Deal',
        'New Task' => 'New Task',
        'New Payment' => 'New Payment',
        'New Contract' => 'New Contract',
    ];

    public static $method = [
        'GET' => 'GET',
        'POST' => 'POST'
    ];
}
