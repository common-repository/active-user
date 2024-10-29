<?php

/**
* @wordpress-plugin
* Plugin Name:    Active User Professional
* Plugin URI:     https://www.bouncingsprout.com/plugins/active-user
* Description:    Find out when your users last logged in to your website.
* Version:        1.0.1
* Author:         Bouncingsprout Studio
* Author URI:     https://www.bouncingsprout.com
* License:        GPL-2.0+
* License URI:    http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:    active-user
* Domain Path:    /languages
*/
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}

if ( function_exists( 'au_fs' ) ) {
    au_fs()->set_basename( false, __FILE__ );
} else {
    
    if ( !function_exists( 'au_fs' ) ) {
        // Create a helper function for easy SDK access.
        function au_fs()
        {
            global  $au_fs ;
            
            if ( !isset( $au_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $au_fs = fs_dynamic_init( array(
                    'id'             => '3823',
                    'slug'           => 'active-user',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_5a9e46e70db568146cbdacdf006ea',
                    'is_premium'     => false,
                    'premium_suffix' => 'Professional',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => array(
                    'slug'    => 'active-user',
                    'support' => false,
                ),
                    'is_live'        => true,
                ) );
            }
            
            return $au_fs;
        }
        
        // Init Freemius.
        au_fs();
        // Signal that SDK was initiated.
        do_action( 'au_fs_loaded' );
    }
    
    // Check for BuddyPress
    function wpau_bp_active()
    {
        
        if ( class_exists( 'BuddyPress' ) ) {
            $buddypress_active = true;
        } else {
            $buddypress_active = false;
        }
        
        return $buddypress_active;
    }
    
    add_action( 'wp_head', 'wpau_bp_active' );
    /**
     * Currently plugin version.
     * Start at version 1.0.0 and use SemVer - https://semver.org
     * Rename this for your plugin and update it as you release new versions.
     */
    define( 'ACTIVE_USER_VERSION', '1.0.1' );
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-active-user-activator.php
     */
    function activate_active_user()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-active-user-activator.php';
        Active_User_Activator::activate();
    }
    
    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-active-user-deactivator.php
     */
    function deactivate_active_user()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-active-user-deactivator.php';
        Active_User_Deactivator::deactivate();
    }
    
    register_activation_hook( __FILE__, 'activate_active_user' );
    register_deactivation_hook( __FILE__, 'deactivate_active_user' );
    // Not like register_uninstall_hook(), you do NOT have to use a static function.
    au_fs()->add_action( 'after_uninstall', 'au_fs_uninstall_cleanup' );
    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/class-active-user.php';
    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    function run_active_user()
    {
        $plugin = new Active_User();
        $plugin->run();
    }
    
    run_active_user();
}
