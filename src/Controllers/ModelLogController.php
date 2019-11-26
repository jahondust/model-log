<?php

namespace Jahondust\ModelLog\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jahondust\ModelLog\Models\ModelLog;
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
        }
        $logs = $query->paginate(10);

        $headers = [
            'table_name' => 'Table Name',
            'row_id' => 'Row',
            'user_id' => 'User',
            'event' => 'Event',
            'before' => 'Before',
            'after' => 'After',
            'created_at' => 'Created at',
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
                'message'    => "Model Log Cleared",
                'alert-type' => 'success',
            ]);
    }
}
