<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $guarded = [];

    /**
     * Get all of the post's comments.
     */
    public function meta()
    {
        return $this->hasOne(Item::class);
    }
}
