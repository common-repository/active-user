<?php

/**
* The file that defines the core plugin class
*
*/
class Active_User
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Active_User_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected  $loader ;
    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $active_user    The string used to uniquely identify this plugin.
     */
    protected  $active_user ;
    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected  $version ;
    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        
        if ( defined( 'ACTIVE_USER_VERSION' ) ) {
            $this->version = ACTIVE_USER_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        
        $this->active_user = 'active-user';
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        add_action(
            'wp_login',
            array( $this, 'timestamp' ),
            10,
            2
        );
    }
    
    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Active_User_Loader. Orchestrates the hooks of the plugin.
     * - Active_User_i18n. Defines internationalization functionality.
     * - Active_User_Admin. Defines all hooks for the admin area.
     * - Active_User_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-active-user-loader.php';
        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-active-user-i18n.php';
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-active-user-admin.php';
        /**
         * The class responsible for our User Activity Table
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-active-user-table.php';
        $this->loader = new Active_User_Loader();
    }
    
    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Active_User_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new Active_User_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }
    
    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Active_User_Admin( $this->get_active_user(), $this->get_version() );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        // $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu_page' );
    }
    
    /**
     * Set last login time on users when they log in.
     *
     * @since    1.0.0
     */
    public function timestamp( $user_login, $user )
    {
        // update user meta on login and add time() to it.
        $date = date( "Y-m-d H:i:s" );
        update_user_meta( $user->ID, 'wpau_last_login', $date );
    }
    
    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }
    
    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_active_user()
    {
        return $this->active_user;
    }
    
    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Active_User_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }
    
    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

}