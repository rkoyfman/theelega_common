<?php
/*
    Converts an array of arrays to an associative array.
    $arr: Array to process.
    $key: The key of the element in the inner array whose value will be the key in the new array.
    $value: The key of the element in the inner array whose value will be the value in the new array.
        If $value is null, the entire inner array is used as value.
*/
function theelega_arr_to_map($arr, $key, $value = null)
{
    $ret = [];

    foreach ($arr as $inner_arr)
    {
        $ia = (array) $inner_arr;
        $k = $ia[$key];

        if (!$k && $k !== 0)
        {
            continue;
        }
        $v = $value === null ? $ia : $ia[$value];

        $ret[$k] = $v;
    }

    return $ret;
}

/*
    Get value while avoiding that stupid notice about missing keys.
*/
function theelega_arr_get($arr, $key, $default = null)
{
    $keys = is_array($key) ? $key : [$key];
    $ret = $arr;

    foreach ($keys as $k)
    {
        if (isset($ret[$k]))
        {
            $ret = $ret[$k];
        }
        elseif (isset($ret->$k))
        {
            $ret = $ret->$k;
        }
        else
        {
            return $default;
        }
    }

    return $ret;
}

/*
    Set value if not set.
*/
function theelega_arr_get_or_create(&$arr, $key, $val = null)
{
    if (!isset($arr[$key]))
    {
        $arr[$key] = $val;
    }
    return $arr[$key];
}

/*
    Make array of arrays associative, using the specified column as key.
*/
function theelega_arr_index($arr, $keycol)
{
    $ret = [];
    foreach ($arr as $arr2)
    {
        $key = theelega_arr_get($arr2, $keycol);
        $ret[$key] = $arr2;
    }

    return $ret;
}

/*
    Remove falsy items from array.
*/
function theelega_remove_falsy($arr)
{
    return array_filter($arr, function($x) {return $x;});
}
?>