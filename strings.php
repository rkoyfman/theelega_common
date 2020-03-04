<?php
function theelega_string_startswith($haystack, $needle)
{
    $position = strpos($haystack, $needle);
    return $position === 0;
}

function theelega_string_endswith($haystack, $needle)
{
    $hlen = strlen($haystack);
    $nlen = strlen($needle);
    $position = strpos($haystack, $needle);
    return $position === ($hlen - $nlen);
}

function theelega_string_contains($haystack, $needle)
{
    $position = strpos($haystack, $needle);
    return $position !== false;
}
?>