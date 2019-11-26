<?php


namespace Jahondust\ModelLog\Traits;

use Illuminate\Support\Facades\Auth;
use Jahondust\ModelLog\Models\ModelLog;

trait ModelLogging
{
    public function getLogFields(){
        return property_exists($this, 'logFields') ? $this->logFields : ['allFields'];
    }

    public function getUserId(){
        if(Auth::check()) {
            $user = Auth::user();
        }
        return isset($user->id) ? $user->id : 0;
    }

    public static function bootModelLogging()
    {
        self::created(function($model){
            $log = ModelLog::create([
                'user_id' => $model->getUserId(),
                'table_name' => $model->getTable(),
                'row_id' => $model->id,
                'event' => 'created',
            ]);
        });

        self::updating(function($model){
            $newdatas = $model->toArray();
            $olddatas = $model->getOriginal();
            $old = []; $news = [];
            foreach ($olddatas as $key => $value) {
                if( (in_array('allFields', $model->getLogFields()) || in_array($key, $model->getLogFields())) && $value != $newdatas[$key] ){
                    $old[$key] = $value;
                    $news[$key] = $newdatas[$key];
                }
            }
            if(count($news) > 0){
                $log = ModelLog::create([
                    'user_id' => $model->getUserId(),
                    'table_name' => $model->getTable(),
                    'row_id' => $model->id,
                    'event' => 'updated',
                    'after' => json_encode($news),
                    'before' => json_encode($old),
                ]);
            }
        });

        self::deleted(function($model){
            $log = ModelLog::create([
                'user_id' => $model->getUserId(),
                'table_name' => $model->getTable(),
                'row_id' => $model->id,
                'event' => 'deleted',
            ]);
        });
    }
}
