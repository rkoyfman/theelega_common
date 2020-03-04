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
    if (isset($arr[$key]))
    {
        return $arr[$key];
    }
    if (isset($arr->$key))
    {
        return $arr->$key;
    }
    return $default;
}

/*
    Set value if not set.
*/
function theelega_arr_get_or_create($arr, $key, $val)
{
    if (!isset($arr[$key]))
    {
        $arr[$key] = $val;
    }
    return $arr[$key];
}
?>