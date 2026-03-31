<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppTaskCustomFieldValue extends Model
{
    public $timestamps = false;

    protected $table = 'opp_task_custom_field_values';

    protected $fillable = [
        'task_id',
        'custom_field_id',
        'value',
    ];

    public function task()
    {
        return $this->belongsTo(OppTask::class, 'task_id');
    }

    public function field()
    {
        return $this->belongsTo(OppCustomField::class, 'custom_field_id');
    }
}
