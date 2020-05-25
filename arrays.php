<?php
/**
 * Get value while avoiding that stupid notice about missing keys.
 * $arr: The array.
 * $keys: An array of keys to find the value in an set of nested array.
 *      Say, if the object's address is '$arr[a][b][c], $keys would be [a, b, c]'.
 *      If $keys is not an array, it's converted to an array of one item.
 * $default: Value returned if the query fails.
*/
function theelega_arr_get($arr, $keys, $default = null)
{
    $keys = is_array($keys) ? $keys : [$keys];
    $ret = $arr;

    foreach ($keys as $k)
    {
        if (is_array($ret) && isset($ret[$k]))
        {
            $ret = $ret[$k];
        }
        elseif (is_object($ret) && isset($ret->$k))
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

function theelega_arr_last($arr)
{
    return theelega_arr_get($arr, count($arr) - 1);
}

/**
 * Scans through the values of an array (which are themselves arrays or objects) and groups them by one or more keys.
 * The result is a multiply nested array, with one dimension for each key.
 * The values are the inner arrays or objects.
 * 
 * $arr: Array to process.
 * $keys: The key or keys of the element in the inner array whose value will be the key in the new array.
 * $value: The key of the element in the inner array whose value will be added to the group.
 *      If $value is null, the entire inner array is used as value.
*/
function theelega_arr_group_by($arr, $keys, $value = null)
{
    $ret = [];
    $keys = is_array($keys) ? $keys : [$keys];

    foreach ($arr as $inner_arr)
    {
        $target_arr = &$ret; //Array to which we're adding
        foreach ($keys as $key)
        {
            $k = theelega_arr_get($inner_arr, $key);

            if (!isset($target_arr[$k]))
            {
                $target_arr[$k] = [];
            }
            $target_arr = &$target_arr[$k];
        }

        $v = $value === null ? $inner_arr : theelega_arr_get($inner_arr, $value);
        $target_arr[] = $v;
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