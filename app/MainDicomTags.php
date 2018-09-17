<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MainDicomTags extends Model
{
    //
    protected $table = 'MainDicomTags';


    public function patient()
    {
        return $this->belongsTo('App\Resource','internalId','id');
    }
}
