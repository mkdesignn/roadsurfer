<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemMeta extends Model
{
    protected $guarded = [];

    protected $table = 'item_meta';

    /**
     * Get all of the post's comments.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
