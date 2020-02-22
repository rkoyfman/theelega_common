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

function theelega_var_dump($obj)
{
    ob_start();
    var_dump($obj);
    $vd = ob_get_clean();
    return $vd;
}

function theelega_read_csv_from_post($field_name, $strip_prices = false)
{
    $input_file = $_FILES[$field_name]['tmp_name'];
    if (!$input_file)
    {
        throw new Exception("CSV: No input!");
    }

    return theelega_read_csv_from_file($input_file, $strip_prices);
}

function theelega_read_csv_from_file($input_file, $strip_prices = false)
{
    $file_resource = fopen($input_file, "r");
    if ($file_resource === false)
    {
        throw('CSV: File not found.');
    }

    $csv = array();
    while(true)
    {
        $row = fgetcsv($file_resource);
        if (!is_array($row))
        {
            break;
        }

        $row = array_map('trim', $row);
        if (strlen(implode('', $row)) > 0)
        {
            $csv[] = $row;
        }
    }
    fclose($file_resource);

    if (!isset($csv[0]) || !is_array($csv[0]))
    {
        die('CSV: File had no data.');
    }

    $csv_headers = $csv[0];
    $row_objects = array_map(function($row) use ($csv_headers, $strip_prices)
    {
        $obj = array();
        foreach ($row as $i => $value)
        {
            $key = $csv_headers[$i];
            $value = str_replace(chr(128), '', $value); //This symbol caused problems.
            $obj[$key] = $value;
            
            if ($strip_prices && theelega_string_contains(strtolower($key), 'price'))
            {
                $obj[$key] = trim($obj[$key], '$€£¥₱');
                $obj[$key] = trim($obj[$key]);
                $obj[$key] = str_replace(',', '', $obj[$key]);
            }
        }

        return $obj;
    }, array_slice($csv, 1));

    return $row_objects;
}

/*
    Parses the string produced by copying and pasting from an Excel document to a plain text field.
    Assumes that the first row is column headers, and that cells are separated with tabs ("\t").
    Return an array of associative arrays.
*/
function theelega_parse_excel_copypasta($excel, $strip_prices = false)
{
    //Split into lines.
    $excel = preg_split("/\r\n|\n|\r/", $excel);
    
    //Remove empty lines.
    $excel = array_filter($excel, function($line)
    {
        return strlen($line) > 0;
    });

    //Split lines into cells.
    $excel = array_map(function($line)
    {
        return explode("\t", $line); 
    }, $excel);

    if (isset($excel[0]))
    {
        //Converts each row into an associative array, with cells from the first row as keys.
        $header = array_shift($excel);
        $excel = array_map(function($line) use ($header, $strip_prices)
        {
            $ret = array();
            for ($i = 0; $i < count($header); $i++)
            {
                $key = trim($header[$i]);
                $val = trim($line[$i]);
                $ret[$key] = $val;

                if ($strip_prices && theelega_string_contains(strtolower($key), 'price'))
                {
                    $ret[$key] = trim($ret[$key], '$€£¥₱');
                    $ret[$key] = trim($ret[$key]);
                    $ret[$key] = str_replace(',', '', $ret[$key]);
                }
            }

            return $ret;
        }, $excel);

        $excel = array_filter($excel, function($row)
        {
            return strlen(trim(implode('', array_values($row))));
        });

    }

    return $excel;
}

function theelega_read_XMLSpreadsheet2003_from_post($field_name, $strip_prices = false)
{
    $input_file = $_FILES[$field_name]['tmp_name'];
    if (!$input_file)
    {
        return new WP_Error(1, "File not received!");
    }
    
    $xml = trim(file_get_contents($input_file));
    if (empty($xml))
    {
        return new WP_Error(1, 'File was empty.');
    }
    return theelega_read_XMLSpreadsheet2003($xml, $strip_prices);
}

function theelega_read_XMLSpreadsheet2003($xml, $strip_prices)
{
    require_once 'XMLSpreadsheet2003Parser.php';

    $parser = new THEELEGA_XMLSpreadsheet2003Parser($xml);
    return $parser->getWorksheetData(0, $strip_prices);
}

/*
    Obtains data from requests sent by post.js.
*/
function theelega_get_ajax_request($nonse_name)
{
    check_ajax_referer($nonse_name);
    
    $post_data = $_POST['post_data'];
    $post_data = stripslashes($post_data);
    $post_data = json_decode($post_data, true);

    return $post_data;
}

function theelega_throw_on_db_error()
{
    /** @var wpdb $wpdb */
    global $wpdb;

    $e = $wpdb->last_error;
    if ($e)
    {
        throw new Exception($e);
    }
}
?>