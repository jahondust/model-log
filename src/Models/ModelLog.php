<?php


namespace Jahondust\ModelLog\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class ModelLog extends Model
{
    protected $table = 'model_log';
    protected $fillable = ['table_name', 'row_id', 'event', 'user_id', 'before', 'after'];

    protected $events = [
        'created' => [
            'title' => 'Created',
            'class' => 'label-success'
        ],
        'updated' => [
            'title' => 'Updated',
            'class' => 'label-primary'
        ],
        'deleted' => [
            'title' => 'Deleted',
            'class' => 'label-danger'
        ],
    ];

    public function user(){
        return $this->belongsTo(config('model-log.user_model', 'App\User'), 'user_id');
    }

    public function getType(){
        return $this->events[$this->event];
    }
}
