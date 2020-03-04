<?php
function theelega_request_field($fieldname)
{
    if (isset($_GET[$fieldname]))
    {
        return $_GET[$fieldname];
    }
    if (isset($_POST[$fieldname]))
    {
        return $_POST[$fieldname];
    }

    return null;
}
?>