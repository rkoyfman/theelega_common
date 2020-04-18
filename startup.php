<?php

//Accepts an array of strings, then checks if the corresponding plugins have been loaded.
function theelega_missing_dependencies($dependencies)
{        
    $deps_available = [
        'acf' => class_exists('ACF'),
        'theplus' => class_exists('ThePlus_addon'),
        'js_composer' => class_exists('Vc_Manager'), //WPBakery Page Builder
        'woocommerce' => class_exists('WooCommerce'),
        'wpallimport' => class_exists('PMXI_Plugin'),
        'wpallimport-woocommerce' => class_exists('PMWI_Plugin'),
    ];

    $ret = [];
    foreach($dependencies as $d)
    {
        if (!$deps_available[$d])
        {
            $ret[] = $d;
        }
    }

    return $ret;
};

function theelega_missing_dependencies_notification($plugin, $dependencies)
{
    $msg = "The plugin $plugin failed to load due to missing dependencies: " . implode(', ', $dependencies);
    theelega_show_notification([$msg], 'error');
}

add_action('wp_loaded', function() 
{
    do_action('theelega_startup');
});
?>