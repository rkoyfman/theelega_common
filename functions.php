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
        $c = new WP_Term((object) $a);
        $c->ancestors = [];
        $c->ancestor_slugs = [];

        $ret['IDs'][$c->term_taxonomy_id] = $c;
        $ret['slugs'][$c->slug] = $c;
    }

    foreach ($ret['IDs'] as $c)
    {
        $pid = $c->parent;

        while ($pid)
        {
            $p = $ret['IDs'][$pid];
            $c->ancestors[] = $p;
            $c->ancestor_slugs[] = $p->slug;

            $pid = $p->parent;
        }
    }

    return $ret;
}
?>