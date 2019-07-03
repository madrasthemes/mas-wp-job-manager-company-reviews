<?php
/**
 * Plugin Name: Mas Reviews for WP Job Manager Company
 * Description: Leave reviews for listings in WP Job Manager Company. Define review categories and choose the number of stars available.
 * Version: 1.0.0
 * Author: MadrasThemes
 * Author URI: https://madrasthemes.com/
 *
 * Text Domain: mas-wp-job-manager-company-reviews
 * Domain Path: /languages/
 *
 * @package Reviews
 * @category Core
 * @author Madras Themes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Mas_WP_Job_Manager_Company_Reviews.
 *
 * Main Mas_WPJMCR class initializes the plugin.
 *
 * @class     Mas_WP_Job_Manager_Company_Reviews
 * @version   1.0.0
 * @author    Madras Themes
 */
class Mas_WP_Job_Manager_Company_Reviews {

    /**
     * Instace of Mas_WP_Job_Manager_Company_Reviews.
     *
     * @since 1.0.0
     * @access private
     * @var object $instance The instance of Mas_WPJMCR.
     */
    private static $instance;

    /**
     * Plugin file.
     *
     * @since 1.0.0
     * @var string $file Plugin file path.
     */
    public $file = __FILE__;

    /**
     * Construct.
     *
     * Initialize the class and plugin.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );
        $this->plugin_dir = plugin_dir_path( __FILE__ );
        $this->init();
    }

    /**
     * Instace.
     *
     * An global instance of the class. Used to retrieve the instance
     * to use on other files/plugins/themes.
     *
     * @since 1.0.0
     * @return object Instance of the class.
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Initialize plugin.
     * Load all file and classes.
     *
     * @since 1.0.0
     */
    public function init() {

        // Load Plugin Translation.
        load_plugin_textdomain( dirname( plugin_basename( __FILE__ ) ), false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

        // Functions.
        require_once( $this->plugin_dir . 'includes/functions.php' );

        /* === CLASSES === */

        // Review Form.
        require_once( $this->plugin_dir . 'includes/class-mas-wpjmcr-form.php' );
        $this->form = new Mas_WPJMCR_Form();

        // Submit Review.
        require_once( $this->plugin_dir . 'includes/class-mas-wpjmcr-submit.php' );
        $this->submit = new Mas_WPJMCR_Submit();

        // Display Review.
        require_once( $this->plugin_dir . 'includes/class-mas-wpjmcr-display.php' );
        $this->display = new Mas_WPJMCR_Display();

        // Edit Review.
        require_once( $this->plugin_dir . 'includes/class-mas-wpjmcr-edit.php' );
        $this->edit = new Mas_WPJMCR_Edit();

        // Post Edit Screen.
        require_once( $this->plugin_dir . 'includes/class-mas-wpjmcr-post-edit.php' );
        $this->post_edit = new Mas_WPJMCR_Post_Edit();

        // Shortcode [mas_review_stars], [mas_review_average], [mas_review_count], & [mas_review_dashboard].
        require_once( $this->plugin_dir . 'includes/class-mas-wpjmcr-shortcodes.php' );
        $this->shortcodes = new Mas_WPJMCR_Shortcodes();

        /* === SETTINGS === */

        // Settings.
        if ( is_admin() ) {
            require_once( $this->plugin_dir . 'includes/class-mas-wpjmcr-settings.php' );
            $this->settings = new Mas_WPJMCR_Settings();
        }

        /* === INTEGRATIONS === */
        // Jetpack (Comments).
        require_once( $this->plugin_dir . 'includes/integrations/jetpack.php' );

        // Polylang.
        if ( function_exists( 'pll_register_string' ) ) {
            require_once( $this->plugin_dir . 'includes/integrations/class-mas-wpjmcr-polylang.php' );
            new Mas_WPJMCR_Polylang();
        }

        // Add comment support for company.
        add_action( 'init', array( $this, 'add_comments_support' ) );

        // Load Scripts.
        add_action( 'wp_enqueue_scripts', array( $this, 'mas_wpjmcr_enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'mas_wpjmcr_admin_enqueue_scripts' ) );
    }

    /**
     * Enable Listing Comments
     *
     * @since 1.0.0
     */
    public function add_comments_support() {
        add_post_type_support( 'company', 'comments' );
    }

    /**
     * Enqueue scripts.
     *
     * Enqueue all style en javascripts.
     *
     * @since 1.0.0
     */
    public function mas_wpjmcr_enqueue_scripts() {
        // General stylesheet.
        if( apply_filters( 'mas_wpjmcr_enqueue_scripts_enable_frontend_css', true ) ) {
            wp_enqueue_style( 'mas-wp-job-manager-company-reviews', plugins_url( 'assets/css/mas-wp-job-manager-company-reviews.css', __FILE__ ), array( 'dashicons' ) );
        }

        // Javascript.
        if( apply_filters( 'mas_wpjmcr_enqueue_scripts_enable_frontend_js', true ) ) {
            wp_enqueue_script( 'mas-wp-job-manager-company-reviews-js', plugins_url( 'assets/js/mas-wp-job-manager-company-reviews.js', __FILE__ ), array( 'jquery' ) );
        }
    }

    /**
     * Admin scripts.
     *
     * @since 1.0.0
     */
    public function mas_wpjmcr_admin_enqueue_scripts( $hook_suffix ) {
        global $post_type;
        if ( in_array( $hook_suffix, array( 'comment.php', 'edit-comments.php' ) ) || 'post.php' === $hook_suffix && 'company' === $post_type ) {
            wp_enqueue_style( 'mas-wpjmcr-gallery-admin', $this->plugin_url . 'assets/css/mas-wp-job-manager-company-reviews-gallery-admin.css', array() );
        }
    }
}

/**
 * The main function responsible for returning the Mas_WP_Job_Manager_Company_Reviews object.
 *
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * @since 1.0.0
 *
 * @return object Mas_WP_Job_Manager_Company_Reviews class object.
 */
function mas_wpjmcr() {
    if ( ! class_exists( 'Mas_WP_Job_Manager_Company' ) )
        return;

    return Mas_WP_Job_Manager_Company_Reviews::instance();
}

// Load plugin instance on plugins loaded.
add_action( 'plugins_loaded', 'mas_wpjmcr' );