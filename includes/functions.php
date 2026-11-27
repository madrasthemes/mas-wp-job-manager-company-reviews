<?php
/**
 * Functions.
 *
 * @since 1.0.0
 *
 * @package MAS Company Reviews For WP Job Manager
 * @category Core
 * @author Madras Themes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get Review Categories.
 *
 * @since 1.0.0
 *
 * @return array
 */
if ( ! function_exists( 'mas_wpjmcr_get_categories' ) ) {
    function mas_wpjmcr_get_categories() {

        $default = array(
            esc_html__( 'Speed', 'mas-wp-job-manager-company-reviews' ),
            esc_html__( 'Quality', 'mas-wp-job-manager-company-reviews' ),
            esc_html__( 'Price', 'mas-wp-job-manager-company-reviews' ),
        );
        $default = implode( ',', $default ); // Default string.

        $categories = get_option( 'mas_wpjmcr_categories', $default ); // String.

        return array_values( array_filter( array_map( 'trim', explode( ',', (string) $categories ) ), 'strlen' ) ); // Array.
    }
}

/**
 * Get Max Star Number.
 * Return the max number of stars used to display. Default is 5;
 *
 * @since 1.0.0
 *
 * @return int
 */
if ( ! function_exists( 'mas_wpjmcr_get_max_stars' ) ) {
    function mas_wpjmcr_get_max_stars() {
        $stars = get_option( 'mas_wpjmcr_star_count', 5 );
        $stars = absint( apply_filters( 'mas_wpjmcr_count_stars', $stars ) );
        return $stars > 0 ? $stars : 5;
    }
}

/**
 * Get review (cached).
 *
 * @since 1.0.0
 *
 * @param int $post_id Listing ID.
 * @return array
 */
if ( ! function_exists( 'mas_wpjmcr_get_reviews' ) ) {
    function mas_wpjmcr_get_reviews( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        // Bail if not company.
        if ( 'company' !== get_post_type( $post_id ) ) {
            return array();
        }

        // Only if posts has comments.
        $comments = absint( get_comments_number( $post_id ) );
        if ( ! $comments ) {
            return array();
        }

        // Get reviews datas.
        $reviews = get_post_meta( $post_id, '_all_ratings', true );
        $reviews = is_array( $reviews ) ? $reviews : array();

        // Reviews can't be more than comments. That's wrong, reset.
        if ( count( $reviews ) > $comments ) { 
            $reviews = array();
        }

        // Check and update data once a day.
        $today = gmdate( 'Ymd' ); // YYYYMMDD.
        $last_updated = get_post_meta( $post_id, '_mas_wpjmcr_last_updated', true );

        if ( intval( $today ) !== intval( $last_updated ) || ! $reviews ) {
            $reviews = mas_wpjmcr_get_reviews_db( $post_id );
            update_post_meta( $post_id, '_all_ratings', $reviews );
            update_post_meta( $post_id, '_mas_wpjmcr_last_updated', $today );
        }

        return $reviews;
    }
}

/**
 * Get Reviews DB by Listing ID.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID.
 * @return array
 */
if ( ! function_exists( 'mas_wpjmcr_get_reviews_db' ) ) {
    function mas_wpjmcr_get_reviews_db( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        // Var.
        $reviews = array();

        // Bail if not company.
        if ( 'company' !== get_post_type( $post_id ) ) {
            return $reviews;
        }

        // Get all first level comments.
        $args = array(
            'post_id'    => $post_id,
            'parent'     => 0,
            'status'     => 'approve',
            'fields'     => 'ids',
            'meta_query' => array(
                'relation'    => 'OR',
                array (
                    'key'     => 'review_average',
                    'compare' => 'EXISTS',
                ),
            ),
        );
        $comments = get_comments( $args );
        if ( ! $comments ) {
            return $reviews;
        }

        // Loop all comment and add if it's a review.
        foreach ( $comments as $comment_id ) {

            // Get review average.
            $review_average = get_comment_meta( $comment_id, 'review_average', true );

            // Add reviews.
            $reviews[ $comment_id ] = $review_average;
        }

        return $reviews;
    }
}

/**
 * Get Reviews DB by Listing ID.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID.
 * @return array
 */
if ( ! function_exists( 'mas_wpjmcr_get_catetgory_average_reviews_db' ) ) {
    function mas_wpjmcr_get_catetgory_average_reviews_db( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        // Var.
        $stars = array();

        // Bail if not company.
        if ( 'company' !== get_post_type( $post_id ) ) {
            return $stars;
        }

        // Get all first level comments.
        $args = array(
            'post_id'    => $post_id,
            'parent'     => 0,
            'status'     => 'approve',
            'fields'     => 'ids',
            'meta_query' => array(
                'relation'    => 'OR',
                array (
                    'key'     => 'review_average',
                    'compare' => 'EXISTS',
                ),
            ),
        );
        $comments = get_comments( $args );
        if ( ! $comments ) {
            return $stars;
        }

        $categories = mas_wpjmcr_get_categories();

        // Loop all comment and add if it's a review.
        foreach ( $comments as $comment_id ) {
            $ratings = get_comment_meta( $comment_id, 'review_stars', true );
            if ( ! is_array( $ratings ) ) {
                continue;
            }
            foreach ( $ratings as $category => $rating ) {
                $stars[ $category ][] = mas_wpjmcr_sanitize_number( $rating );
            }
        }

        foreach ( $stars as $key => $star ) {
            $stars[ $key ] = ! empty( $star ) ? array_sum( $star ) / count( $star ) : 0;
        }

        return $stars;
    }
}

/**
 * Get reviews average of a listing.
 *
 * @since 1.0.0
 *
 * @param int $post_id Listing ID.
 * @return float|int
 */
if ( ! function_exists( 'mas_wpjmcr_get_reviews_average' ) ) {
    function mas_wpjmcr_get_reviews_average( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        if ( ! $post_id || 'company' !== get_post_type( $post_id ) ) {
            return 0;
        }

        // Get review average.
        $raw_average    = get_post_meta( $post_id, '_average_rating', true );
        $review_average = mas_wpjmcr_sanitize_number( $raw_average );

        // Try to update if not cached yet.
        if ( '' === $raw_average || ( ! $review_average && ! metadata_exists( 'post', $post_id, '_average_rating' ) ) ) {
            mas_wpjmcr_update_reviews_average( $post_id );
            $review_average = mas_wpjmcr_sanitize_number( get_post_meta( $post_id, '_average_rating', true ) );
        }

        // Still no review average, return 0.
        if ( ! $review_average ) {
            return 0;
        }

        // In v.2.0.0 we round on output not input.
        return round( $review_average * 2, apply_filters( 'mas_wpjmcr_review_average_round', 1 ) ) / 2;
    }
}

/**
 * Get reviews count of a listing.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID. Optional, will use current loop ID.
 * @return int
 */
if ( ! function_exists( 'mas_wpjmcr_get_reviews_count' ) ) {
    function mas_wpjmcr_get_reviews_count( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        $reviews = mas_wpjmcr_get_reviews( $post_id );
        return count( $reviews );
    }
}

/**
 * Get listing star rating display.
 *
 * @since 1.0.0
 *
 * @param int $post_id Listing ID.
 * @return string Single listing rating HTML.
 */
if ( ! function_exists( 'mas_wpjmcr_reviews_get_stars' ) ) {
    function mas_wpjmcr_reviews_get_stars( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        // Get review average.
        $max_stars = mas_wpjmcr_get_max_stars();
        $rating    = min( $max_stars, max( 0, (float) mas_wpjmcr_get_reviews_average( $post_id ) ) );

        // Display stars based on total average ratings.
        $full_stars  = max( 0, (int) floor( $rating ) );
        $half_stars  = max( 0, (int) ceil( $rating - $full_stars ) );
        $empty_stars = max( 0, (int) ( $max_stars - $full_stars - $half_stars ) );
        ob_start(); 
        ?>

        <span class="stars-rating wp-job-manager-star-listing-star-rating">
            <?php echo str_repeat( '<span class="dashicons dashicons-star-filled"></span>', $full_stars ); ?>
            <?php echo str_repeat( '<span class="dashicons dashicons-star-half"></span>', $half_stars ); ?>
            <?php echo str_repeat( '<span class="dashicons dashicons-star-empty"></span>', $empty_stars ); ?>
        </span>

        <?php
        return ob_get_clean();
    }
}

/**
 * Get single review stars
 *
 * @since 1.0.0
 *
 * @param int $comment_id Comment ID.
 * @return string Single comment/review rating HTML.
 */
if ( ! function_exists( 'mas_wpjmcr_review_get_stars' ) ) {
    function mas_wpjmcr_review_get_stars( $comment_id ) {
        $ratings = get_comment_meta( $comment_id, 'review_stars', true );
        if ( empty( $ratings ) || ! is_array( $ratings ) ) {
            return '';
        }

        $max_stars    = mas_wpjmcr_get_max_stars();
        $review_stars = '<div class="mas-wpjmcr-list-reviews">';
        foreach ( $ratings as $category => $rating ) {
            $category      = apply_filters( 'mas_wpjmcr_category_label', $category );
            $numeric_score = (float) mas_wpjmcr_sanitize_number( $rating );
            $review_stars .= '<div class="stars-rating star-rating">';
            $review_stars .= '<div class="star-rating-title">';
            $review_stars .= esc_html( $category );
            $review_stars .= '</div>';
            $review_stars .= '<div class="ratings">';
            for ( $i = 0; $i < $max_stars; $i++ ) {
                $review_stars .= '<span class="dashicons dashicons-star-' . ( $i < $numeric_score ? 'filled' : 'empty' ) . '"></span>';
            }
            $review_stars .= '</div>';
            $review_stars .= '</div>';
        }
        $review_stars .= '</div>';

        return $review_stars;
    }
}

/**
 * Update and recalculate review average of a listing.
 *
 * @since 1.0.0
 *
 * @param int $post_id Listing ID.
 * @return bool|int
 */
if ( ! function_exists( 'mas_wpjmcr_update_reviews_average' ) ) {
    function mas_wpjmcr_update_reviews_average( $post_id ) {
        $total   = 0;
        $reviews = mas_wpjmcr_get_reviews( $post_id );
        if ( ! $reviews ) {
            update_post_meta( $post_id, '_average_rating', 0 );
            return 0;
        }
        foreach ( $reviews as $review ) {
            $total += mas_wpjmcr_sanitize_number( $review );
        }
        $average = mas_wpjmcr_sanitize_number( $total / count( $reviews ) );
        return update_post_meta( $post_id, '_average_rating', $average );
    }
}

/**
 * Get single review average.
 *
 * @since 1.0.0
 *
 * @param int $comment_id Comment ID.
 * @return string
 */
if ( ! function_exists( 'mas_wpjmcr_get_review_average' ) ) {
    function mas_wpjmcr_get_review_average( $comment_id ) {
        $average = get_comment_meta( $comment_id, 'review_average', true );
        return number_format( (float) mas_wpjmcr_sanitize_number( $average ), 1, '.', ',' );
    }
}

/**
 * Get SVG
 *
 * @since 1.0.0
 *
 * @param string $icon Icon name.
 * @return string|false
 */
if ( ! function_exists( 'mas_wpjmcr_get_svg' ) ) {
    function mas_wpjmcr_get_svg( $icon ) {
        $icon = sanitize_file_name( (string) $icon );
        if ( empty( $icon ) ) {
            return false;
        }
        $file = plugin_dir_path( mas_wpjmcr()->file ) . "assets/images/{$icon}.svg";

        if ( file_exists( $file ) ) {
            ob_start();
            ?>
            <span class="mas-wpjmcr-icon"><?php require( $file ); ?></span>
            <?php
            return trim( ob_get_clean() );
        }
        return false;
    }
}

/**
 * Format Files Data.
 *
 * @since 1.0.0
 *
 * @param array $files $_FILES.
 * @return array
 */
if ( ! function_exists( 'mas_wpjmcr_handle_uploads' ) ) {
    function mas_wpjmcr_handle_uploads( $post_id, $comment_id ) {

        // Check if enabled.
        if ( ! get_option( 'mas_wpjmcr_allow_images', true ) ) {
            return;
        }

        // Get uploaded images data.
        if ( ! isset( $_FILES['mas-wpjmcr-gallery'] ) ) {
            return;
        }

        // Format multiple files into individual $_FILES data.
        $_files_gallery  = $_FILES['mas-wpjmcr-gallery'];
        $original_files  = $_files_gallery;
        $files_data      = array();
        if ( isset( $_files_gallery['name'] ) && is_array( $_files_gallery['name'] ) ) {
            $file_count = count( $_files_gallery['name'] );
            for ( $n = 0; $n < $file_count; $n++ ) {
                if ( ! empty( $_files_gallery['name'][ $n ] ) && ! empty( $_files_gallery['type'][ $n ] ) && ! empty( $_files_gallery['tmp_name'][ $n ] ) ) {
                    if ( empty( $_files_gallery['error'][ $n ] ) && file_exists( $_files_gallery['tmp_name'][ $n ] ) ) {
                        $type = wp_check_filetype( $_files_gallery['name'][ $n ] );

                        // Only image allowed.
                        if ( ! empty( $type['type'] ) && 0 === strpos( $type['type'], 'image/' ) ) {
                            $files_data[] = array(
                                'name'     => $_files_gallery['name'][ $n ],
                                'type'     => $type['type'],
                                'tmp_name' => $_files_gallery['tmp_name'][ $n ],
                                'error'    => $_files_gallery['error'][ $n ],
                                'size'     => filesize( $_files_gallery['tmp_name'][ $n ] ), // in byte.
                            );
                        }
                    }
                }
            }
        } // end if().

        // Upload each file.
        foreach ( $files_data as $file_data ) {

            // Load WP Media.
            if ( ! function_exists( 'media_handle_upload' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
                require_once( ABSPATH . 'wp-admin/includes/media.php' );
            }

            // Set files data to upload.
            $_FILES['mas-wpjmcr-gallery'] = $file_data;
            $attachment_id = media_handle_upload( 'mas-wpjmcr-gallery', $post_id );

            if ( ! is_wp_error( $attachment_id ) && $attachment_id ) {
                // Track using attachment/post meta.
                update_post_meta( $attachment_id, 'mas-wpjmcr-gallery', $comment_id );

                // Track using comment meta (multiple).
                add_comment_meta( $comment_id, 'mas-wpjmcr-gallery', $attachment_id, false );
            }
        }

        // Restore original $_FILES entry.
        $_FILES['mas-wpjmcr-gallery'] = $original_files;
    }
}

/**
 * Helper Function to Send Email
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'mas_wpjmcr_send_mail' ) ) {
    function mas_wpjmcr_send_mail( $args ) {
        $sitename = isset( $_SERVER['SERVER_NAME'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) ) : '';
        if ( empty( $sitename ) ) {
            $sitename = (string) wp_parse_url( home_url(), PHP_URL_HOST );
        }
        if ( 0 === strpos( $sitename, 'www.' ) ) {
            $sitename = substr( $sitename, 4 );
        }
        $args_default = array(
            'to'             => get_bloginfo( 'admin_email' ),
            'from'           => 'wordpress@' . $sitename,
            'from_name'      => esc_html__( 'Reviews Notification', 'mas-wp-job-manager-company-reviews' ),
            'reply_to'       => '',
            'subject'        => esc_html__( 'Reviews Notification', 'mas-wp-job-manager-company-reviews' ),
            'message'        => '',
            'content_type'   => 'text/html',
            'charset'        => get_bloginfo( 'charset' ),
        );
        $args = wp_parse_args( $args, $args_default );
        $args = apply_filters( 'mas_wpjmcr_send_mail_args', $args );

        $headers = array(
            'From: "' . wp_strip_all_tags( $args['from_name'] ) . '" <' . sanitize_email( $args['from'] ) . '>',
            'Content-type: ' . sanitize_text_field( $args['content_type'] ) . '; charset: ' . sanitize_text_field( $args['charset'] ),
        );
        if ( ! empty( $args['reply_to'] ) ) {
            $reply_to = sanitize_email( $args['reply_to'] );
            if ( $reply_to ) {
                $headers[] = 'Reply-To: ' . $reply_to;
            }
        }

        return wp_mail( sanitize_email( $args['to'] ), sanitize_text_field( $args['subject'] ), wp_kses_post( $args['message'] ), $headers );
    }
}

/**
 * Moderate Actions.
 *
 * @since 1.0.0
 *
 * @param bool $active True to load only active actions.
 * @return array
 */
if ( ! function_exists( 'mas_wpjmcr_dashboard_actions' ) ) {
    function mas_wpjmcr_dashboard_actions( $active = false ) {
        $actions = array(
            'approve'   => esc_html__( 'Approve', 'mas-wp-job-manager-company-reviews' ),
            'unapprove' => esc_html__( 'Unapprove', 'mas-wp-job-manager-company-reviews' ),
            'spam'      => esc_html__( 'Spam', 'mas-wp-job-manager-company-reviews' ),
            'trash'     => esc_html__( 'Delete', 'mas-wp-job-manager-company-reviews' ),
        );
        $actions = apply_filters( 'mas_wpjmcr_moderate_actions', $actions );

        // Unset inactive actions.
        if ( $active ) {
            $option = get_option( 'mas_wpjmcr_dashboard_actions' );
            $option = is_array( $option ) ? $option : array();
            foreach ( $actions as $action => $action_label ) {
                if ( ! in_array( $action, $option ) ) {
                    unset( $actions[ $action ] );
                }
            }
        }

        return $actions;
    }
}

/**
 * Get Dashboard Notices
 *
 * @since 1.0.0
 *
 * @param bool $clear True to also clear the transient data.
 */
if ( ! function_exists( 'mas_wpjmcr_print_dashboard_notices' ) ) {
    function mas_wpjmcr_print_dashboard_notices( $clear = true ) {
        $name = get_current_user_id() . '_mas_wpjmcr_notices';
        $data = get_transient( $name );
        $data = is_array( $data) ?  $data : array();
        if ( $clear ) {
            delete_transient( $name );
        }
        if ( ! $data ) {
            return;
        }
        foreach( $data as $notice ) {
            printf( '<div class="mas-wpjmcr-notice">%s</div>', wpautop( wp_kses_post( $notice ) ) );
        }
    }
}

/**
 * Comment Gallery Output
 *
 * @since 1.0.0
 *
 * @param int $comment_id Comment ID.
 * @return string
 */
if ( ! function_exists( 'mas_wpjmcr_get_gallery' ) ) {
    function mas_wpjmcr_get_gallery( $comment_id ) {
        $gallery = get_comment_meta( $comment_id, 'mas-wpjmcr-gallery', false );
        if ( ! $gallery || ! is_array( $gallery ) ) {
            return '';
        }
        $gallery = array_filter( array_map( 'absint', $gallery ) );
        if ( empty( $gallery ) ) {
            return '';
        }
        ob_start();
        ?>
        <div class="mas-wpjmcr-gallery">
            <?php echo do_shortcode( '[gallery ids="' . implode( ',', $gallery ) . '"]' ); ?>
        </div>
        <?php
        return apply_filters( 'mas-wpjmcr-gallery-output', ob_get_clean(), $comment_id, $gallery );
    }
}

/**
 * Set Dashboard Notices.
 *
 * @since 1.0.0
 *
 * @param string $notice Notice to set.
 * @return bool
 */
if ( ! function_exists( 'mas_wpjmcr_set_dashboard_notices' ) ) {
    function mas_wpjmcr_set_dashboard_notices( $notice ) {
        $name = get_current_user_id() . '_mas_wpjmcr_notices';
        $data = get_transient( $name );
        $data = is_array( $data ) ? $data : array();
        $data[] = $notice;
        return set_transient( $name, $data, DAY_IN_SECONDS );
    }
}

/**
 * Sanitize Number
 * Added because intval() round the return value,
 *
 * @since 1.0.0
 *
 * @param mixed $input Data to sanitize.
 * @return string
 */
if ( ! function_exists( 'mas_wpjmcr_sanitize_number' ) ) {
    function mas_wpjmcr_sanitize_number( $input ) {
        if ( is_numeric( $input ) ) {
            return $input + 0;
        }
        return 0;
    }
}
