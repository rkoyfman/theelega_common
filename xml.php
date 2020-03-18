<?php
/**
 * Converts a string of XML into a DOMDocument. Throws an exception on failure.
 * @param string $xml
 * @return DOMDocument
 */
function theelega_load_xml($xml)
{
    $doc = new DOMDocument();

    $doc->preserveWhiteSpace = false;
    $doc->formatOutput = true;

    $res = $doc->loadXML($xml);
    if (!$res)
    {
        throw new Exception('Could not load XML.');
    }
    return $doc;
}
?>