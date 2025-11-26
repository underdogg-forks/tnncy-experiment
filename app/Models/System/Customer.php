<?php

namespace App\Models\System;

use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Customer extends Model
{
    use HasRoles;
    use  UsesSystemConnection;

    // Guard primary key and any sensitive attributes
    protected $guarded = ['id'];
    // Or explicitly whitelist:
    // protected $fillable = ['name', 'email', '...'];

    public function hostname()
    {
        return $this->hasOne(Hostname::class);
    }
}
