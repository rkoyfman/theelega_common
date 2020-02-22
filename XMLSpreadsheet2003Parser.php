<?php
    /*
        A class for reading Excel spreadsheets exported with the XML Spreadsheet 2003 format.
    */
    class THEELEGA_XMLSpreadsheet2003Parser
    {
        private const main_namespace = 'urn:schemas-microsoft-com:office:spreadsheet';
        private const ss_namespace = 'urn:schemas-microsoft-com:office:spreadsheet';

        /**@var DomDocument $doc */
        private $doc = null;
        private $rawData = null;

        public function __construct($xml = null)
        {
            if (!empty($xml))
            {
                $this->load($xml);
            }
        }

        public function load($xml)
        {
            $this->doc = new DOMDocument();
            $res = $this->doc->loadXML($xml);
            
            if (!$res)
            {
                $this->rawData = new WP_Error(1, 'Could not load XML.');
                return $this->rawData;
            }

            $this->doc->preserveWhiteSpace = false;
            $this->doc->formatOutput = true;

            $this->rawData = $this->getRawWorkbookData();
            return $this->rawData;
        }

        /*
            Returns the data contained in the document.

            Returned value is an indexed array of worksheets.
            A worksheet is an associative array with these properties: 
                Name: The name of the Excel tab that contains the sheet.
                Rows: See below.
            Rows is an associative array of all the rows in a worksheet.
                The key is an index. Excel skips rows with no data.
                The value is the row.
            A row is an associative array of cells.
                The key is an index. Excel skips cells with no data.
                The value is the cell.
            A cell is a string inside the Data sub-element of the Cell element,
                or '' if it's absent.
        */
        public function getRawWorkbookData()
        {
            $doc = $this->doc;
            if (!$doc)
            {
                return new WP_Error(1, 'Invalid XML.');
            }
            
            $ns = self::main_namespace;
            $ss = self::ss_namespace;

            /** @param DOMElement $c A cell in a row */
            $getCellData = function($c) use ($ns, $ss)
            {
                $nlData = $c->getElementsByTagNameNS($ns, 'Data');
                
                $ret = '';

                if ($nlData->length)
                {
                    /**@var DOMElement $data The Data element in the cell */
                    $data = $nlData->item(0);                    
                    $ret = $data->nodeValue;
                }

                return $ret;
            };

            /** @param DOMElement $r A row in a worksheet */
            $getCellsInRow = function($r) use ($ns, $ss, $getCellData)
            {
                $ret = [];
                $cells = $r->getElementsByTagNameNS($ns, 'Cell');
                $cc = $cells->count();

                $cellIndex = 0;
                for ($i = 0; $i < $cc; $i++)
                {
                    /**@var DOMElement $cell */
                    $cell = $cells->item($i);
                    $ci = intval($cell->getAttributeNS($ss, 'Index'));
                    $cellIndex = $ci ? $ci : $cellIndex + 1;
                    
                    $ret[$cellIndex] = $getCellData($cell);
                }

                return $ret;
            };

            /** @param DOMElement $w A worksheet in the document */
            $getRowsInWorksheet = function($w) use ($ns, $ss, $getCellsInRow)
            {
                $ret = [];
                $rows = $w->getElementsByTagNameNS($ns, 'Row');
                $rc = $rows->count();

                $rowIndex = 0;
                for ($i = 0; $i < $rc; $i++)
                {
                    /**@var DOMElement $row */
                    $row = $rows->item($i);
                    $ri = intval($row->getAttributeNS($ss, 'Index'));
                    $rowIndex = $ri ? $ri : $rowIndex + 1;
                    
                    $ret[$rowIndex] = $getCellsInRow($row);
                }

                return $ret;
            };

            $worksheets = $doc->getElementsByTagNameNS($ns, 'Worksheet');
            /** @param DOMElement $w */
            $worksheets = array_map(function($w) use ($ss, $getRowsInWorksheet)
            {
                $ret = [];
                $ret['Name'] = $w->getAttributeNS($ss, 'Name');
                $ret['Rows'] = $getRowsInWorksheet($w);

                return $ret;
            }, iterator_to_array($worksheets));

            return $worksheets;
        }

        /*
            Takes $this->rawData and converts it to a more useful format.

            The worksheet is the zero-based index of a worksheet in the array
            returned by getRawWorkbookData(). It can also be null, in which case you get an array 
            of all the worksheets filtered through this function.

            The first row is assumed to be the header with column names, and the rest are data.
            In the output, all rows but the first become associative arrays from the value in the header to the
            value in the corresponding cell in this row.

            If a header is empty, its column is ignored.

            The array of rows is associative, preserving the row numbers in the original Excel document.
        */
        public function getWorksheetData($worksheet, $strip_prices)
        {
            if (is_wp_error($this->rawData))
            {
                return $this->rawData;
            }

            if (!$this->rawData)
            {
                return new WP_Error(1, 'No data loaded.');
            }

            if ($worksheet === null)
            {
                $ret = [];
                foreach ($this->rawData as $i => $sheet)
                {
                    $ret[$i] = $this->getWorksheetData($i, $strip_prices);
                }
                return $ret;
            }

            if (is_int($worksheet))
            {
                $worksheet = $this->rawData[$worksheet]['Rows'];
            }

            $header = null;
            $ret = [];

            foreach ($worksheet as $rownum => $row)
            {
                if (!$header)
                {
                    $header = $row;
                    continue;
                }

                $row2 = [];
                foreach ($header as $i => $h)
                {
                    $key = trim($h);
                    if (!$key)
                    {
                        continue;
                    }

                    $value = isset($row[$i]) ? $row[$i] : '';
                    $value = trim($value);
                    
                    if ($strip_prices && theelega_string_contains(strtolower($key), 'price'))
                    {
                        $value = trim($value, '$€£¥₱');
                        $value = trim($value);
                        $value = str_replace(',', '', $value);
                    }
                    $row2[$key] = trim($value);
                }

                $ret[$rownum] = $row2;
            }

            return $ret;
        }
    }
?>