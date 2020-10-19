<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $guarded = [];
    use HasFactory;

    public function voucher(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function offers()
    {
        return $this->hasManyThrough(Offer::class, Voucher::class);
    }
}
