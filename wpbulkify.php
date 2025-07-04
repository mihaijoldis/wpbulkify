<?php
/**
 * Plugin Name: WPBulkify
 * Plugin URI: https://wpbulkify.com/
 * Author URI: https://profiles.wordpress.org/wpbulkify/
 * Description: Enables bulk plugin installation via the WPBulkify browser extension.
 * Version: 1.0.0
 * Author: WPBulkify
 * License: GPL v2 or later
 * Text Domain: wpbulkify
 *  
 * WPBulkify is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WPBulkify is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WPBulkify. If not, see <http://www.gnu.org/licenses/>.
 */

 // Prevent direct access
if ( ! defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPB_VERSION', '1.0.0');
define('WPB_PLUGIN_DIR', plugin_dir_path(__FILE__) );
define('WPB_PLUGIN_URL', plugin_dir_url(__FILE__) );

// Include required files
require_once WPB_PLUGIN_DIR . 'includes/class-plugin-installer.php';
require_once WPB_PLUGIN_DIR . 'includes/class-rest-api.php';
require_once WPB_PLUGIN_DIR . 'includes/class-ajax-handler.php';

// Initialize the plugin
add_action('plugins_loaded', function() {
    if ( class_exists('WPB_REST_API') ) {
        new WPB_REST_API();
    }

    if ( class_exists('WPB_Ajax_Handler') ) {
        new WPB_Ajax_Handler();
    }
});

// Add admin notice for successful activation
add_action('admin_notices', function() {
    if ( get_transient('wpb_activated') ) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('WPBulkify is now active! Your browser extension can now perform bulk plugin installations.', 'wpbulkify'); ?></p>
        </div>
        <?php
        delete_transient('wpb_activated');
    }
});

// Set transient on activation
register_activation_hook(__FILE__, function() {
    set_transient('wpb_activated', true, 5);
});

// Expose helper object to frontend
add_action('admin_footer', function() {
    $wpb_nonce = esc_attr( wp_create_nonce('wp_rest') );
    $wpb_version = esc_attr( WPB_VERSION );
    $wpb_ajax_url = esc_url( admin_url('admin-ajax.php') );
    $wpb_rest_url = esc_url( rest_url('wpbulkify/v1') );
    ?>
    <script>
    (function() {
        if (localStorage.getItem('wpb_debug_mode') === 'true') {
            console.log('[WPBulkify] admin_footer hook executing...');
        }
        // Initialize helper object
        if (!window.wpbHelper) {
            window.wpbHelper = {
                version: '<?php echo $wpb_version; ?>',
                ajaxUrl: '<?php echo $wpb_ajax_url; ?>',
                restUrl: '<?php echo $wpb_rest_url; ?>',
                restNonce: '<?php echo $wpb_nonce; ?>',
                initialized: true
            };
            if (localStorage.getItem('wpb_debug_mode') === 'true') {
                console.log('[WPBulkify] Initialized in footer:', window.wpbHelper);
            }
        } else {
            if (localStorage.getItem('wpb_debug_mode') === 'true') {
                console.log('[WPBulkify] Already initialized:', window.wpbHelper);
            }
        }
        
        // Add data attributes to body for easier detection
        document.body.setAttribute('data-wpb-helper', 'active');
        document.body.setAttribute('data-wpb-nonce', '<?php echo $wpb_nonce; ?>');
        if (localStorage.getItem('wpb_debug_mode') === 'true') {
            console.log('[WPBulkify] Added data-wpb-helper attribute to body');
            console.log('[WPBulkify] Added nonce to body:', '<?php echo $wpb_nonce; ?>');
        }
    })();
    </script>
    <?php
});