<?php
class THEELEGA_XMLElement
{
    /** @var string $name The element's name. */
    public $name = '';
    
    /** @var (THEELEGA_XMLElement|string)[] $children
     * An array of the element's child elements. 
     * Strings are added as text nodes.
    */
    public $children = [];

    /** @var string[] $attributes
     * An associative array of the element's attributes.
     * The keys are the attributes' names.
     */
    public $attributes = [];

    public function __construct($name, ...$children)
    {
        $this->name = $name;
        $this->addElements(...$children);
    }

    /** 
     * @return string the element as XML
     */
    public function __toString()
    {
        $ret = self::toDOM($this)->saveXML();
        if (theelega_string_startswith($ret, '<?xml'))
        {
            $loc = strpos($ret, '>');
            $ret = substr($ret, $loc + 1);
        }
        
        $ret = trim($ret);
        $ret = str_replace('&#xD;', "\r", $ret);
        $ret = str_replace('&#xA;', "\n", $ret);
        $ret = str_replace("\r\n", "\n", $ret);
        $ret = str_replace("\n", "\r\n", $ret);

        return $ret;
    }

    /** 
     * @param THEELEGA_XMLElement|string $name_or_element
     * @param string|null $text 
     */
    public function addElement($name_or_element, $text = null)
    {
        if ($name_or_element instanceof THEELEGA_XMLElement)
        {
            $this->children[] = $name_or_element;
        }
        else
        {
            $this->children[] = new self($name_or_element, $text);
        }
    }

    /** 
     * @param ...(string|THEELEGA_XMLElement|array) $elements
     * Each element is a string, a THEELEGA_XMLElement or an array consisting of:
     * 1) The name of the new element;
     * 2) The text of the new element (optional);
     * 3) Child elements, to be recursively fed to the new element's addElements() (optional).
     */
    public function addElements(...$elements)
    {
        foreach($elements as $e)
        {
            if (is_array($e) && isset($e[0]) && is_string($e[0]) && strlen($e[0]))
            {
                $name = theelega_arr_get($e, 0);
                $text = theelega_arr_get($e, 1);
                $newE = new self($name, $text);

                $newE->addElements(...array_slice($e, 2));
                $this->children[] = $newE;
            }
            else
            {
                $this->children[] = $e;
            }
        }
    }

    public function addText($str)
    {
        $this->children[] = $str;
    }

    public function addAttr($key, $val)
    {
        $this->attributes[$key] = $val;
    }

    /** 
     * @param THEELEGA_XMLElement $obj 
     * @param DomElement $parent Used in recursive calls
     * @return DomDocument
     */
    private static function toDOM($obj, $parent = null)
    {

        $xmldoc = null;
        if ($parent)
        {
            $xmldoc = $parent->ownerDocument;
        }
        else
        {
            $xmldoc = new DomDocument();
            $xmldoc->preserveWhiteSpace = false;
            $xmldoc->formatOutput = true;
    
            $parent = $xmldoc;
        }
    
        $elm = $xmldoc->createElement($obj->name, null);
        $parent->appendChild($elm);
    
        foreach ($obj->children as $child)
        {
            if ($child instanceof THEELEGA_XMLElement)
            {
                self::toDOM($child, $elm);
            }
            else if (is_string($child) || is_numeric($child))
            {
                $txt = $xmldoc->createTextNode($child);
                $elm->appendChild($txt);
            }        
        }
    
        foreach ($obj->attributes as $key => $value)
        {
            if (is_string($value) || is_numeric($value))
            {
                $attr = $xmldoc->createAttribute($key);
                $attr->value = $value;
                $elm->appendChild($attr);
            }        
        }
    
        return $xmldoc;
    }
}
?>