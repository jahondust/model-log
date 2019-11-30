<?php

if (!function_exists('modallog_asset')) {
    function modallog_asset($path, $secure = null)
    {
        return route('voyager.model_log.assets').'?path='.urlencode($path);
    }
}

