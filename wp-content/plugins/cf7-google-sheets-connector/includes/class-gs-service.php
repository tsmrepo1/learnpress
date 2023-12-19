<?php
/**
 * Service class for Google Sheet Connector
 * @since 1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
   exit; // Exit if accessed directly
}

/**
 * Gs_Connector_Service Class
 *
 * @since 1.0
 */
class Gs_Connector_Service {

	private $allowed_tags = array( 'text', 'email', 'url', 'tel', 'number', 'range', 'date', 'textarea', 'select', 'checkbox', 'radio', 'acceptance', 'quiz', 'file', 'hidden' );

	private $special_mail_tags = array( 'date', 'time', 'serial_number', 'remote_ip', 'user_agent', 'url', 'post_id', 'post_name', 'post_title', 'post_url', 'post_author', 'post_author_email', 'site_title', 'site_description', 'site_url', 'site_admin_email', 'user_login', 'user_email', 'user_display_name' ); 
	
	protected $gs_uploads   = array();
   
   /**
    *  Set things up.
    *  @since 1.0
    */
   public function __construct() {
      add_action( 'wp_ajax_verify_gs_integation', array( $this, 'verify_gs_integation' ) );
      add_action( 'wp_ajax_gs_clear_log', array( $this, 'gs_clear_logs' ) );
      
      add_action( 'wp_ajax_deactivate_gs_integation', array( $this, 'deactivate_gs_integation' ) );

    // clear debug logs method using ajax for system status tab
    add_action('wp_ajax_cf7_clear_debug_log', array($this, 'cf7_clear_debug_logs'));

      // Add new tab to contact form 7 editors panel
      add_filter( 'wpcf7_editor_panels', array( $this, 'cf7_gs_editor_panels' ) );

      add_action( 'wpcf7_after_save', array( $this, 'save_gs_settings' ) );
      add_action( 'wpcf7_before_send_mail', array( $this, 'save_uploaded_files_local' ) );
      add_action( 'wpcf7_mail_sent', array( $this, 'cf7_save_to_google_sheets' ) );


      //add_action( 'admin_notices', array( $this, 'display_upgrade_notice' ) );
      
      //add_action( 'wp_ajax_set_upgrade_notification_interval', array( $this, 'set_upgrade_notification_interval' ) );
      //add_action( 'wp_ajax_close_upgrade_notification_interval', array( $this, 'close_upgrade_notification_interval' ) );
   }


/**
     * Build System Information String
     * @global object $wpdb
     * @return string
     * @since 5.3
     */
    public function get_cf7gs_system_info() {

        global $wpdb;

        // Get WordPress version
        $wp_version = get_bloginfo('version');

        // Get theme info
        $theme_data = wp_get_theme();
        $theme_name_version = $theme_data->get('Name') . ' ' . $theme_data->get('Version');
        $parent_theme = $theme_data->get('Template');

        if (!empty($parent_theme)) {
            $parent_theme_data = wp_get_theme($parent_theme);
            $parent_theme_name_version = $parent_theme_data->get('Name') . ' ' . $parent_theme_data->get('Version');
        } else {
            $parent_theme_name_version = 'N/A';
        }

        
        // Check plugin version and subscription plan
        $plugin_version = defined('GS_CONNECTOR_VERSION') ? GS_CONNECTOR_VERSION : 'N/A';
        $subscription_plan = 'FREE';

        // Check Google Account Authentication
        // $api_token = get_option('gs_token');
        // $google_sheet = new CF7GSC_googlesheet_PRO();
        // $email_account = $google_sheet->gsheet_print_google_account_email();

        $api_token_auto = get_option('gs_token');
       

        if (!empty($api_token_auto)) {
            // The user is authenticated through the auto method
            $google_sheet_auto = new CF7GSC_googlesheet();
            
            $email_account_auto = $google_sheet_auto->gsheet_print_google_account_email();
            $connected_email = !empty($email_account_auto) ? esc_html($email_account_auto) : 'Not Auth';
        }  else {
            // Neither auto nor manual authentication is available
            $connected_email = 'Not Auth';
        }

        
        // Check Google Permission
        $gs_verify_status = get_option('gs_verify');
        $search_permission = ($gs_verify_status === 'valid') ? 'Given' : 'Not Given';

    
        // Create the system info HTML
        $system_info = '<div class="system-statuswc">';
        $system_info .= '<h4><button id="show-info-button" class="info-button">GSheetConnector<span class="dashicons dashicons-arrow-down"></span></h4>';
        $system_info .= '<div id="info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>GSheetConnector</h3>';
        $system_info .= '<table>';
        $system_info .= '<tr><td>Plugin Version</td><td>' . esc_html($plugin_version) . '</td></tr>';
        $system_info .= '<tr><td>Plugin Subscription Plan</td><td>' . esc_html($subscription_plan) . '</td></tr>';
        $system_info .= '<tr><td>Connected Email Account</td><td>' . $connected_email . '</td></tr>';
        $system_info .= '<tr><td>Google Drive Permission</td><td>' . esc_html($search_permission) . '</td></tr>';
        $system_info .= '<tr><td>Google Sheet Permission</td><td>' . esc_html($search_permission) . '</td></tr>';
        $system_info .= '</table>';
        $system_info .= '</div>';
         // Add WordPress info
        // Create a button for WordPress info
        $system_info .= '<h2><button id="show-wordpress-info-button" class="info-button">WordPress Info<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="wordpress-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>WordPress Info</h3>';
        $system_info .= '<table>';
        $system_info .= '<tr><td>Version</td><td>' . get_bloginfo('version') . '</td></tr>';
        $system_info .= '<tr><td>Site Language</td><td>' . get_bloginfo('language') . '</td></tr>';
        $system_info .= '<tr><td>Debug Mode</td><td>' . (WP_DEBUG ? 'Enabled' : 'Disabled') . '</td></tr>';
        $system_info .= '<tr><td>Home URL</td><td>' . get_home_url() . '</td></tr>';
        $system_info .= '<tr><td>Site URL</td><td>' . get_site_url() . '</td></tr>';
        $system_info .= '<tr><td>Permalink structure</td><td>' . get_option('permalink_structure') . '</td></tr>';
        $system_info .= '<tr><td>Is this site using HTTPS?</td><td>' . (is_ssl() ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>Is this a multisite?</td><td>' . (is_multisite() ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>Can anyone register on this site?</td><td>' . (get_option('users_can_register') ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>Is this site discouraging search engines?</td><td>' . (get_option('blog_public') ? 'No' : 'Yes') . '</td></tr>';
        $system_info .= '<tr><td>Default comment status</td><td>' . get_option('default_comment_status') . '</td></tr>';

        $server_ip = $_SERVER['REMOTE_ADDR'];
        if ($server_ip == '127.0.0.1' || $server_ip == '::1') {
            $environment_type = 'localhost';
        } else {
            $environment_type = 'production';
        }
        $system_info .= '<tr><td>Environment type</td><td>' . esc_html($environment_type) . '</td></tr>';

        $user_count = count_users();
        $total_users = $user_count['total_users'];
        $system_info .= '<tr><td>User Count</td><td>' . esc_html($total_users) . '</td></tr>';

        $system_info .= '<tr><td>Communication with WordPress.org</td><td>' . (get_option('blog_publicize') ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '</table>';
        $system_info .= '</div>';

        // info about active theme
        $active_theme = wp_get_theme();

        $system_info .= '<h2><button id="show-active-info-button" class="info-button">Active Theme<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="active-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>Active Theme</h3>';
        $system_info .= '<table>';
        $system_info .= '<tr><td>Name</td><td>' . $active_theme->get('Name') .'</td></tr>';
        $system_info .= '<tr><td>Version</td><td>' . $active_theme->get('Version') .'</td></tr>';
        $system_info .= '<tr><td>Author</td><td>' . $active_theme->get('Author') .'</td></tr>';
        $system_info .= '<tr><td>Author website</td><td>' . $active_theme->get('AuthorURI') .'</td></tr>';
        $system_info .= '<tr><td>Theme directory location</td><td>' . $active_theme->get_template_directory() .'</td></tr>';
        $system_info .= '</table>';
        $system_info .= '</div>';

        // Get a list of other plugins you want to check compatibility with
        $other_plugins = array(
            'plugin-folder/plugin-file.php', // Replace with the actual plugin slug
            // Add more plugins as needed
        );

        // Network Active Plugins
        if (is_multisite()) {
           $network_active_plugins = get_site_option('active_sitewide_plugins', array());
           if (!empty($network_active_plugins)) {
               $system_info .= '<h2><button id="show-netplug-info-button" class="info-button">Network Active plugins<span class="dashicons dashicons-arrow-down"></span></h2>';
               $system_info .= '<div id="netplug-info-container" class="info-content" style="display:none;">';
               $system_info .= '<h3>Network Active plugins</h3>';
               $system_info .= '<table>';
               foreach ($network_active_plugins as $plugin => $plugin_data) {
                   $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
                   $system_info .= '<tr><td>' . $plugin_data['Name'] . '</td><td>' . $plugin_data['Version'] . '</td></tr>';
               }
               // Add more network active plugin statuses here...
                $system_info .= '</table>';
                $system_info .= '</div>';
           }
        }
        // Active plugins
        $system_info .= '<h2><button id="show-acplug-info-button" class="info-button">Active plugins<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="acplug-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>Active plugins</h3>';
        $system_info .= '<table>';

        // Retrieve all active plugins data
        $active_plugins_data = array();
        $active_plugins = get_option('active_plugins', array());
        foreach ($active_plugins as $plugin) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
            $active_plugins_data[$plugin] = array(
                'name'    => $plugin_data['Name'],
                'version' => $plugin_data['Version'],
                'count'   => 0, // Initialize the count to zero
            );
        }

        // Count the number of active installations for each plugin
        $all_plugins = get_plugins();
        foreach ($all_plugins as $plugin_file => $plugin_data) {
            if (array_key_exists($plugin_file, $active_plugins_data)) {
                $active_plugins_data[$plugin_file]['count']++;
            }
        }

        // Sort plugins based on the number of active installations (descending order)
        uasort($active_plugins_data, function ($a, $b) {
            return $b['count'] - $a['count'];
        });

        // Display the top 5 most used plugins
        $counter = 0;
        foreach ($active_plugins_data as $plugin_data) {
            $system_info .= '<tr><td>' . $plugin_data['name'] . '</td><td>' . $plugin_data['version'] . '</td></tr>';
            $counter++;
            if ($counter >= 5) {
                break;
            }
        }
        $system_info .= '</table>';
        $system_info .= '</div>';
        // Webserver Configuration
        $system_info .= '<h2><button id="show-server-info-button" class="info-button">Server<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="server-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>Server</h3>';
        $system_info .= '<table>';
        $system_info .= '<p>The options shown below relate to your server setup. If changes are required, you may need your web host’s assistance.</p>';
        // Add Server information
        $system_info .= '<tr><td>Server Architecture</td><td>' . esc_html(php_uname('s')) . '</td></tr>';
        $system_info .= '<tr><td>Web Server</td><td>' . esc_html($_SERVER['SERVER_SOFTWARE']) . '</td></tr>';
        $system_info .= '<tr><td>PHP Version</td><td>' . esc_html(phpversion()) . '</td></tr>';
        $system_info .= '<tr><td>PHP SAPI</td><td>' . esc_html(php_sapi_name()) . '</td></tr>';
        $system_info .= '<tr><td>PHP Max Input Variables</td><td>' . esc_html(ini_get('max_input_vars')) . '</td></tr>';
        $system_info .= '<tr><td>PHP Time Limit</td><td>' . esc_html(ini_get('max_execution_time')) . ' seconds</td></tr>';
        $system_info .= '<tr><td>PHP Memory Limit</td><td>' . esc_html(ini_get('memory_limit')) . '</td></tr>';
        $system_info .= '<tr><td>Max Input Time</td><td>' . esc_html(ini_get('max_input_time')) . ' seconds</td></tr>';
        $system_info .= '<tr><td>Upload Max Filesize</td><td>' . esc_html(ini_get('upload_max_filesize')) . '</td></tr>';
        $system_info .= '<tr><td>PHP Post Max Size</td><td>' . esc_html(ini_get('post_max_size')) . '</td></tr>';
        $system_info .= '<tr><td>cURL Version</td><td>' . esc_html(curl_version()['version']) . '</td></tr>';
        $system_info .= '<tr><td>Is SUHOSIN Installed?</td><td>' . (extension_loaded('suhosin') ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>Is the Imagick Library Available?</td><td>' . (extension_loaded('imagick') ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>Are Pretty Permalinks Supported?</td><td>' . (get_option('permalink_structure') ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>.htaccess Rules</td><td>' . esc_html(is_writable('.htaccess') ? 'Writable' : 'Non Writable') . '</td></tr>';
        $system_info .= '<tr><td>Current Time</td><td>' . esc_html(current_time('mysql')) . '</td></tr>';
        $system_info .= '<tr><td>Current UTC Time</td><td>' . esc_html(current_time('mysql', true)) . '</td></tr>';
        $system_info .= '<tr><td>Current Server Time</td><td>' . esc_html(date('Y-m-d H:i:s')) . '</td></tr>';
        $system_info .= '</table>';
        $system_info .= '</div>';

        // Database Configuration
        $system_info .= '<h2><button id="show-database-info-button" class="info-button">Database<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="database-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>Database</h3>';
        $system_info .= '<table>';
        $database_extension = 'mysqli';
        $database_server_version = $wpdb->get_var("SELECT VERSION() as version");
        $database_client_version = $wpdb->db_version();
        $database_username = DB_USER;
        $database_host = DB_HOST;
        $database_name = DB_NAME;
        $table_prefix = $wpdb->prefix;
        $database_charset = $wpdb->charset;
        $database_collation = $wpdb->collate;
        $max_allowed_packet_size = $wpdb->get_var("SHOW VARIABLES LIKE 'max_allowed_packet'");
        $max_connections_number = $wpdb->get_var("SHOW VARIABLES LIKE 'max_connections'");

        $system_info .= '<tr><td>Extension</td><td>' . esc_html($database_extension) . '</td></tr>';
        $system_info .= '<tr><td>Server Version</td><td>' . esc_html($database_server_version) . '</td></tr>';
        $system_info .= '<tr><td>Client Version</td><td>' . esc_html($database_client_version) . '</td></tr>';
        $system_info .= '<tr><td>Database Username</td><td>' . esc_html($database_username) . '</td></tr>';
        $system_info .= '<tr><td>Database Host</td><td>' . esc_html($database_host) . '</td></tr>';
        $system_info .= '<tr><td>Database Name</td><td>' . esc_html($database_name) . '</td></tr>';
        $system_info .= '<tr><td>Table Prefix</td><td>' . esc_html($table_prefix) . '</td></tr>';
        $system_info .= '<tr><td>Database Charset</td><td>' . esc_html($database_charset) . '</td></tr>';
        $system_info .= '<tr><td>Database Collation</td><td>' . esc_html($database_collation) . '</td></tr>';
        $system_info .= '<tr><td>Max Allowed Packet Size</td><td>' . esc_html($max_allowed_packet_size) . '</td></tr>';
        $system_info .= '<tr><td>Max Connections Number</td><td>' . esc_html($max_connections_number) . '</td></tr>';
        $system_info .= '</table>';
        $system_info .= '</div>';

        // wordpress constants
        $system_info .= '<h2><button id="show-wrcons-info-button" class="info-button">WordPress Constants<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="wrcons-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>WordPress Constants</h3>';
        $system_info .= '<table>';
        // Add WordPress Constants information
        $system_info .= '<tr><td>ABSPATH</td><td>' . esc_html(ABSPATH) . '</td></tr>';
        $system_info .= '<tr><td>WP_HOME</td><td>' . esc_html(home_url()) . '</td></tr>';
        $system_info .= '<tr><td>WP_SITEURL</td><td>' . esc_html(site_url()) . '</td></tr>';
        $system_info .= '<tr><td>WP_CONTENT_DIR</td><td>' . esc_html(WP_CONTENT_DIR) . '</td></tr>';
        $system_info .= '<tr><td>WP_PLUGIN_DIR</td><td>' . esc_html(WP_PLUGIN_DIR) . '</td></tr>';
        $system_info .= '<tr><td>WP_MEMORY_LIMIT</td><td>' . esc_html(WP_MEMORY_LIMIT) . '</td></tr>';
        $system_info .= '<tr><td>WP_MAX_MEMORY_LIMIT</td><td>' . esc_html(WP_MAX_MEMORY_LIMIT) . '</td></tr>';
        $system_info .= '<tr><td>WP_DEBUG</td><td>' . (defined('WP_DEBUG') && WP_DEBUG ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>WP_DEBUG_DISPLAY</td><td>' . (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>SCRIPT_DEBUG</td><td>' . (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>WP_CACHE</td><td>' . (defined('WP_CACHE') && WP_CACHE ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>CONCATENATE_SCRIPTS</td><td>' . (defined('CONCATENATE_SCRIPTS') && CONCATENATE_SCRIPTS ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>COMPRESS_SCRIPTS</td><td>' . (defined('COMPRESS_SCRIPTS') && COMPRESS_SCRIPTS ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>COMPRESS_CSS</td><td>' . (defined('COMPRESS_CSS') && COMPRESS_CSS ? 'Yes' : 'No') . '</td></tr>';
        // Manually define the environment type (example values: 'development', 'staging', 'production')
        $environment_type = 'development';

        // Display the environment type
        $system_info .= '<tr><td>WP_ENVIRONMENT_TYPE</td><td>' . esc_html($environment_type) . '</td></tr>';

        $system_info .= '<tr><td>WP_DEVELOPMENT_MODE</td><td>' . (defined('WP_DEVELOPMENT_MODE') && WP_DEVELOPMENT_MODE ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>DB_CHARSET</td><td>' . esc_html(DB_CHARSET) . '</td></tr>';
        $system_info .= '<tr><td>DB_COLLATE</td><td>' . esc_html(DB_COLLATE) . '</td></tr>';

        $system_info .= '</table>';
        $system_info .= '</div>';

        // Filesystem Permission
        $system_info .= '<h2><button id="show-ftps-info-button" class="info-button">Filesystem Permission <span class="dashicons dashicons-arrow-down"></span></button></h2>';
        $system_info .= '<div id="ftps-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>Filesystem Permission</h3>';
        $system_info .= '<p>Shows whether WordPress is able to write to the directories it needs access to.</p>';
        $system_info .= '<table>';
        // Filesystem Permission information
        $system_info .= '<tr><td>The main WordPress directory</td><td>' . esc_html(ABSPATH) . '</td><td>' . (is_writable(ABSPATH) ? 'Writable' : 'Not Writable') . '</td></tr>';
        $system_info .= '<tr><td>The wp-content directory</td><td>' . esc_html(WP_CONTENT_DIR) . '</td><td>' . (is_writable(WP_CONTENT_DIR) ? 'Writable' : 'Not Writable') . '</td></tr>';
        $system_info .= '<tr><td>The uploads directory</td><td>' . esc_html(wp_upload_dir()['basedir']) . '</td><td>' . (is_writable(wp_upload_dir()['basedir']) ? 'Writable' : 'Not Writable') . '</td></tr>';
        $system_info .= '<tr><td>The plugins directory</td><td>' . esc_html(WP_PLUGIN_DIR) . '</td><td>' . (is_writable(WP_PLUGIN_DIR) ? 'Writable' : 'Not Writable') . '</td></tr>';
        $system_info .= '<tr><td>The themes directory</td><td>' . esc_html(get_theme_root()) . '</td><td>' . (is_writable(get_theme_root()) ? 'Writable' : 'Not Writable') . '</td></tr>';

        $system_info .= '</table>';
        $system_info .= '</div>';

        return $system_info;
    }


      public function display_error_cf7_log() {

        // Define the path to your debug log file
        $debug_log_file = WP_CONTENT_DIR. '/debug.log';

        // Check if the debug log file exists
        if (file_exists($debug_log_file)) {
            // Read the contents of the debug log file
            $debug_log_contents = file_get_contents($debug_log_file);

            // Split the log content into an array of lines
            $log_lines = explode("\n", $debug_log_contents);

            // Get the last 100 lines in reversed order
            $last_100_lines = array_slice(array_reverse($log_lines), 0, 100);

            // Join the lines back together with line breaks
            $last_100_log = implode("\n", $last_100_lines);

            // Output the last 100 lines in reversed order in a textarea
            ?>
            <textarea class="errorlog" rows="20" cols="80"><?php echo esc_textarea($last_100_log); ?></textarea>
            <?php
        } else {
            echo 'Debug log file not found.';
        }
    }


   /**
    * AJAX function - verifies the token
    *
    * @since 1.0
    */
   public function verify_gs_integation() {
      // nonce checksave_gs_settings
      check_ajax_referer( 'gs-ajax-nonce', 'security' );

      /* sanitize incoming data */
      $Code = sanitize_text_field( $_POST["code"] );

      update_option( 'gs_access_code', $Code );

      if ( get_option( 'gs_access_code' ) != '' ) {
         include_once( GS_CONNECTOR_ROOT . '/lib/google-sheets.php');
         cf7gsc_googlesheet::preauth( get_option( 'gs_access_code' ) );
         // update_option( 'gs_verify', 'valid' );
         wp_send_json_success();
      } else {
         update_option( 'gs_verify', 'invalid' );
         wp_send_json_error();
      }
   }
   
    /**
    * AJAX function - deactivate activation
    * @since 4.2
    */
   public function deactivate_gs_integation() {
      // nonce check
      check_ajax_referer('gs-ajax-nonce', 'security');

      if ( get_option('gs_token') !== '' ) {
         $accesstoken = get_option( 'gs_token' );
         $client = new CF7GSC_googlesheet();
         $client->revokeToken_auto($accesstoken);
         delete_option('gs_token');
         delete_option('gs_access_code');
         delete_option('gs_verify');

         wp_send_json_success();
      } else {
         wp_send_json_error();
      }
   }
   
   /**
    * AJAX function - clear log file
    * @since 2.1
    */
   public function gs_clear_logs() {
      // nonce check
      check_ajax_referer( 'gs-ajax-nonce', 'security' );
      $existDebugFile = get_option('gs_debug_log_file');
      $clear_file_msg ='';
      // check if debug unique log file exist or not then exists to clear file
      if (!empty($existDebugFile) && file_exists($existDebugFile)) {
       
         $handle = fopen ( $existDebugFile, 'w');
        
        fclose( $handle );
        $clear_file_msg ='Logs are cleared.';
       }
       else{
        $clear_file_msg = 'No log file exists to clear logs.';
       }
     
      
      wp_send_json_success($clear_file_msg);
   }
     /**
    * AJAX function - clear log file for system status tab
    * @since 2.1
    */
    public function cf7_clear_debug_logs() {
    // nonce check
    check_ajax_referer('gs-ajax-nonce', 'security');
    $handle = fopen(WP_CONTENT_DIR . '/debug.log', 'w');
    fclose($handle);
    wp_send_json_success();
}
   
   /**
    * Add new tab to contact form 7 editors panel
    * @since 1.0
    */
   public function cf7_gs_editor_panels( $panels ) {
      if ( current_user_can( 'wpcf7_edit_contact_forms' ) ) {
         $panels['google_sheets'] = array(
            'title' => __( 'Google Sheets', 'contact-form-7' ),
            'callback' => array( $this, 'cf7_editor_panel_google_sheet' )
         );
      }
      return $panels;
   }

   /**
    * Set Google sheet settings with contact form
    * @since 1.0
    */
   public function save_gs_settings( $post ) {
      $default = array(
         "sheet-name" => "",
          "sheet-id" => "",
         "sheet-tab-name" => "",
          "tab-id" => ""
          
      );
      $sheet_data = isset( $_POST['cf7-gs'] ) ? $_POST['cf7-gs'] : $default;
      update_post_meta( $post->id(), 'gs_settings', $sheet_data );
     
   }
   
   /**
    * Create array of file name for the uploaded files
    * @since 4.5
    */
   public function save_uploaded_files_local() {
       $form = WPCF7_Submission::get_instance();
	if ( $form ) {
	    $files		 = $form->uploaded_files();
	    $uploads_stored	 = array();

	    foreach ( $files as $field_name => $file_path ) {
		if ( ! isset( $_FILES[ $field_name ] ) ) {
		    continue;
		}
		
		$file_details = $_FILES[ $field_name ];
		$file_name = $file_details['name'];
		$uploads_stored[ $field_name ] = $file_name; 
	    }
	    $this->gs_uploads = $uploads_stored;
	}
   }

   /**
    * Function - To send contact form data to google spreadsheet
    * @param object $form
    * @since 1.0
    */
   public function cf7_save_to_google_sheets( $form ) {
      
      $submission = WPCF7_Submission::get_instance();
      
      // get form data
      $form_id = $form->id();
      $form_data = get_post_meta( $form_id, 'gs_settings' );
      $data = array();
    
      // if contact form sheet name and tab name is not empty than send data to spreedsheet
      if ( $submission && (! empty( $form_data[0]['sheet-name'] ) ) && (! empty( $form_data[0]['sheet-tab-name'] ) ) ) {
         $posted_data = $submission->get_posted_data();
	 
         // make sure the form ID matches the setting otherwise don't do anything
         try {
            include_once( GS_CONNECTOR_ROOT . "/lib/google-sheets.php" );
            $doc = new cf7gsc_googlesheet();
            $doc->auth();
            $doc->setSpreadsheetId( $form_data[0]['sheet-id'] );
            $doc->setWorkTabId( $form_data[0]['tab-id'] );

            // Special Mail Tags  
            $meta = array();

            $special_mail_tags = array( 'serial_number', 'remote_ip', 'user_agent', 'url', 'date', 'time', 'post_id', 'post_name', 'post_title', 'post_url', 'post_author', 'post_author_email', 'site_title', 'site_description', 'site_url', 'site_admin_email', 'user_login', 'user_email', 'user_url', 'user_first_name', 'user_last_name', 'user_nickname', 'user_display_name' );

            foreach ( $special_mail_tags as $smt ) {
                $tagname = sprintf( '_%s', $smt );

		$mail_tag = new WPCF7_MailTag(
			sprintf( '[%s]', $tagname ),
			$tagname,
			''
		);
                
               $meta[$smt] = apply_filters( 'wpcf7_special_mail_tags', '', $tagname, false, $mail_tag );

            }

            if ( ! empty( $meta ) ) {
               $data["date"] = $meta["date"];
               $data["time"] = $meta["time"];
               $data["serial-number"] = $meta["serial_number"];
               $data["remote-ip"] = $meta["remote_ip"];
               $data["user-agent"] = $meta["user_agent"];
               $data["url"] = $meta["url"];
               $data["post-id"] = $meta["post_id"];
               $data["post-name"] = $meta["post_name"];
               $data["post-title"] = $meta["post_title"];
               $data["post-url"] = $meta["post_url"];
               $data["post-author"] = $meta["post_author"];
               $data["post-author-email"] = $meta["post_author_email"];
               $data["site-title"] = $meta["site_title"];
               $data["site-description"] = $meta["site_description"];
               $data["site-url"] = $meta["site_url"];
               $data["site-admin-email"] = $meta["site_admin_email"];
               $data["user-login"] = $meta["user_login"];
               $data["user-email"] = $meta["user_email"];
               $data["user-url"] = $meta["user_url"];
               $data["user-first-name"] = $meta["user_first_name"];
               $data["user-last-name"] = $meta["user_last_name"];
               $data["user-nickname"] = $meta["user_nickname"];
               $data["user-display-name"] = $meta["user_display_name"];
            }

            foreach ( $posted_data as $key => $value ) {
		// exclude the default wpcf7 fields in object
		if ( strpos( $key, '_wpcf7' ) !== false || strpos( $key, '_wpnonce' ) !== false ) {
		    // do nothing
		} else {
		    // Get file name array
		    $uploaded_file = $this->gs_uploads;
		    if ( array_key_exists( $key, $uploaded_file ) || isset( $uploaded_file[ $key ] ) ) {
			$data[ $key ] = sanitize_file_name( $uploaded_file[ $key ] );
			continue;
		    }

		    // handle strings and array elements
		    if ( is_array( $value ) ) {
			$data[ $key ] = sanitize_text_field( implode( ', ', $value ) );
		    } else {
          //$data[$key] = sanitize_text_field(stripcslashes($value));//Old Code
          $data[$key] = sanitize_textarea_field(stripcslashes($value));//Line Break in textarea issue resolved. 
		    }
		}
	    }
            // Filter Form Submitted data such as for repetable fields plugin
            $data = apply_filters( 'gsc_filter_form_data', $data, $form );
            if( ! empty( $data[0] ) && is_array( $data[0] ) ) {
              $doc->add_multiple_row( $data );
            } else {
              $doc->add_row( $data );
            }
            
         } catch ( Exception $e ) {
            $data['ERROR_MSG'] = $e->getMessage();
            $data['TRACE_STK'] = $e->getTraceAsString();
            Gs_Connector_Utility::gs_debug_log( $data );
         }
      }
   }

   /*
    * Google sheet settings page  
    * @since 1.0
    */

   public function cf7_editor_panel_google_sheet( $post ) {
      $form_id = sanitize_text_field( $_GET['post'] );
      $form_data = get_post_meta( $form_id, 'gs_settings' );
      // Check if the user is authenticated
       $authenticated = get_option('gs_token');
      
       $per = get_option('gs_verify');
       $per_msg = __( 'invalid-auth', 'gsconnector' );
       // check user is authenticated when save existing api method
      $show_setting = 0;
          
     if ((!empty($authenticated) && $per == "valid") ) {
        $show_setting = 1;
    }
    else{
     ?>
     <p class="gs-display-note">
            <?php 
            echo __( '<strong>Authentication Required:</strong>
                  You must have to <a href="admin.php?page=wpcf7-google-sheet-config&tab=integration" target="_blank">Authenticate using your Google Account</a> along with Google Drive and Google Sheets Permissions in order to enable the settings for configuration.', 'gsconnector' );
            ?>
           
        </p>
     <?php 
   }
   if($show_setting == 1){
      ?>
      <form method="post">
         <div class="gs-fields">
            <h2><span><?php echo esc_html( __( 'Google Sheet Settings', 'gsconnector' ) ); ?></span><span class="gs-info"><?php echo __( '( Fetch your sheets automatically using PRO <a href="https://www.gsheetconnector.com/cf7-google-sheet-connector-pro?gsheetconnector-ref=17" target="__blank">Upgrade to PRO</a> )', 'gsconnector'); ?> </span></h2>
             <p>
               <label><?php echo esc_html( __( 'Google Sheet Name', 'gsconnector' ) ); ?></label>
               <input type="text" name="cf7-gs[sheet-name]" id="gs-sheet-name" 
                      value="<?php echo ( isset( $form_data[0]['sheet-name'] ) ) ? esc_attr( $form_data[0]['sheet-name'] ) : ''; ?>"/>


               <a href="" class=" gs-name help-link"><img src="<?php echo GS_CONNECTOR_URL; ?>assets/img/help.png" class="help-icon"><?php //echo esc_html( __( 'Where do i get Sheet Name?', 'gsconnector' ) ); ?><span class='hover-data'><?php echo esc_html( __( 'Go to your google account and click on"Google apps" icon and than click "Sheets". Select the name of the appropriate sheet you want to link your contact form or create new sheet.', 'gsconnector' ) ); ?> </span></a>
            </p>
			<p>
                  <label><?php echo esc_html(__('Google Sheet ID', 'gsconnector')); ?></label>
                  <input type="text" name="cf7-gs[sheet-id]" id="gs-sheet-id"
                         value="<?php echo ( isset($form_data[0]['sheet-id']) ) ? esc_attr($form_data[0]['sheet-id']) : ''; ?>"/>
                  <a href="" class=" gs-name help-link"><img src="<?php echo GS_CONNECTOR_URL; ?>assets/img/help.png" class="help-icon"><?php //echo esc_html(__('Google Sheet Id?', 'gsconnector')); ?><span class='hover-data'><?php echo esc_html(__('you can get sheet id from your sheet URL', 'gsconnector')); ?></span></a>
               </p>
            <p>
               <label><?php echo esc_html( __( 'Google Sheet Tab Name', 'gsconnector' ) ); ?></label>
               <input type="text" name="cf7-gs[sheet-tab-name]" id="gs-sheet-tab-name"
                      value="<?php echo ( isset( $form_data[0]['sheet-tab-name'] ) ) ? esc_attr( $form_data[0]['sheet-tab-name'] ) : ''; ?>"/>
               <a href="" class=" gs-name help-link"><img src="<?php echo GS_CONNECTOR_URL; ?>assets/img/help.png" class="help-icon"><?php //echo esc_html( __( 'Where do i get Tab Name?', 'gsconnector' ) ); ?><span class='hover-data'><?php echo esc_html( __( 'Open your Google Sheet with which you want to link your contact form . You will notice a tab names at bottom of the screen. Copy the tab name where you want to have an entry of contact form.', 'gsconnector' ) ); ?></span></a>
            </p>
		     <p>
                  <label><?php echo esc_html(__('Google Tab ID', 'gsconnector')); ?></label>
                  <input type="text" name="cf7-gs[tab-id]" id="gs-tab-id"
                         value="<?php echo ( isset($form_data[0]['tab-id']) ) ? esc_attr($form_data[0]['tab-id']) : ''; ?>"/>
                  <a href="" class=" gs-name help-link"><img src="<?php echo GS_CONNECTOR_URL; ?>assets/img/help.png" class="help-icon"><?php //echo esc_html(__('Google Tab Id?', 'gsconnector')); ?><span class='hover-data'><?php echo esc_html(__('you can get tab id from your sheet URL', 'gsconnector')); ?></span></a>
               </p>
			   
			   <?php if((isset( $form_data[0]['sheet-name'] )) && (isset($form_data[0]['sheet-id'])) &&  (isset( $form_data[0]['sheet-tab-name'])) && (isset($form_data[0]['tab-id']))) {
				$link = "https://docs.google.com/spreadsheets/d/".$form_data[0]['sheet-id']."/edit#gid=".$form_data[0]['tab-id']; 
				   ?>
			  <p>
				<a href="<?php echo $link; ?>" target="_blank" class="cf7_gs_link" >Google Sheet Link</a>
			  </p>
			  <?php } ?>
         </div> 
		  
      </form>
	  
	  <?php
		include( GS_CONNECTOR_PATH . "includes/pages/gs-field-list.php" );
		
		include( GS_CONNECTOR_PATH . "includes/pages/gs-special-mailtags.php" );
      
    include( GS_CONNECTOR_PATH . "includes/pages/gs-custom-mail-tags.php" );

    include( GS_CONNECTOR_PATH . "includes/pages/gs-custom-ordering.php");
	  
		include( GS_CONNECTOR_PATH . "includes/pages/gs-demo-details.php" );
   }
}
   
   /**
    * Function - fetch contact form list that is connected with google sheet
    * @since 2.1
    */
   public function get_forms_connected_to_sheet() {
      global $wpdb;
      
      $query = $wpdb->get_results("SELECT ID, post_title, meta_value from ".$wpdb->prefix."posts as p JOIN ".$wpdb->prefix."postmeta as pm on p.ID = pm.post_id where pm.meta_key='gs_settings' AND p.post_type='wpcf7_contact_form' ORDER BY p.ID");
      return $query;
   }
   
   /**
    * Function - display contact form fields to be mapped to google sheet
    * @param int $form_id
    * @since 1.0
    */
   public function display_form_fields( $form_id ) { ?>
      
      <?php
      // fetch saved fields
      $saved_mail_tags = get_post_meta( $form_id, 'gs_map_mail_tags' );
      
      // fetch mail tags
      $assoc_arr = [];
      $meta = get_post_meta( $form_id, '_form', true );
      $fields = $this->get_contact_form_fields( $meta );
      if( $fields ) {
         foreach ( $fields as $field ) {
            $single = $this->get_field_assoc( $field );
            if ( $single ) {
               $assoc_arr[] = $single;
            }
         }
      }
      
      if( ! empty( $assoc_arr ) ) {
      ?>
      <table class="gs-field-list">
      <?php
      $count = 0;
      foreach ( $assoc_arr as $key => $value ) {
         foreach ( $value as $k => $v ) {
            $saved_val = "";
            $checked = "";
            if( ! empty( $saved_mail_tags ) && array_key_exists( $v, $saved_mail_tags[0] ) ) :
               $saved_val = $saved_mail_tags[0][$v];
               $checked = "checked";
            endif;
            
            $placeholder = preg_replace('/[\\_]|\\s+/', '-', $v );
            ?>
               <tr>
                  <td>
                     <!-- <input type="checkbox" checked="checked" disabled="disabled" class="check-toggle-cf7" name="gs-custom-ck[<?php echo $count; ?>]" value="1" <?php echo $checked; ?> > -->
                       <label for="enable-sorting-option" class="button-woo-toggle-cf7" id="sorting-toggle"></label>
                  </td>
                  <td><?php echo $v; ?> : </td>
                  <td>
                     <input type="text" name="gs-custom-header[<?php echo $count; ?>]" value="<?php echo $saved_val; ?>" placeholder="<?php echo $placeholder; ?>" disabled>
                  </td>
               </tr>
         <?php 
         $count++;
         }
      }
      ?>
      </table>
      <?php
      } else {
         echo '<p><span class="gs-info">' . __( 'No mail tags available.','gsconnector' ) . '</span></p>';
      }
   }
   
   public function get_contact_form_fields( $meta ) {
      $regexp = '/\[.*\]/';
      $arr = [];
      if ( preg_match_all($regexp, $meta, $arr) == false) {
          return false;
      }
      return $arr[0];
   }
   
   public function get_field_assoc($content) {
      $regexp_type = '/(?<=\[)[^\s\*]*/';
      $regexp_name = '/(?<=\s)[^\s\]]*/';
      $arr_type = [];
      $arr_name = [];
      if (preg_match($regexp_type, $content, $arr_type) == false) {
          return false;
      }
      if (!in_array($arr_type[0], $this->allowed_tags)) {
          return false;
      }
      if (preg_match($regexp_name, $content, $arr_name) == false) {
          return false;
      }
      return array($arr_type[0] => $arr_name[0]);
   }
   
   /**
    * Function - display contact form special mail tags to be mapped to google sheet
    * @since 2.6
    */
   public function display_form_special_tags( $form_id ) {
      
     
      
      $tags_count = count( $this->special_mail_tags );
      $num_of_cols = 1;
      ?>
      <h2><span class="gs-info"><?php echo esc_html( __( 'Map special mail tags with custom header name and save automatically to google sheet. ', 'gsconnector' ) ); ?></span></h2>
      <table class="gs-field-list special">
         <?php 
            
            for ( $i = 0; $i <= $tags_count; $i++ ) {
               if ( $i == $tags_count ) {
                  break;
               }  
               $tag_name = $this->special_mail_tags[ $i ];
                           
               $placeholder = str_replace( '_', '-', $tag_name );
            echo '<tr>';
               echo '<td>
               <!--<input type="checkbox" disabled="disabled" name="gs-st-ck['. $i . ']" value="1">-->
               <label for="enable-sorting-option" class="button-woo-toggle-cf7" id="sorting-toggle"></label>
                            </td>';
               echo '<td class="special-tags">[_' . $tag_name . '] </td>';
               echo '<td class="gs-r-pad"><input type="text" class="name-field" name="gs-st-custom-header['. $i . ']" value="" disabled placeholder="'. $placeholder .'"> </td>';
               if ( $i % $num_of_cols == 1 ) {
                     echo '</tr><tr>';
                  }
               }
         ?>
      </table>
      <?php
   }
   
   function display_form_custom_tag( $form_id ){
		$custom_mail_tags = array();
		$num_of_cols = 2;
	   
      if ( has_filter( "gscf7_special_mail_tags" ) ) {
         // Filter hook for custom mail tags
         $custom_tags = apply_filters( "gscf7_special_mail_tags", $custom_mail_tags, $form_id );
         $custom_tags_count = count( $custom_tags );
         $num_of_cols = 2;
         // fetch saved fields
         $saved_cmail_tags = get_post_meta( $form_id, 'gs_map_custom_mail_tags' );
      ?>
         <table class="gs-field-list">
            <?php 
               echo '<tr>';
               for ( $i = 0; $i <= $custom_tags_count; $i++ ) {
                  if ( $i == $custom_tags_count ) {
                     break;
                  } 
                  $tag_name = $custom_tags[ $i ];
                  $modify_tag = ltrim( $tag_name, '_' );
                  $saved_val = "";
                  $checked = "";
                  if( ! empty( $saved_cmail_tags ) && array_key_exists( $modify_tag, $saved_cmail_tags[0] ) ) :
                     $saved_val = $saved_cmail_tags[0][$modify_tag];
                     $checked = "checked";
                  endif;
                  
                  //hack - todo
                  $placeholder_explode = explode( '_', $tag_name, 2 );
                  $placeholder = str_replace( '_', '-', $placeholder_explode[1] );
               
                  echo '<td><input type="checkbox" name="gs-ct-ck['. $i . ']" value="1" ' . $checked . '></td>';
                  echo '<td>[' . $tag_name . ']</td>';
                  echo '<td class="gs-r-pad"><input type="hidden" name="gs-ct-key['. $i . ']" value="' . $tag_name . '" ><input type="hidden" name="gs-ct-placeholder['. $i . ']" value="' . $placeholder . '" ><input type="text" name="gs-ct-custom-header['. $i . ']" value="' . $saved_val . '" placeholder="'. $placeholder .'"></td>';
                  if ( $i % $num_of_cols == 1 ) {
                        echo '</tr><tr>';
                     }
                  }
            ?>
         </table>
      <?php 
      } else {
         echo '<p><span class="gs-info">' . __( 'No custom mail tags available.','gsconnector' ) . '</span></p>';
      } 	   
   }
   
   public function display_upgrade_notice() {
      $get_notification_display_interval = get_option( 'gs_upgrade_notice_interval' );
      $close_notification_interval = get_option( 'gs_close_upgrade_notice' );
      
      if( $close_notification_interval === "off" ) {
         return;
      }
      
      if ( ! empty( $get_notification_display_interval ) ) {
         $adds_interval_date_object = DateTime::createFromFormat( "Y-m-d", $get_notification_display_interval );
         $notice_interval_timestamp = $adds_interval_date_object->getTimestamp();
      }
      
      if ( empty( $get_notification_display_interval ) || current_time( 'timestamp' ) > $notice_interval_timestamp ) {
         $ajax_nonce   = wp_create_nonce( "gs_upgrade_ajax_nonce" );
         $upgrade_text = '<div class="gs-adds-notice">';
         $upgrade_text .= '<span><b>CF7 Google Sheet Connector </b> ';
         $upgrade_text .= 'version 4.0 would required you to <a href="'.  admin_url("admin.php?page=wpcf7-google-sheet-config") . '">reauthenticate</a> with your Google Account again due to update of Google API V3 to V4.<br/><br/>';
         $upgrade_text .= 'To avoid any loss of data redo the Google Sheet settings of each Contact Forms again with required sheet and tab details.</span>';
         $upgrade_text .= '<ul class="review-rating-list">';
         $upgrade_text .= '<li><a href="javascript:void(0);" class="cf7gsc_upgrade" title="Done">Yes, I have done.</a></li>';
         $upgrade_text .= '<li><a href="javascript:void(0);" class="cf7gsc_upgrade_later" title="Remind me later">Remind me later.</a></li>';      
         $upgrade_text .= '</ul>';
         $upgrade_text .= '<input type="hidden" name="gs_upgrade_ajax_nonce" id="gs_upgrade_ajax_nonce" value="' . $ajax_nonce . '" />';
         $upgrade_text .= '</div>';

         $upgrade_block = Gs_Connector_Utility::instance()->admin_notice( array(
            'type'    => 'upgrade',
            'message' => $upgrade_text
         ) );
         echo $upgrade_block;
      }
   }
   
   public function set_upgrade_notification_interval() {
      // check nonce
      check_ajax_referer( 'gs_upgrade_ajax_nonce', 'security' );
      $time_interval = date( 'Y-m-d', strtotime( '+10 day' ) );
      update_option( 'gs_upgrade_notice_interval', $time_interval );
      wp_send_json_success();
   }
   
   public function close_upgrade_notification_interval() {
      // check nonce
      check_ajax_referer( 'gs_upgrade_ajax_nonce', 'security' );
      update_option( 'gs_close_upgrade_notice', 'off' );
      wp_send_json_success();
   }

}

$gs_connector_service = new Gs_Connector_Service();


