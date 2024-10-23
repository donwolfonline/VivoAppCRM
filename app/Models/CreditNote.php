<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    protected $fillable = [
        'invoice',
        'client',
        'amount',
        'date',
    ];

    public function getClient()
    {
        return $this->hasOne('App\Models\User','id' ,'client' );
    }

    public function client()
    {
        return $this->hasOne('App\Models\User','id' ,'client' );
    }

    public static function clients($client)
    {
        
        $categoryArr  = explode(',', $client);
        $unitRate = 0;
        foreach($categoryArr as $client)
        {
            $client          = User::find($client);
            $unitRate        = $client->name;
        }

        return $unitRate;
    }
}
