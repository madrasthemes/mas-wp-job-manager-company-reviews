<?php
/**
 * Add Settings in WPJM Settings Page.
 *
 * @since 1.0.0
 *
 * @package Reviews
 * @category Core
 * @author Madras Themes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Settings Class.
 * Handle the admin settings.
 *
 * @since 1.0.0
 */
class Mas_WPJMCR_Settings {

    /**
     * Construct.
     *
     * Initialize this class including hooks.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Add settings.
        add_action( 'job_manager_settings', array( $this, 'settings_tab' ), 20 );

        add_action( 'wp_job_manager_admin_field_mas_wpjmcr_dashboard_actions', array( $this, 'dashboard_actions_field' ), 10, 4 );
    }

    /**
     * Settings page.
     *
     * Add an settings tab to the Listings -> settings page.
     *
     * @since 1.0.0
     *
     * @param array $settings Array of default settings.
     * @return array $settings Array including the new settings.
     */
    public function settings_tab( $settings ) {

        $settings['mas_wpjmcr_settings'] = array(
            __( 'Company Reviews', 'mas-wp-job-manager-company-reviews' ),
            array(
                array(
                    'name'          => 'mas_wpjmcr_star_count',
                    'std'           => '5',
                    'placeholder'   => '',
                    'label'         => __( 'Stars', 'mas-wp-job-manager-company-reviews' ),
                    'desc'          => __( 'How many stars would you like to use?', 'mas-wp-job-manager-company-reviews' ),
                    'attributes'    => array()
                ),
                array(
                    'name'          => 'mas_wpjmcr_categories',
                    'std'           => implode( ',', mas_wpjmcr_get_categories() ),
                    'placeholder'   => '',
                    'label'         => __( 'Review categories', 'mas-wp-job-manager-company-reviews' ),
                    'desc'          => __( 'Categories you would you like to use, each category seperated by comma(,).', 'mas-wp-job-manager-company-reviews' ),
                    'attributes'    => array(),
                    'type'          => 'text'
                ),
                array(
                    'name'          => 'mas_wpjmcr_listing_authors_can_moderate',
                    'std'           => '0',
                    'placeholder'   => '',
                    'label'         => __( 'Listing owners can moderate reviews', 'mas-wp-job-manager-company-reviews' ),
                    'cb_label'      => __( 'Listing owners can moderate reviews', 'mas-wp-job-manager-company-reviews' ),
                    'desc'          => __( 'Let listing owners moderate the reviews on their listings.', 'mas-wp-job-manager-company-reviews' ),
                    'attributes'    => array(),
                    'type'          => 'checkbox'
                ),
                array(
                    'name'          => 'mas_wpjmcr_allow_owner',
                    'std'           => '0',
                    'placeholder'   => '',
                    'label'         => __( 'Allow listing owner review', 'mas-wp-job-manager-company-reviews' ),
                    'cb_label'      => __( 'Allow listing owners to review their own listings.', 'mas-wp-job-manager-company-reviews' ),
                    'desc'          => '',
                    'attributes'    => array(),
                    'type'          => 'checkbox'
                ),
                array(
                    'name'          => 'mas_wpjmcr_allow_multiple',
                    'std'           => '0',
                    'placeholder'   => '',
                    'label'         => __( 'Allow multiple review', 'mas-wp-job-manager-company-reviews' ),
                    'cb_label'      => __( 'Allow multiple review from the same user (does not apply to listing owner).', 'mas-wp-job-manager-company-reviews' ),
                    'desc'          => '',
                    'attributes'    => array(),
                    'type'          => 'checkbox'
                ),
                array(
                    'name'          => 'mas_wpjmcr_allow_guests',
                    'std'           => '1',
                    'placeholder'   => '',
                    'label'         => __( 'Allow guests to review', 'mas-wp-job-manager-company-reviews' ),
                    'cb_label'      => __( 'Allow logged out users to leave a review.', 'mas-wp-job-manager-company-reviews' ),
                    'desc'          => '',
                    'attributes'    => array(),
                    'type'          => 'checkbox'
                ),
                array(
                    'name'          => 'mas_wpjmcr_allow_blank_comment',
                    'std'           => '0',
                    'placeholder'   => '',
                    'label'         => __( 'Allow blank comment', 'mas-wp-job-manager-company-reviews' ),
                    'cb_label'      => __( 'Allow blank comment content in review.', 'mas-wp-job-manager-company-reviews' ),
                    'desc'          => '',
                    'attributes'    => array(),
                    'type'          => 'checkbox'
                ),
                array(
                    'name'          => 'mas_wpjmcr_enable_title',
                    'std'           => '1',
                    'placeholder'   => '',
                    'label'         => __( 'Comment Title', 'mas-wp-job-manager-company-reviews' ),
                    'cb_label'      => __( 'Allow users to add comment title to their review.', 'mas-wp-job-manager-company-reviews' ),
                    'desc'          => __( 'If enabled user can add comment title when they submit review.', 'mas-wp-job-manager-company-reviews' ),
                    'attributes'    => array(),
                    'type'          => 'checkbox',
                ),
                array(
                    'name'          => 'mas_wpjmcr_allow_images',
                    'std'           => '1',
                    'placeholder'   => '',
                    'label'         => __( 'Image Upload', 'mas-wp-job-manager-company-reviews' ),
                    'cb_label'      => __( 'Allow users to add image gallery to their review.', 'mas-wp-job-manager-company-reviews' ),
                    'desc'          => __( 'If enabled user can upload gallery when they submit review.', 'mas-wp-job-manager-company-reviews' ),
                    'attributes'    => array(),
                    'type'          => 'checkbox',
                ),
                array(
                    'name'          => 'mas_wpjmcr_dashboard_actions',
                    'std'           => array( 'approve', 'unapprove', 'spam', 'trash' ),
                    'label'         => __( 'Dashboard Actions', 'mas-wp-job-manager-company-reviews' ),
                    'type'          => 'mas_wpjmcr_dashboard_actions',
                ),
            ),
        );

        return apply_filters( 'mas_wpjmcr_settings_tab_fields', $settings );
    }

    /**
     * Dashboard Action Field Callback
     *
     * @since 1.0.0
     */
    public function dashboard_actions_field( $option, $attributes, $value, $placeholder ) {
        $value = is_array( $value ) ? $value : array(); // Make sure it's array.
        $actions = mas_wpjmcr_dashboard_actions(); // Available actions.
        ?>
        <?php foreach( $actions as $action => $label ) : ?>
            <p>
                <label>
                    <input name="<?php echo esc_attr( $option['name'] ); ?>[]" type="checkbox" value="<?php echo esc_attr( $action ); ?>" <?php echo in_array( $action, $value ) ? 'checked="checked"' : ''; ?>> <?php echo esc_html( $label ); ?>
                </label>
            </p>
        <?php endforeach; ?>
        <?php
    }

}
