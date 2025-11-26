<?php

namespace App\Models\System;

use Hyn\Tenancy\Contracts\Hostname as HostNameContract;
use Hyn\Tenancy\Models\Website;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hostname extends Model implements HostNameContract
{
    /**
     *  Relationships.
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
