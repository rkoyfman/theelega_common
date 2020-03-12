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

    /**
     * Like the WP function @see get_metadata(), but gets many at once.
     * 
     * @param array $ids - object IDs for which to get meta
     * @param array $meta_keys - types of meta to get
     * @param array $meta_type - things like post, comment, term...
     * 
     * @return 
    */
    public function get_meta($ids, $meta_keys, $meta_type)
    {
        $ids = $ids ? $ids : [];
        $meta_keys = $meta_keys ? $meta_keys : [];

        $ids = implode(',', array_map('intval', $ids));
        $meta_keys = "'" . implode("', '", array_map('esc_sql', $meta_keys)) . "'";
        
        $id_clause = '';
        if ($ids)
        {
            $id_clause = "AND {$meta_type}_id IN ($ids)";
        }

        $metakey_clause = '';
        if ($meta_keys)
        {
            $metakey_clause = "AND meta_key IN ($meta_keys)";
        }
        
        $sql = "SELECT *
        FROM {$this->prefix}{$meta_type}meta
        WHERE 1 = 1
        $id_clause
        $metakey_clause";

        $ret = new THEELEGA_get_meta_result();
        foreach ($this->get_results($sql) as $row)
        {
            $pid = $row['post_id'];
            $key = $row['meta_key'];

            $ret->result[$pid][$key]['id'] = trim($row['meta_id']);
            $ret->result[$pid][$key]['value'] = trim($row['meta_value']);
        }

        return $ret;
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

/**
 * Class for the return value of THEELEGA_db::get_meta().
 */
class THEELEGA_get_meta_result
{
    public $result = [];
    public $meta_type = '';

    /** Get the meta_value corresponding to the given object and meta_key */
    public function get($object_id, $meta_key, $default = null)
    {
        if (isset($this->result[$object_id][$meta_key]['value']))
        {
            return $this->result[$object_id][$meta_key]['value'];
        }

        return $default;
    }

    /** Get the meta_id corresponding to the given object and meta_key */
    public function get_id($object_id, $meta_key, $default = null)
    {
        if (isset($this->result[$object_id][$meta_key]['id']))
        {
            return $this->result[$object_id][$meta_key]['id'];
        }

        return $default;
    }
}
?>