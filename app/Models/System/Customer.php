<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;
use Spatie\Permission\Traits\HasRoles;

class Customer extends Model
{
    use  UsesSystemConnection, HasRoles;

    // Guard primary key and any sensitive attributes
    protected $guarded = ['id'];
    // Or explicitly whitelist:
    // protected $fillable = ['name', 'email', '...'];
    
    public function hostname()
    {
        return $this->hasOne(Hostname::class);
    }
}
