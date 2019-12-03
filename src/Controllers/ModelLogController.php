<?php

namespace Jahondust\ModelLog\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Jahondust\ModelLog\Models\ModelLog;
use Illuminate\Support\Facades\File;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\Controller;

class ModelLogController extends Controller
{
    public function browse(Request $request){
        $this->authorize('browse', ModelLog::class);

        $search = (object) ['value' => $request->get('s'), 'key' => $request->get('key'), 'filter' => $request->get('filter')];
        $orderBy = $request->get('order_by', null);
        $sortOrder = $request->get('sort_order', null);
        $query = ModelLog::query();
        if($search->filter == 'contains'){
            $query->where($search->key, 'like', '%' . $search->value . '%');
        } elseif($search->filter == 'equal') {
            $query->where($search->key, '=', $search->value);
        }
        if(isset($orderBy)) {
            $query->orderBy($orderBy, isset($sortOrder) ? $sortOrder : 'ASC');
        } else {
            $query->orderBy('id', 'DESC');
        }
        $logs = $query->paginate(10);
        $headers = [
            'table_name' => __('modellog::modellog.table_name'),
            'row_id' => __('modellog::modellog.row'),
            'event' => __('modellog::modellog.event'),
            'before' => __('modellog::modellog.before'),
            'after' => __('modellog::modellog.after'),
            'ip_address' => __('modellog::modellog.user_ip'),
            'user_agent' => __('modellog::modellog.user_agent'),
            'user_id' =>__('modellog::modellog.user'),
            'created_at' => __('modellog::modellog.created_at'),
        ];

        return view('modellog::index', compact(
            'logs',
            'search',
            'headers',
            'orderBy',
            'sortOrder'
        ));
    }

    public function clear(Request $request){
        $this->authorize('clear', ModelLog::class);

        $query = ModelLog::query();
        if (isset($request->table_name)){
            $query->where('table_name', $request->get('table_name'));
        }
        if (isset($request->start_date)) $query->whereDate('created_at', '>=', $request->get('start_date'));
        if (isset($request->end_date)) $query->whereDate('created_at', '<=', $request->get('end_date'));

        $query->delete();
        return redirect()
            ->back()
            ->with([
                'message'    => __('modellog::modellog.log_cleared'),
                'alert-type' => 'success',
            ]);
    }

    public function assets(Request $request)
    {
        $path = Str::start(str_replace(['../', './'], '', urldecode($request->path)), '/');
        $path = base_path('vendor/model-log/resources/assets'.$path);
        if (File::exists($path)) {
            $mime = '';
            if (Str::endsWith($path, '.js')) {
                $mime = 'text/javascript';
            } elseif (Str::endsWith($path, '.css')) {
                $mime = 'text/css';
            } else {
                $mime = File::mimeType($path);
            }
            $response = response(File::get($path), 200, ['Content-Type' => $mime]);
            $response->setSharedMaxAge(31536000);
            $response->setMaxAge(31536000);
            $response->setExpires(new \DateTime('+1 year'));

            return $response;
        }

        return response('', 404);
    }
}
