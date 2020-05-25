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

    public function escape($text, $like)
    {
        if ($like)
        {
            $text = $this->wpdb->esc_like($text);
        }
        return esc_sql($text);
    }

    /**
     * Like the WP function get_metadata(), but gets many at once.
     * 
     * @param array $ids - object IDs for which to get meta
     * @param array $meta_keys - types of meta to get
     * @param string $meta_type - things like post, comment, term...
     * 
     * @return THEELEGA_get_meta_result
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
            $id = $row[$meta_type . '_id'];
            $key = $row['meta_key'];

            $ret->result[$id][$key]['id'] = trim($row['meta_id']);
            $ret->result[$id][$key]['value'] = trim($row['meta_value']);
        }

        return $ret;
    }

    /**
     * Delete the metadata of the given type, en masse.
     * 
     * @param array $items - array of arrays; Each array has two fields - 'id' (post/comment/term ID) and 'meta_key'
     * @param string $meta_type - things like post, comment, term...
     * 
     * @return 
    */
    public function delete_meta($items, $meta_type)
    {
        if (!$items)
        {
            return;
        }

        $where_clause = [];
        foreach ($items as $i)
        {
            $id = theelega_arr_get($i, 'id');
            $mk = theelega_arr_get($i, 'meta_key');
            if ($id === null || $mk === null)
            {
                continue;
            }

            $str1 = "{$meta_type}_id = " . intval($id);
            $str2 = "meta_key = '" . esc_sql($mk) . "'";
            $where_clause[] = "($str1 AND $str2)";
        }

        $where_clause = implode(' OR ', $where_clause);

        $sql = "DELETE FROM {$this->prefix}{$meta_type}meta
        WHERE {$where_clause}";

        $this->query($sql);
    }

    /**
     * Delete the metadata of the given type, en masse.
     * 
     * @param array $items - array of arrays; Each array has these fields -, 
     *      'id' (post/comment/term ID),
     *      'meta_key',
     *      'meta_value,
     *      'delete_existing' (If false, the meta field may have multiple entries.)
     * @param string $meta_type - things like post, comment, term...
     * @param bool $delete_existing - Default value, if the sub-arrays don't have one...\
    */
    public function insert_meta($items, $meta_type, $delete_existing = true)
    {
        if (!$items)
        {
            return;
        }

        $deleted = array_filter($items, function($i) use ($delete_existing)
        {
            return theelega_arr_get($i, 'delete_existing', $delete_existing);
        });
        $this->delete_meta($deleted, $meta_type);

        $rows = [];
        foreach ($items as $i)
        {
            $id = theelega_arr_get($i, 'id');
            $mk = theelega_arr_get($i, 'meta_key');
            $mv = theelega_arr_get($i, 'meta_value');
            if ($id === null || $mk === null || $mv === null)
            {
                continue;
            }

            $id = intval($id);
            $mk = "'" . esc_sql(maybe_serialize($mk)) . "'";
            $mv = "'" . esc_sql(maybe_serialize($mv)) . "'";
            $rows[] = "($id, $mk, $mv)";
        }

        $rows = implode(',', $rows);

        $sql = "INSERT INTO {$this->prefix}{$meta_type}meta
        ({$meta_type}_id, meta_key, meta_value)
        VALUES
        $rows";

        $this->query($sql);
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
        return theelega_arr_get($this->result, [$object_id, $meta_key, 'value'], $default);
    }

    /** Get the meta_id corresponding to the given object and meta_key */
    public function get_id($object_id, $meta_key, $default = null)
    {
        return theelega_arr_get($this->result, [$object_id, $meta_key, 'id'], $default);
    }
}
?>