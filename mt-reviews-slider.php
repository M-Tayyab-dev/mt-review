<?php
/**
 * Plugin Name: MT Reviews Slider
 * Description: Displays a customizable reviews carousel with instant AJAX tabbed settings, independent color persistence, and robust security.
 * Version: 3.5.2
 * Author: Muhammad Tayyab
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Register Custom Post Type for Reviews
add_action( 'init', 'mt_register_reviews_cpt' );
function mt_register_reviews_cpt() {
    register_post_type( 'mt_review', array(
        'labels' => array(
            'name' => 'Reviews',
            'singular_name' => 'Review',
            'add_new_item' => 'Add New Review',
            'all_items' => 'All Reviews'
        ),
        'public' => false,
        'show_ui' => true,
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'menu_icon' => 'dashicons-star-filled',
        'capability_type' => 'page',
        'map_meta_cap'    => true,
    ));
}

// 2. Add Settings Page under "Reviews" Menu with Tabs
add_action( 'admin_menu', 'mt_reviews_settings_menu' );
function mt_reviews_settings_menu() {
    add_submenu_page( 
        'edit.php?post_type=mt_review', 
        'Slider Settings', 
        'Settings', 
        'manage_options', 
        'mt-reviews-settings', 
        'mt_reviews_settings_page' 
    );
}

add_action( 'admin_init', 'mt_register_settings' );
function mt_register_settings() {
    register_setting( 'mt_reviews_options', 'mt_text_color', array('default' => '#ffffff', 'sanitize_callback' => 'sanitize_hex_color') );
    register_setting( 'mt_reviews_options', 'mt_card_color', array('default' => '#1a1a1a', 'sanitize_callback' => 'sanitize_hex_color') );
    register_setting( 'mt_reviews_options', 'mt_border_color', array('default' => '#333333', 'sanitize_callback' => 'sanitize_hex_color') );
    register_setting( 'mt_reviews_options', 'mt_accent_color', array('default' => '#d83b64', 'sanitize_callback' => 'sanitize_hex_color') );
    register_setting( 'mt_reviews_options', 'mt_close_hover_color', array('default' => '#b52d50', 'sanitize_callback' => 'sanitize_hex_color') );
    register_setting( 'mt_reviews_options', 'mt_border_radius', array('default' => '10px', 'sanitize_callback' => 'mt_sanitize_css_length') );
    register_setting( 'mt_reviews_options', 'mt_max_lines', array('default' => '4', 'sanitize_callback' => 'absint') );
    register_setting( 'mt_reviews_options', 'mt_button_text', array('default' => 'Read Full Review', 'sanitize_callback' => 'sanitize_text_field') );
    register_setting( 'mt_reviews_options', 'mt_button_icon_url', array('default' => '', 'sanitize_callback' => 'esc_url_raw') );
    register_setting( 'mt_reviews_options', 'mt_enable_slider', array('default' => 'yes', 'sanitize_callback' => 'sanitize_text_field') );
    register_setting( 'mt_reviews_options', 'mt_slides_per_view', array('default' => '3', 'sanitize_callback' => 'absint') );
    register_setting( 'mt_reviews_options', 'mt_posts_per_page', array('default' => '-1', 'sanitize_callback' => 'intval') );
    register_setting( 'mt_reviews_options', 'mt_autoplay_speed', array('default' => '3000', 'sanitize_callback' => 'absint') );
    register_setting( 'mt_reviews_options', 'mt_pause_on_hover', array('default' => 'yes', 'sanitize_callback' => 'sanitize_text_field') );
    register_setting( 'mt_reviews_options', 'mt_arrow_color', array('default' => '#d83b64', 'sanitize_callback' => 'sanitize_hex_color') );
}

function mt_sanitize_css_length( $value ) {
    $value = trim( (string) $value );
    if ( preg_match( '/^\d{1,4}(?:\.\d{1,2})?(px|%|em|rem)$/', $value ) ) {
        return $value;
    }
    return '10px';
}

function mt_reviews_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    wp_enqueue_media();
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
    
    $updated = isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] === 'true';
    ?>
    <div class="wrap">
        <h1>MT Reviews Settings</h1>
        
        <?php if ( $updated ) : ?>
            <div class="notice notice-success is-dismissible"><p><strong>Settings saved successfully.</strong></p></div>
        <?php endif; ?>

        <h2 class="nav-tab-wrapper" id="mt-settings-tabs">
            <a href="#general_settings" class="nav-tab nav-tab-active" data-target="mt-tab-general">General Settings</a>
            <a href="#slider_settings" class="nav-tab" data-target="mt-tab-slider">Slider Settings</a>
        </h2>

        <form method="post" action="options.php">
            <?php settings_fields( 'mt_reviews_options' ); ?>
            
            <div id="mt-tab-general" class="mt-tab-content">
                <table class="form-table">
                    <?php 
                    $colors = array(
                        'mt_text_color'        => array('label' => 'Text Color', 'default' => '#ffffff'),
                        'mt_card_color'        => array('label' => 'Card Background Color', 'default' => '#1a1a1a'),
                        'mt_border_color'      => array('label' => 'Border Color', 'default' => '#333333'),
                        'mt_accent_color'      => array('label' => 'Accent / Button Hover Color', 'default' => '#d83b64'),
                        'mt_close_hover_color' => array('label' => 'Close Icon Hover Color', 'default' => '#b52d50'),
                    );
                    foreach($colors as $opt_name => $opt_data):
                        $val = get_option($opt_name, $opt_data['default']);
                    ?>
                    <tr>
                        <th scope="row"><?php echo esc_html($opt_data['label']); ?></th>
                        <td><input type="text" name="<?php echo esc_attr($opt_name); ?>" value="<?php echo esc_attr($val); ?>" class="mt-color-field" data-default-color="<?php echo esc_attr($opt_data['default']); ?>" /></td>
                    </tr>
                    <?php endforeach; ?>

                    <tr>
                        <th scope="row">Button Icon Image</th>
                        <td>
                            <?php $icon_url = get_option('mt_button_icon_url'); ?>
                            <input type="text" id="mt_button_icon_url" name="mt_button_icon_url" value="<?php echo esc_attr( $icon_url ); ?>" style="width:60%;" />
                            <button type="button" class="button" id="mt_upload_icon_btn">Upload / Choose Icon</button>
                            <button type="button" class="button" id="mt_remove_icon_btn" style="color:red;">Remove Icon</button>
                        </td>
                    </tr>
                    <tr><th scope="row">Read More Button Text</th><td><input type="text" name="mt_button_text" value="<?php echo esc_attr( get_option('mt_button_text', 'Read Full Review') ); ?>" style="width:60%;" /></td></tr>
                    <tr><th scope="row">Border Radius (e.g., 10px)</th><td><input type="text" name="mt_border_radius" value="<?php echo esc_attr( get_option('mt_border_radius', '10px') ); ?>" /></td></tr>
                    <tr><th scope="row">Max Lines of Comment</th><td><input type="number" name="mt_max_lines" value="<?php echo esc_attr( get_option('mt_max_lines', '4') ); ?>" min="1" /></td></tr>
                </table>
            </div>

            <div id="mt-tab-slider" class="mt-tab-content" style="display:none;">
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Slider Carousel</th>
                        <td>
                            <select name="mt_enable_slider">
                                <option value="yes" <?php selected( get_option('mt_enable_slider', 'yes'), 'yes' ); ?>>Yes (Carousel Slider)</option>
                                <option value="no" <?php selected( get_option('mt_enable_slider', 'yes'), 'no' ); ?>>No (Static Grid)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Reviews to Show per Row</th>
                        <td>
                            <select name="mt_slides_per_view">
                                <option value="1" <?php selected( get_option('mt_slides_per_view', '3'), '1' ); ?>>1 Column</option>
                                <option value="2" <?php selected( get_option('mt_slides_per_view', '3'), '2' ); ?>>2 Columns</option>
                                <option value="3" <?php selected( get_option('mt_slides_per_view', '3'), '3' ); ?>>3 Columns</option>
                                <option value="4" <?php selected( get_option('mt_slides_per_view', '3'), '4' ); ?>>4 Columns</option>
                            </select>
                        </td>
                    </tr>
                    <tr><th scope="row">Total Reviews to Display (-1 for All)</th><td><input type="number" name="mt_posts_per_page" value="<?php echo esc_attr( get_option('mt_posts_per_page', '-1') ); ?>" /></td></tr>
                    <tr><th scope="row">Slider Moving Speed / Time (Milliseconds)</th><td><input type="number" name="mt_autoplay_speed" value="<?php echo esc_attr( get_option('mt_autoplay_speed', '3000') ); ?>" step="100" min="500" /> <p class="description">e.g., 3000 = 3 seconds per slide.</p></td></tr>
                    <tr>
                        <th scope="row">Pause on Hover</th>
                        <td>
                            <select name="mt_pause_on_hover">
                                <option value="yes" <?php selected( get_option('mt_pause_on_hover', 'yes'), 'yes' ); ?>>Yes</option>
                                <option value="no" <?php selected( get_option('mt_pause_on_hover', 'yes'), 'no' ); ?>>No</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Arrow Navigation Color</th>
                        <td><input type="text" name="mt_arrow_color" value="<?php echo esc_attr( get_option('mt_arrow_color', '#d83b64') ); ?>" class="mt-color-field" data-default-color="#d83b64" /></td>
                    </tr>
                </table>
            </div>

            <?php submit_button(); ?>
        </form>
    </div>
    <script>
    jQuery(document).ready(function($){
        $('.mt-color-field').wpColorPicker();

        $('#mt-settings-tabs').on('click', 'a', function(e) {
            e.preventDefault();
            $('#mt-settings-tabs a').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');

            var target = $(this).attr('data-target');
            $('.mt-tab-content').hide();
            $('#' + target).fadeIn(150);
        });

        var mediaUploader;
        $('#mt_upload_icon_btn').click(function(e) {
            e.preventDefault();
            if (mediaUploader) { mediaUploader.open(); return; }
            mediaUploader = wp.media.frames.file_frame = wp.media({ title: 'Choose Icon', button: { text: 'Choose Icon' }, multiple: false });
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#mt_button_icon_url').val(attachment.url);
            });
            mediaUploader.open();
        });
        $('#mt_remove_icon_btn').click(function(e) {
            e.preventDefault();
            $('#mt_button_icon_url').val('');
        });
    });
    </script>
    <?php
}

add_action( 'add_meta_boxes', 'mt_review_stars_meta' );
function mt_review_stars_meta() {
    add_meta_box( 'mt_review_stars', 'Star Rating (1-5)', 'mt_review_stars_callback', 'mt_review', 'side' );
}
function mt_review_stars_callback( $post ) {
    wp_nonce_field( 'mt_save_review_stars_data', 'mt_review_stars_meta_box_nonce' );
    $rating = get_post_meta( $post->ID, '_mt_review_stars', true );
    echo '<input type="number" name="mt_review_stars" value="' . esc_attr( $rating ? $rating : 5 ) . '" min="1" max="5" style="width:100%;" />';
}
add_action( 'save_post', 'mt_save_review_stars' );
function mt_save_review_stars( $post_id ) {
    if ( ! isset( $_POST['mt_review_stars_meta_box_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['mt_review_stars_meta_box_nonce'], 'mt_save_review_stars_data' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['mt_review_stars'] ) ) {
        $rating = absint( $_POST['mt_review_stars'] );
        if ( $rating > 5 ) $rating = 5;
        update_post_meta( $post_id, '_mt_review_stars', $rating );
    }
}

add_action( 'wp_enqueue_scripts', 'mt_enqueue_swiper' );
function mt_enqueue_swiper() {
    $swiper_version = '10.3.1';
    wp_enqueue_style( 'swiper-css', "https://cdn.jsdelivr.net/npm/swiper@{$swiper_version}/swiper-bundle.min.css", array(), $swiper_version );
    wp_enqueue_script( 'swiper-js', "https://cdn.jsdelivr.net/npm/swiper@{$swiper_version}/swiper-bundle.min.js", array(), $swiper_version, true );
}

if ( ! defined( 'MT_SWIPER_JS_SRI' ) )  define( 'MT_SWIPER_JS_SRI', '' );
if ( ! defined( 'MT_SWIPER_CSS_SRI' ) ) define( 'MT_SWIPER_CSS_SRI', '' );

add_filter( 'script_loader_tag', 'mt_add_swiper_sri_script', 10, 2 );
function mt_add_swiper_sri_script( $tag, $handle ) {
    if ( $handle === 'swiper-js' && MT_SWIPER_JS_SRI !== '' ) {
        $tag = str_replace( ' src=', ' integrity="' . esc_attr( MT_SWIPER_JS_SRI ) . '" crossorigin="anonymous" src=', $tag );
    }
    return $tag;
}
add_filter( 'style_loader_tag', 'mt_add_swiper_sri_style', 10, 2 );
function mt_add_swiper_sri_style( $tag, $handle ) {
    if ( $handle === 'swiper-css' && MT_SWIPER_CSS_SRI !== '' ) {
        $tag = str_replace( ' href=', ' integrity="' . esc_attr( MT_SWIPER_CSS_SRI ) . '" crossorigin="anonymous" href=', $tag );
    }
    return $tag;
}

function mt_get_review_plain_text() {
    return esc_html( wp_strip_all_tags( get_the_content() ) );
}

add_shortcode( 'mt_reviews_slider', 'mt_reviews_slider_shortcode' );
function mt_reviews_slider_shortcode() {
    $text_color        = get_option('mt_text_color', '#ffffff');
    $card_color        = get_option('mt_card_color', '#1a1a1a');
    $border_color      = get_option('mt_border_color', '#333333');
    $border_radius     = get_option('mt_border_radius', '10px');
    $max_lines         = get_option('mt_max_lines', '4');
    $accent_color      = get_option('mt_accent_color', '#d83b64');
    $close_hover_color = get_option('mt_close_hover_color', '#b52d50');
    $button_text       = get_option('mt_button_text', 'Read Full Review');
    $button_icon_url   = get_option('mt_button_icon_url', '');

    $enable_slider     = get_option('mt_enable_slider', 'yes');
    $slides_per_view   = intval( get_option('mt_slides_per_view', 3) );
    $posts_per_page    = intval( get_option('mt_posts_per_page', -1) );
    $autoplay_speed    = intval( get_option('mt_autoplay_speed', 3000) );
    $pause_on_hover    = get_option('mt_pause_on_hover', 'yes');
    $arrow_color       = get_option('mt_arrow_color', '#d83b64');

    $query = new WP_Query( array( 'post_type' => 'mt_review', 'posts_per_page' => $posts_per_page ) );
    if ( ! $query->have_posts() ) return 'No reviews found.';

    ob_start();
    $unique_id = 'mt_swiper_' . wp_rand( 100, 999 );
    ?>
    <style>
        .mt-reviews-wrapper { 
            width: 100%; 
            box-sizing: border-box; 
            padding: 20px 0 40px 0; 
            position: relative;
        }
        .mt-reviews-grid { 
            display: grid;
            grid-template-columns: repeat(<?php echo esc_attr($slides_per_view); ?>, 1fr);
            gap: 25px;
            width: 100%;
        }
        @media (max-width: 1024px) {
            .mt-reviews-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .mt-reviews-grid { grid-template-columns: 1fr; }
        }
        .mt-review-card {
            background-color: <?php echo esc_attr($card_color); ?>;
            border: 1px solid <?php echo esc_attr($border_color); ?>;
            border-radius: <?php echo esc_attr($border_radius); ?>;
            padding: 30px;
            box-sizing: border-box;
            color: <?php echo esc_attr($text_color); ?>;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }
        .mt-review-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .mt-review-avatar {
            flex-shrink: 0;
            margin-right: 15px;
        }
        .mt-review-avatar img {
            width: 60px !important;
            height: 60px !important;
            min-width: 60px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
        }
        .mt-review-name { 
            margin: 0 0 5px 0; 
            font-size: 18px; 
            font-weight: 600; 
            color: <?php echo esc_attr($text_color); ?>; 
            line-height: 1.2;
        }
        .mt-review-stars { 
            color: #d4af37; 
            font-size: 16px; 
            letter-spacing: 2px; 
            line-height: 1;
        }
        .mt-review-body {
            position: relative;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .mt-review-content {
            display: -webkit-box;
            -webkit-line-clamp: <?php echo esc_attr($max_lines); ?>;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 14px;
            line-height: 1.6;
            color: <?php echo esc_attr($text_color); ?>;
            margin: 0;
        }
        .mt-read-more-btn {
            background: none;
            border: none;
            color: #d4af37;
            cursor: pointer;
            font-size: 13px;
            padding: 6px 12px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 15px;
            align-self: flex-start;
            transition: background 0.3s ease, color 0.3s ease;
        }
        .mt-read-more-btn img {
            width: 16px;
            height: 16px;
            object-fit: contain;
        }
        .mt-read-more-btn:hover {
            background-color: <?php echo esc_attr($accent_color); ?>;
            color: #ffffff;
        }

        /* Swiper Navigation and Pagination Customization */
        .<?php echo esc_attr($unique_id); ?> .swiper-button-next,
        .<?php echo esc_attr($unique_id); ?> .swiper-button-prev {
            color: <?php echo esc_attr($arrow_color); ?>;
            background: rgba(0,0,0,0.5);
            padding: 20px;
            border-radius: 50%;
            width: 40px;
            height: 40px;
        }
        .<?php echo esc_attr($unique_id); ?> .swiper-button-next:after,
        .<?php echo esc_attr($unique_id); ?> .swiper-button-prev:after {
            font-size: 18px;
            font-weight: bold;
        }
        .<?php echo esc_attr($unique_id); ?> .swiper-pagination-bullet-active {
            background: <?php echo esc_attr($arrow_color); ?>;
        }
        .<?php echo esc_attr($unique_id); ?> .swiper-pagination {
            bottom: 0px !important;
        }

        /* Modal Styles */
        .mt-review-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .mt-review-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .mt-review-modal-content {
            background-color: <?php echo esc_attr($card_color); ?>;
            border: 1px solid <?php echo esc_attr($border_color); ?>;
            border-radius: <?php echo esc_attr($border_radius); ?>;
            padding: 40px;
            width: 90%;
            max-width: 600px;
            color: <?php echo esc_attr($text_color); ?>;
            position: relative;
            box-shadow: 0 15px 35px rgba(0,0,0,0.6);
            max-height: 80vh;
            overflow-y: auto;
        }
        .mt-review-modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: <?php echo esc_attr($accent_color); ?>;
            border: none;
            border-radius: 4px;
            color: #ffffff;
            width: 32px;
            height: 32px;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .mt-review-modal-close:hover {
            background-color: <?php echo esc_attr($close_hover_color); ?>;
        }
        .mt-review-modal-body {
            font-size: 15px;
            line-height: 1.8;
            margin-top: 20px;
        }
    </style>

    <div class="mt-reviews-wrapper">
        <?php if ( $enable_slider === 'yes' ) : ?>
            <div class="swiper <?php echo esc_attr($unique_id); ?>">
                <div class="swiper-wrapper">
                    <?php 
                    $modal_counter = 0;
                    $modals_html = '';
                    while ( $query->have_posts() ) : $query->the_post(); 
                        $modal_counter++;
                        $stars = get_post_meta( get_the_ID(), '_mt_review_stars', true ) ?: 5;
                        $stars = max( 1, min( 5, absint( $stars ) ) );
                        $star_html = str_repeat('★', $stars) . str_repeat('☆', 5 - $stars);
                        $full_content = mt_get_review_plain_text();
                    ?>
                        <div class="swiper-slide" style="height: auto; display: flex;">
                            <div class="mt-review-card" style="width: 100%;">
                                <div>
                                    <div class="mt-review-header">
                                        <div class="mt-review-avatar">
                                            <?php if ( has_post_thumbnail() ) { 
                                                the_post_thumbnail( 'thumbnail' ); 
                                            } else { 
                                                echo '<div style="width:60px;height:60px;background:#555;border-radius:50%;"></div>'; 
                                            } ?>
                                        </div>
                                        <div class="mt-review-meta">
                                            <h4 class="mt-review-name"><?php echo esc_html( get_the_title() ); ?></h4>
                                            <div class="mt-review-stars"><?php echo esc_html( $star_html ); ?></div>
                                        </div>
                                    </div>
                                    <div class="mt-review-body">
                                        <div class="mt-review-content">
                                            <?php echo $full_content; ?>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <button class="mt-read-more-btn" onclick="openMtModal('<?php echo esc_attr($unique_id); ?>-<?php echo $modal_counter; ?>')">
                                        <?php if ( ! empty( $button_icon_url ) ) : ?>
                                            <img src="<?php echo esc_url( $button_icon_url ); ?>" alt="icon" />
                                        <?php endif; ?>
                                        <span><?php echo esc_html( $button_text ); ?></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <?php 
                        // Capture modal markup safely outside the swiper track loop to prevent clipping
                        ob_start(); 
                        ?>
                        <div class="mt-review-modal-overlay" id="mt-modal-<?php echo esc_attr($unique_id); ?>-<?php echo $modal_counter; ?>">
                            <div class="mt-review-modal-content">
                                <button class="mt-review-modal-close" onclick="closeMtModal('<?php echo esc_attr($unique_id); ?>-<?php echo $modal_counter; ?>')">&times;</button>
                                <div class="mt-review-header">
                                    <div class="mt-review-avatar">
                                        <?php if ( has_post_thumbnail() ) { 
                                            the_post_thumbnail( 'thumbnail' ); 
                                        } else { 
                                            echo '<div style="width:60px;height:60px;background:#555;border-radius:50%;"></div>'; 
                                        } ?>
                                    </div>
                                    <div class="mt-review-meta">
                                        <h4 class="mt-review-name"><?php echo esc_html( get_the_title() ); ?></h4>
                                        <div class="mt-review-stars"><?php echo esc_html( $star_html ); ?></div>
                                    </div>
                                </div>
                                <div class="mt-review-modal-body">
                                    <?php echo $full_content; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                        $modals_html .= ob_get_clean();
                        ?>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
                <!-- Navigation Arrows -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <!-- Pagination Bullets -->
                <div class="swiper-pagination"></div>
            </div>

            <!-- Render modals outside swiper wrapper container -->
            <?php echo $modals_html; ?>

            <script>
                window.addEventListener('load', function() {
                    if (typeof Swiper !== 'undefined') {
                        new Swiper('.<?php echo esc_js($unique_id); ?>', {
                            slidesPerView: <?php echo esc_js($slides_per_view); ?>,
                            spaceBetween: 25,
                            loop: true,
                            autoplay: {
                                delay: <?php echo esc_js($autoplay_speed); ?>,
                                disableOnInteraction: false,
                                pauseOnMouseEnter: <?php echo $pause_on_hover === 'yes' ? 'true' : 'false'; ?>
                            },
                            navigation: {
                                nextEl: '.<?php echo esc_js($unique_id); ?> .swiper-button-next',
                                prevEl: '.<?php echo esc_js($unique_id); ?> .swiper-button-prev',
                            },
                            pagination: {
                                el: '.<?php echo esc_js($unique_id); ?> .swiper-pagination',
                                clickable: true,
                            },
                            breakpoints: {
                                0: { slidesPerView: 1 },
                                768: { slidesPerView: Math.min(2, <?php echo esc_js($slides_per_view); ?>) },
                                1024: { slidesPerView: <?php echo esc_js($slides_per_view); ?> }
                            }
                        });
                    }
                });
            </script>
        <?php else : ?>
            <div class="mt-reviews-grid">
                <?php 
                $modal_counter = 0;
                $modals_html = '';
                while ( $query->have_posts() ) : $query->the_post(); 
                    $modal_counter++;
                    $stars = get_post_meta( get_the_ID(), '_mt_review_stars', true ) ?: 5;
                    $stars = max( 1, min( 5, absint( $stars ) ) );
                    $star_html = str_repeat('★', $stars) . str_repeat('☆', 5 - $stars);
                    $full_content = mt_get_review_plain_text();
                ?>
                    <div class="mt-review-card">
                        <div>
                            <div class="mt-review-header">
                                <div class="mt-review-avatar">
                                    <?php if ( has_post_thumbnail() ) { 
                                        the_post_thumbnail( 'thumbnail' ); 
                                    } else { 
                                        echo '<div style="width:60px;height:60px;background:#555;border-radius:50%;"></div>'; 
                                    } ?>
                                </div>
                                <div class="mt-review-meta">
                                    <h4 class="mt-review-name"><?php echo esc_html( get_the_title() ); ?></h4>
                                    <div class="mt-review-stars"><?php echo esc_html( $star_html ); ?></div>
                                </div>
                            </div>
                            <div class="mt-review-body">
                                <div class="mt-review-content">
                                    <?php echo $full_content; ?>
                                </div>
                            </div>
                        </div>
                        <div>
                            <button class="mt-read-more-btn" onclick="openMtModal('<?php echo esc_attr($unique_id); ?>-<?php echo $modal_counter; ?>')">
                                <?php if ( ! empty( $button_icon_url ) ) : ?>
                                    <img src="<?php echo esc_url( $button_icon_url ); ?>" alt="icon" />
                                <?php endif; ?>
                                <span><?php echo esc_html( $button_text ); ?></span>
                            </button>
                        </div>
                    </div>

                    <?php 
                    ob_start(); 
                    ?>
                    <div class="mt-review-modal-overlay" id="mt-modal-<?php echo esc_attr($unique_id); ?>-<?php echo $modal_counter; ?>">
                        <div class="mt-review-modal-content">
                            <button class="mt-review-modal-close" onclick="closeMtModal('<?php echo esc_attr($unique_id); ?>-<?php echo $modal_counter; ?>')">&times;</button>
                            <div class="mt-review-header">
                                <div class="mt-review-avatar">
                                    <?php if ( has_post_thumbnail() ) { 
                                        the_post_thumbnail( 'thumbnail' ); 
                                    } else { 
                                        echo '<div style="width:60px;height:60px;background:#555;border-radius:50%;"></div>'; 
                                    } ?>
                                </div>
                                <div class="mt-review-meta">
                                    <h4 class="mt-review-name"><?php echo esc_html( get_the_title() ); ?></h4>
                                    <div class="mt-review-stars"><?php echo esc_html( $star_html ); ?></div>
                                </div>
                            </div>
                            <div class="mt-review-modal-body">
                                <?php echo $full_content; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    $modals_html .= ob_get_clean();
                    ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            <?php echo $modals_html; ?>
        <?php endif; ?>
    </div>

    <script>
        function openMtModal(id) {
            document.getElementById('mt-modal-' + id).classList.add('active');
        }
        function closeMtModal(id) {
            document.getElementById('mt-modal-' + id).classList.remove('active');
        }
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('mt-review-modal-overlay')) {
                e.target.classList.remove('active');
            }
        });
    </script>
    <?php
    return ob_get_clean();
}