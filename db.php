<?php
class THEELEGA_db
{
    /** @var wpdb $wpdb */
    protected $wpdb;
    protected $prefix;

    public function __construct($wpdb = null)
    {
        $this->wpdb = $wpdb ? $wpdb : $GLOBALS['wpdb']; 
        $this->prefix = $this->wpdb->prefix; 
    }

    public function get_results($sql, $output = ARRAY_A)
    {
        $ret = $this->wpdb->get_results($sql, $output);
        theelega_throw_on_db_error($this->wpdb);
        return $ret;
    }

    public function get_row($sql)
    {
        $ret = $this->wpdb->get_row($sql);
        theelega_throw_on_db_error($this->wpdb);
        return $ret;
    }

    public function get_col($sql)
    {
        $ret = $this->wpdb->get_col($sql);
        theelega_throw_on_db_error($this->wpdb);
        return $ret;
    }

    public function get_var($sql)
    {
        $ret = $this->wpdb->get_var($sql);
        theelega_throw_on_db_error($this->wpdb);
        return $ret;
    }

    public function query($sql)
    {
        $this->wpdb->query($sql);
        theelega_throw_on_db_error($this->wpdb);
    }
}

function theelega_throw_on_db_error($db = null)
{
    global $wpdb;
    $db = $db ? $db : $wpdb;

    $e = $db->last_error;
    if ($e)
    {
        throw new Exception($e);
    }
}
?>