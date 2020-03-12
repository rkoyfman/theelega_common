<?php
function theelega_var_dump($obj)
{
    ob_start();
    var_dump($obj);
    $vd = ob_get_clean();
    return $vd;
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

function theelega_show_notification($messages, $notification_type)
{
    $messages = is_array($messages) ? $messages : [$messages];
    
    $messages = array_map(function($m)
    {
        $m = esc_html($m);
        return "<div>$m</div>";
    }, $messages);

    $messages = implode('', $messages);

    add_action('admin_notices', function() use ($messages, $notification_type)
    {
        ?>
        <div class="notice notice-<?= $notification_type ?> is-dismissible">
            <?= $messages ?>
        </div>
        <?php
    });
}

/*
    Builds a category tree from an array of database rows.
    The returned object is an array of two sub-arrays: 
        IDs: index of the categories by term_taxonomy_id.
        slugs: index of the categories by slug.

    The values are WP_Term objects, with the extra property $ancestors - an array of all the
    term's ancestors, in ascending order (from direct parent to top-level). The ancestors are also WP_Terms.
*/
function theelega_build_category_tree($array)
{
    $ret = [];
    $ret['IDs'] = [];
    $ret['slugs'] = [];

    foreach ($array as $a)
    {
        $c = $a instanceof WP_Term ? $a : new WP_Term((object) $a);
        $c->ancestor_slugs = [];
        $c->descendent_slugs = [];

        $ret['IDs'][$c->term_taxonomy_id] = $c;
        $ret['slugs'][$c->slug] = $c;
    }

    foreach ($ret['IDs'] as $c)
    {
        $pid = $c->parent;

        while ($pid)
        {
            $p = $ret['IDs'][$pid];
            $c->ancestor_slugs[$p->slug] = $p->slug;
            $p->descendent_slugs[$c->slug] = $c->slug;

            $pid = $p->parent;
        }
    }

    return $ret;
}

function theelega_debug_backtrace()
{
    ob_start();
    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $ret = ob_get_clean();
    return $ret;
}

function theelega_log($file, $txt)
{
    $timestamp = new DateTime();
    $timestamp = $timestamp->format(DateTime::ISO8601);
    
    $dir = trailingslashit(get_home_path()) . '/wp-content/logs';
    if (!file_exists($dir))
    {
        mkdir($dir, 0777, true);
    }
    
    $file = $dir . '/' . $file;

    $myfile = fopen($file, 'a');
    
    fwrite($myfile,
    $timestamp
        . ' (Greenwich time):' 
        . PHP_EOL
        . $txt
        . PHP_EOL . PHP_EOL
        . theelega_debug_backtrace()
        . PHP_EOL . PHP_EOL);
    fclose($myfile);
}
?>