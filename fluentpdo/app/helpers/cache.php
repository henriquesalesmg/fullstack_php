<?php
function cache_get($key, $ttl = 300) {
    $file = sys_get_temp_dir() . "/{$key}.cache.php";
    if (file_exists($file) && (filemtime($file) > (time() - $ttl))) {
        return include $file;
    }
    return false;
}

function cache_set($key, $data) {
    $file = sys_get_temp_dir() . "/{$key}.cache.php";
    file_put_contents($file, '<?php return ' . var_export($data, true) . ';');
}