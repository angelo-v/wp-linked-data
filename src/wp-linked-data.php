<?php
/*
Plugin Name: wp-linked-data
Plugin URI: http://wordpress.org/extend/plugins/wp-linked-data/
Description: Publishing blog contents as linked data
Version: 0.5.2
Author: Angelo Veltens
Author URI: http://angelo.veltens.org/
License: GPLv3
*/

define('WP_LINKED_DATA_PLUGIN_DIR_PATH', plugin_dir_path (__FILE__));

function phpVersionSupported () {
    return version_compare (PHP_VERSION, '5.3.0', '>=');
}

if (phpVersionSupported()) {
    require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'wp-linked-data-initialize.php');
    require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'request/WilldurandContentNegotiation.php');
    require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'request/RequestInterceptor.php');
    require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'rdf/RdfBuilder.php');
    require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'rdf/RdfPrinter.php');
    require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'vendor/autoload.php');
    require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'controller/UserProfileController.php');
    require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'service/UserProfileWebIdService.php');
}

if (!class_exists ('WpLinkedData')) {
    class WpLinkedData {

        function WpLinkedData ($wpLinkedDataInitializer) {
            register_activation_hook (__FILE__, array(&$this, 'onPluginActivation'));
            if (phpVersionSupported()) {
                $interceptor = $wpLinkedDataInitializer->initialize ();
                add_action ('wp', array(&$interceptor, 'intercept'));

                $userProfileController = $wpLinkedDataInitializer->getUserProfileController ();
                add_action ('show_user_profile', array(&$userProfileController, 'renderWebIdSection'));
                add_action ('personal_options_update', array(&$userProfileController, 'saveWebIdData'));
            }
        }

        static function onPluginActivation () {
            if (!phpVersionSupported ()) {
                deactivate_plugins (__FILE__);
                wp_die (wp_sprintf ('%1s: ' . __ ('Sorry, This plugin has taken a bold step in requiring PHP 5.3.0+. Your server is currently running PHP %2s, Please bug your host to upgrade to a recent version of PHP which is less bug-prone.', 'wp-linked-data'), __FILE__, PHP_VERSION));
            }
        }
    }
} else {
    exit ('Class WpLinkedData already exists');
}

// initialization of namespaces classes is done in a separated initializer class
// to allow old php versions to display a proper error message instead of crashing
$wpLinkedDataInitializer = null;
if (phpVersionSupported()) {
    $wpLinkedDataInitializer = new WpLinkedDataInitializer();
}
new WpLinkedData($wpLinkedDataInitializer);

?>
