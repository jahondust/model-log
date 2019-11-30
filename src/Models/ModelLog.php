<?php


namespace Jahondust\ModelLog\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Agent\Agent;

class ModelLog extends Model
{
    protected $table = 'model_log';
    protected $fillable = ['table_name', 'row_id', 'event', 'user_id', 'before', 'after', 'ip_address', 'user_agent'];

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

    public function user()
    {
        return $this->belongsTo(config('model-log.user_model', 'App\User'), 'user_id');
    }


    public function getUserAgent()
    {
        $user_agent = [];
        $agent = new Agent();
        $agent->setUserAgent($this->user_agent);
        if ($agent->isDesktop()) {
            $user_agent['device']['icon'] = 'fa fa-desktop';
            $user_agent['device']['text'] = 'Desktop';
        } else {
            $user_agent['device']['icon'] = 'fa fa-mobile';
            $user_agent['device']['text'] = 'Mobile';
        }
        $platform = $agent->platform();
        $p_version = $agent->version($platform);
        switch (strtolower($platform)) {
            case 'windows':
                $user_agent['platform']['icon'] = 'fa fa-windows';
                break;
            case 'linux':
                $user_agent['platform']['icon'] = 'fa fa-linux';
                break;
            case 'ubuntu':
                $user_agent['platform']['icon'] = 'fa fa-linux';
                break;
            case 'androidos':
                $user_agent['platform']['icon'] = 'fa fa-android';
                break;
            case 'ios':
                $user_agent['platform']['icon'] = 'fa fa-apple';
                break;
            default:
                $user_agent['platform']['icon'] = 'fa fa-superpowers'; 
                break;
        }
        $user_agent['platform']['version'] = $platform . "  " . $p_version;
        $browser = $agent->browser();
        $b_version = $agent->version($browser);
        switch (strtolower($browser)) {
            case 'chrome':
                $user_agent['browser']['icon'] = 'fa fa-chrome';
                break;
            case 'firefox':
                $user_agent['browser']['icon'] = 'fa fa-firefox';
                break;
            case 'edge':
                $user_agent['browser']['icon'] = 'fa fa-edge';
                break;
            case 'safari':
                $user_agent['browser']['icon'] = 'fa fa-safari';
                break;
            case 'opera':
                $user_agent['browser']['icon'] = 'fa fa-opera ';
                break;
            case 'ie':
                $user_agent['browser']['icon'] = 'fa fa-internet-explorer';
                break;
            default:
                $user_agent['browser']['icon'] = 'fa fa-globe ';
                break;
        }
        $user_agent['browser']['version'] = $browser . "  " . $b_version;
        return $user_agent;
    }

    public function getType()
    {
        return $this->events[$this->event];
    }
}
