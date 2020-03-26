<?php

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
    $string = file_get_contents($input_file);
    if ($string === false)
    {
        throw new Exception('CSV: File not found.');
    }

    return theelega_read_csv_from_string($string, $strip_prices);
}

function theelega_read_csv_from_string($string, $strip_prices = false)
{
    $stream = fopen('php://memory', 'r+');
    fwrite($stream, $string);
    rewind($stream);
    $csv = [];
    while (($row = fgetcsv($stream)) !== false)
    {
        if (trim(implode('', $row)))
        {
            $csv[] = array_map('trim', $row);

            $last = count($csv) - 1;
            if (count($csv[0]) <> count($csv[$last]))
            {
                die('CSV: Rows don\'t have the same length.');
            }
        }
    }

    if (!isset($csv[0][0]))
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
    $parser = new THEELEGA_XMLSpreadsheet2003Parser($xml);
    return $parser->getWorksheetData(0, $strip_prices);
}
?>