<?php
/*
Plugin Name: wp-linked-data
Plugin URI: http://wordpress.org/extend/plugins/wp-linked-data/
Description: Publishing blog contents as linked data
Version: 0.4
Author: Angelo Veltens
Author URI: http://datenwissen.de/
License: GPLv3
*/

define('WP_LINKED_DATA_PLUGIN_DIR_PATH', plugin_dir_path (__FILE__));

function phpVersionSupported () {
    return version_compare (PHP_VERSION, '5.3.0', '>=');
}

if (phpVersionSupported()) {
    require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'wp-linked-data-initialize.php');
    require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'request/SimplifiedContentNegotiation.php');
    require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'request/PeclHttpContentNegotiation.php');
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
                add_action('admin_init', array(&$this, 'showWarningIfPeclHttpMissing'));

                $interceptor = $wpLinkedDataInitializer->initialize ();
                add_action ('wp', array(&$interceptor, 'intercept'));

                $userProfileController = $wpLinkedDataInitializer->getUserProfileController ();
                add_action ('show_user_profile', array(&$userProfileController, 'renderWebIdSection'));
                add_action ('personal_options_update', array(&$userProfileController, 'saveWebIdData'));
            }
        }

        static function onPluginActivation () {
            if (phpVersionSupported ()) {
                if (!WpLinkedDataInitializer::isPeclHttpInstalled ()) {
                    add_option ('show_pecl_http_missing_warning', true);

                }
            } else {
                deactivate_plugins (__FILE__);
                wp_die (wp_sprintf ('%1s: ' . __ ('Sorry, This plugin has taken a bold step in requiring PHP 5.3.0+. Your server is currently running PHP %2s, Please bug your host to upgrade to a recent version of PHP which is less bug-prone.', 'wp-linked-data'), __FILE__, PHP_VERSION));
            }
        }

        static function showWarningIfPeclHttpMissing() {
            if(get_option ('show_pecl_http_missing_warning', false)) {
                delete_option('show_pecl_http_missing_warning');
                add_action('admin_notices', create_function('', 'echo
    \'<div class="updated fade"><p><strong>Warning:</strong> The PHP extension <strong>pecl_http is missing</strong>. wp-linked-data will work without it, but fall back to a simple, <strong>inaccurate</strong> HTTP content negotiation. It is recommended to <strong>install the <a target="_blank" href="http://pecl.php.net/package/pecl_http">pecl_http extension</a></strong></p></div>\';'));
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
