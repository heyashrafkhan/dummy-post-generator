<?php
/**
 * Plugin Name: Dummy Post Generator
 * Description: Generate dummy posts, pages & custom post type for testing purposes.
 * Version: 1.1
 * Author: Ashraf Khan
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Add admin menu
function dpg_add_admin_menu() {
    add_menu_page(
        __( 'Dummy Post Generator', 'dummy-post-generator' ),
        __( 'Dummy Posts', 'dummy-post-generator' ),
        'manage_options',
        'dummy-post-generator',
        'dpg_admin_page',
        'dashicons-text-page',
        30
    );
}
add_action('admin_menu', 'dpg_add_admin_menu');

// Get available post types
function dpg_get_post_types() {
    $args = array(
        'public'   => true,
        '_builtin' => false
    );
    $custom_types = get_post_types($args, 'objects');
    $post_types = array('post' => 'Post', 'page' => 'Page');
    
    foreach ($custom_types as $type) {
        $post_types[$type->name] = $type->label;
    }
    
    return $post_types;
}

// Admin page content
function dpg_admin_page() {
    $post_types = dpg_get_post_types();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Dummy Post Generator', 'dummy-post-generator' ); ?></h1>
        <form method="post" action="">
            <?php wp_nonce_field( 'dpg_generate_posts', 'dpg_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="post_count"><?php esc_html_e( 'Number of Posts', 'dummy-post-generator' ); ?></label></th>
                    <td>
                        <input type="number" id="post_count" name="post_count" min="1" max="100" value="10" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="post_type"><?php esc_html_e( 'Post Type', 'dummy-post-generator' ); ?></label></th>
                    <td>
                        <select id="post_type" name="post_type">
                            <?php foreach ( $post_types as $type => $label ) : ?>
                                <option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button( __( 'Generate Dummy Posts', 'dummy-post-generator' ) ); ?>
        </form>
    </div>
    <?php
}

// Generate dummy posts
function dpg_generate_dummy_posts($count, $post_type) {
    $dummy_content = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.";

    for ($i = 1; $i <= $count; $i++) {
        $post_title = "Dummy " . ucfirst($post_type) . " " . $i;
        $post_content = $dummy_content;

        $post_id = wp_insert_post(array(
            'post_title'    => $post_title,
            'post_content'  => $post_content,
            'post_status'   => 'publish',
            'post_author'   => get_current_user_id(),
            'post_type'     => $post_type
        ));

        if (is_wp_error($post_id)) {
            error_log('Error creating dummy post: ' . $post_id->get_error_message());
        }
    }
}

// Handle form submission
function dpg_handle_form_submission() {
    if (isset($_POST['dpg_nonce']) && wp_verify_nonce($_POST['dpg_nonce'], 'dpg_generate_posts')) {
        if (isset($_POST['post_count']) && is_numeric($_POST['post_count']) && isset($_POST['post_type'])) {
            $post_count = intval($_POST['post_count']);
            $post_type = sanitize_text_field($_POST['post_type']);
            
            if ($post_count > 0 && $post_count <= 100 && post_type_exists($post_type)) {
                dpg_generate_dummy_posts($post_count, $post_type);
                add_action('admin_notices', 'dpg_admin_notice_success');
            } else {
                add_action('admin_notices', 'dpg_admin_notice_error');
            }
        }
    }
}
add_action('admin_init', 'dpg_handle_form_submission');

// Success notice
function dpg_admin_notice_success() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php esc_html_e( 'Dummy posts generated successfully!', 'dummy-post-generator' ); ?></p>
    </div>
    <?php
}

// Error notice
function dpg_admin_notice_error() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php esc_html_e( 'Please enter a valid number of posts (between 1 and 100) and select a valid post type.', 'dummy-post-generator' ); ?></p>
    </div>
    <?php
}