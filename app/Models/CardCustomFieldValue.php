<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardCustomFieldValue extends Model
{
    protected $fillable = ['card_id', 'custom_field_id', 'value'];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function customField()
    {
        return $this->belongsTo(CustomField::class);
    }
}
