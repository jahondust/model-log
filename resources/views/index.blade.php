@extends('voyager::master')
@section('css')
    <link rel="stylesheet" href="{{ modallog_asset('css/main.css') }} ">
    <link rel="stylesheet" href="{{ modallog_asset('css/font-awesome.css') }} ">
@endsection
@section('page_title', __('modellog::modellog.model_logs'))

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-logbook"></i> {{ __('modellog::modellog.model_logs') }}
        </h1>
        @can('clear', app('Jahondust\ModelLog\Models\ModelLog'))
            @include('modellog::partials.clear')
        @endcan
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <form method="get" class="form-search">
                            <div id="search-input">
                                <div class="col-2">
                                    <select id="search_key" name="key">
                                        @foreach($headers as $key => $name)
                                            <option value="{{ $key }}" @if($search->key == $key){{ 'selected' }}@endif>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <select id="filter" name="filter">
                                        <option value="contains" @if($search->filter == "contains"){{ 'selected' }}@endif>{{ __('modellog::modellog.contains') }}</option>
                                        <option value="equals" @if($search->filter == "equals"){{ 'selected' }}@endif>=</option>
                                    </select>
                                </div>
                                <div class="input-group col-md-12">
                                    <input type="text" class="form-control" placeholder="{{ __('voyager::generic.search') }}" name="s" value="{{ $search->value }}">
                                    <span class="input-group-btn">
                                        <button class="btn btn-info btn-lg" type="submit">
                                            <i class="voyager-search"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                            @if (request()->has('sort_order') && request()->has('order_by'))
                                <input type="hidden" name="sort_order" value="{{ request()->get('sort_order') }}">
                                <input type="hidden" name="order_by" value="{{ request()->get('order_by') }}">
                            @endif
                        </form>
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-hover">
                                <thead>
                                <tr>
                                    @foreach($headers as $field => $name)
                                        @php
                                            $params['order_by'] = $field;
                                            $params['sort_order'] = ($orderBy == $field && $sortOrder == 'asc') ? 'desc' : 'asc';
                                        @endphp
                                        <th>
                                            <a href="{{ url()->current().'?'.http_build_query(array_merge(request()->all(), $params)) }}">

                                                {{ $name }}
                                                    @if ($orderBy == $field)
                                                        @if ($sortOrder == 'asc')
                                                            <i class="voyager-angle-up pull-right"></i>
                                                        @else
                                                            <i class="voyager-angle-down pull-right"></i>
                                                        @endif
                                                    @endif
                                            </a>
                                        </th>
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        @foreach($headers as $field => $name)
                                            <td>
                                                @if($field == 'table_name')
                                                    <h5 align="center">{{ $log->{$field} }}</h5>
                                                @elseif($field == 'user_id')
                                                    <i>{{ (isset($log->user->log_name) ? $log->user->log_name : isset($log->user->name)) ? $log->user->name : '' }}</i>
                                                @elseif($field == 'event')
                                                    <div class="primary"><span class="label {{ $log->getType()['class'] }}">{{ $log->getType()['title'] }}</span></div>
                                                @elseif($field == 'before' || $field == 'after')
                                                    @foreach( (array) json_decode($log->$field) as $key => $value )
                                                        <div class="primary"><span class="label label-default">{{ $key }}:</span> {{ $value }}</div>
                                                    @endforeach
                                                @elseif($field == 'user_agent')
                                                    <div class="col-lg-6 user_agent">
                                                        @if( !empty($log->user_agent) )
                                                            <i class="{{ $log->getUserAgent()['device']['icon'] }}"   style=" {{ $log->getUserAgent()['device']['text'] == 'Mobile' ? 'font-size:25px': ""  }}" data-toggle="tooltip" data-placement="top" title="{{ $log->getUserAgent()['device']['text'] }}"></i>
                                                            <i class="{{ $log->getUserAgent()['platform']['icon'] }}" style="color: #62a8ea;" data-toggle="tooltip" data-placement="top" title="{{ $log->getUserAgent()['platform']['version'] }}"></i>
                                                            <i class="{{ $log->getUserAgent()['browser']['icon'] }}" style="color: #0275d8;" data-toggle="tooltip" data-placement="top" title="{{ $log->getUserAgent()['browser']['version'] }}"></i>
                                                        @endif
                                                    </div>
                                                @else
                                                    {{ $log->{$field} }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="pull-left">
                            <div role="status" class="show-res" aria-live="polite">{{ trans_choice(
                                'voyager::generic.showing_entries', $logs->total(), [
                                    'from' => $logs->firstItem(),
                                    'to' => $logs->lastItem(),
                                    'all' => $logs->total()
                                ]) }}</div>
                        </div>
                        <div class="pull-right">
                            {{ $logs->appends([
                                's' => $search->value,
                                'filter' => $search->filter,
                                'key' => $search->key,
                                'order_by' => $orderBy,
                                'sort_order' => $sortOrder,
                            ])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('javascript')
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();
            $('#search-input select').select2({
                minimumResultsForSearch: Infinity
            });
        });
    </script>
@stop

