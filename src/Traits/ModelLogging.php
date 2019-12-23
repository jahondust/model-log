<?php


namespace Jahondust\ModelLog\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jahondust\ModelLog\Models\ModelLog;

trait ModelLogging
{
    public function getLogFields(){
        return property_exists($this, 'logFields') ? $this->logFields : ['allFields'];
    }

    public function getLogEvents(){
        return property_exists($this, 'logEvents') ? $this->logEvents : ['created', 'updated', 'deleted'];
    }

    public function getUserId(){
        if(Auth::check()) {
            $user = Auth::user();
        }
        return isset($user->id) ? $user->id : 0;
    }

    public function getUserIp()
    {
        $request = Request();
        return $request->ip();
    }
    public function getUserAgent()
    {
        $request = Request();
        return $request->server('HTTP_USER_AGENT');
    }

    public static function bootModelLogging()
    {
        self::created(function($model){
            if( in_array('created', $model->getLogEvents()) ){
                $log = ModelLog::create([
                    'user_id' => $model->getUserId(),
                    'table_name' => $model->getTable(),
                    'row_id' => $model->id,
                    'ip_address' => $model->getUserIp(),
                    'user_agent' => $model->getUserAgent(),
                    'event' => 'created',
                ]);
            }
        });

        self::updating(function($model){
            if( in_array('updated', $model->getLogEvents()) ) {
                $newdatas = $model->toArray();
                $olddatas = $model->getOriginal();
                $old = [];
                $news = [];
                foreach ($olddatas as $key => $value) {
                    if ( !isset($newdatas[$key]) ) continue;
                    if ((in_array('allFields', $model->getLogFields()) || in_array($key, $model->getLogFields())) && md5($value) != md5($newdatas[$key])) {
                        $old[$key] = $value;
                        $news[$key] = $newdatas[$key];
                    }
                }
                if (count($news) > 0) {
                    $log = ModelLog::create([
                        'user_id' => $model->getUserId(),
                        'table_name' => $model->getTable(),
                        'row_id' => $model->id,
                        'ip_address' => $model->getUserIp(),
                        'user_agent' => $model->getUserAgent(),
                        'event' => 'updated',
                        'after' => json_encode($news),
                        'before' => json_encode($old),
                    ]);
                }
            }
        });

        self::deleted(function($model){
            if( in_array('deleted', $model->getLogEvents()) ) {
                $log = ModelLog::create([
                    'user_id' => $model->getUserId(),
                    'table_name' => $model->getTable(),
                    'row_id' => $model->id,
                    'ip_address' => $model->getUserIp(),
                    'user_agent' => $model->getUserAgent(),
                    'event' => 'deleted',
                ]);
            }
        });
    }
}
