<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetRelease extends Model
{
    protected $guarded = [];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function treasurer()
    {
        return $this->belongsTo(User::class, 'released_by');
    }
}
