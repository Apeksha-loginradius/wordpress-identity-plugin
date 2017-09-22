<?php

// Exit if called directly
if (!defined('ABSPATH')) {
    exit();
}

/**
 * The main class and initialization point of the plugin admin.
 */
if (!class_exists('CIAM_Activation_Admin')) {

    class CIAM_Activation_Admin {
        /*
         * Constructor for class CIAM_Social_Login_Admin
         */

        public function __construct() {

            add_action('init', array($this, 'init'), 101);
           
        }

        /*
         * Initialise when constructor get called....
         */

        public function init() {

            
            $this->register_hook_callbacks();
            
            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), '');
        }

        /*
         * Register admin hook callbacks
         */

        public function register_hook_callbacks() {
            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_enqueue_scripts', array($this, 'load_scripts'), 5);
            if(is_super_admin()){ // redirect super admin to secure dashboard on user section 
            add_action('admin_head',array($this,'lr_dashboard_redirect'));
            }
            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), '');
        }

        /**
         * Callback for admin_menu hook,
         * Register CIAM_settings and its sanitization callback. Add Login Radius meta box to pages and posts.
         */
        public function admin_init() {
            register_setting('Ciam_API_settings', 'Ciam_API_settings', array($this, 'ciam_activation_validation'));
            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), '');
        }

        /*
         * It will redirect user to the login radius dashboard on clicking the user section
         */
        
        public function lr_dashboard_redirect(){  ?>
           
           <script type="text/javascript">
              jQuery(document).ready(function(){
                  jQuery('#menu-users a').attr('href','http://secure.loginradius.com/');
                  jQuery('#menu-users ul li:nth-child(4) > a').attr('href','profile.php');
              });
           </script>
              
       <?php }
        
        
        /**
         * Get response from LoginRadius api
         */
        public function api_validation_response($apiKey, $apiSecret) { 
            global $currentErrorCode, $currentErrorResponse;

            $options['method'] = 'get';
            try{
            $wpclient = new \LoginRadiusSDK\Clients\WPHttpClient($apiKey, $apiSecret);
            
            try {
                $query_array = array('apikey' => $apiKey, 'apisecret' => $apiSecret);

                $response = json_decode($wpclient->request("https://api.loginradius.com/api/v2/app/validate", $query_array, $options));

                if (isset($response->Status) && $response->Status) {
                    /* action for debug mode */
                    do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), '');
                    return true;
                } else {

                    $currentErrorCode = '0';
                    $currentErrorResponse = "Details Entered are wrong!";
                    /* action for debug mode */
                    do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), '');
                    return false;
                }
            } catch (\LoginRadiusSDK\LoginRadiusException $e) { 

                $currentErrorCode = '0';
                $currentErrorResponse = "Something went wrong: " . $e->getErrorResponse()->description;

                /* action for debug mode */
                do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), '');
                return false;
            }
            }catch(\LoginRadiusSDK\LoginRadiusException $e){
                $currentErrorCode = '0';
                $currentErrorResponse = "Please recheck your LoginRadius details";

                /* action for debug mode */
                do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), '');
                return false;
            }
        }
        

        /*
         * This function will validate the activation settings.
         */

        function ciam_activation_validation($settings) {

            $settings['sitename'] = sanitize_text_field($settings['sitename']);
            $settings['apikey'] = sanitize_text_field($settings['apikey']);
            $settings['secret'] = sanitize_text_field($settings['secret']);
            if (empty($settings['sitename'])) {
                $message = 'LoginRadius Site Name is blank. Get your LoginRadius Site Name from <a href="http://www.loginradius.com" target="_blank">LoginRadius</a>';
                add_settings_error('Ciam_API_settings', esc_attr('settings_updated'), $message, 'error');
            }

            if (empty($settings['apikey']) && empty($settings['secret'])) {
                $message = 'LoginRadius API Key and API Secret are blank. Get your LoginRadius API Key and API Secret from <a href="http://www.loginradius.com" target="_blank">LoginRadius</a>';
                add_settings_error('Ciam_API_settings', esc_attr('settings_updated'), $message, 'error');

                /* action for debug mode */
                do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), '');
                return $settings;
            }

            if (empty($settings['apikey'])) {
                $message = 'LoginRadius API Key is blank. Get your LoginRadius API Key from <a href="http://www.loginradius.com" target="_blank">LoginRadius</a>';
                add_settings_error('Ciam_API_settings', esc_attr('settings_updated'), $message, 'error');

                /* action for debug mode */
                do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), '');
                return $settings;
            }

            if (empty($settings['secret'])) {
                $message = 'LoginRadius API Secret is blank. Get your LoginRadius API Secret from <a href="http://www.loginradius.com" target="_blank">LoginRadius</a>';
                add_settings_error('Ciam_API_settings', esc_attr('settings_updated'), $message, 'error');
                /* action for debug mode */
                do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), '');
                return $settings;
            }

            if (isset($settings['apikey']) && isset($settings['secret'])) {

                $encodeString = 'settings';

                if ($this->api_validation_response($settings['apikey'], $settings['secret'], $encodeString)) {

                    /* action for debug mode */
                    do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), '');

                    return $settings;
                } else {

                    // Api or Secret is not valid or something wrong happened while getting response from LoginRadius api
                    $message = 'Please recheck your LoginRadius details';
                    global $currentErrorCode, $currentErrorResponse;

                    $errorMessage = array(
                        "API_KEY_NOT_VALID" => 'LoginRadius API key is invalid. Get your LoginRadius API Key from <a href="http://www.loginradius.com" target="_blank">LoginRadius</a>',
                        'API_SECRET_NOT_VALID' => 'LoginRadius API Secret is invalid. Get your LoginRadius API Secret from <a href="http://www.loginradius.com" target="_blank">LoginRadius</a>',
                        'API_KEY_NOT_FORMATED' => 'LoginRadius API Key is not formatted correctly.',
                        'API_SECRET_NOT_FORMATED' => 'LoginRadius API Secret is not formatted correctly.',
                    );

                    if ($currentErrorCode[0] == '0') {
                        $message = $currentErrorResponse;
                    } else {
                        if (count($currentErrorCode) > 1) {

                            add_settings_error('LR_Ciam_API_settings', esc_attr('settings_updated'), $errorMessage[$currentErrorCode[0]], 'error');
                            add_settings_error('LR_Ciam_API_settings', esc_attr('settings_updated'), $errorMessage[$currentErrorCode[1]], 'error');
                        } else {
                            $message = $errorMessage[$currentErrorCode[0]];
                        }
                    }

                    add_settings_error('LR_Ciam_API_settings', esc_attr('settings_updated'), $message, 'error');
                }
            } else {

                add_settings_error('LR_Ciam_API_settings', esc_attr('settings_updated'), 'Settings Updated', 'updated');
                /* action for debug mode */
                do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), '');

                return $settings;
            }

            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), '');
        }

        /*
         * Adding Javascript/Jquery for admin settings page
         */

        public function load_scripts() {
            global $ciam_js_in_footer, $ciam_setting;

            wp_enqueue_script('ciam_activation_options', CIAM_PLUGIN_URL . 'activation/assets/js/script.js', array('jquery'), CIAM_PLUGIN_VERSION, $ciam_js_in_footer);

            // switching the minified version of js and css file 
            if (!isset($ciam_setting['disable_minified_version'])) {

                wp_register_style('ciam-admin-style', CIAM_PLUGIN_URL . 'activation/assets/css/style.min.css', array(), CIAM_PLUGIN_VERSION);
            } else {
                wp_register_style('ciam-admin-style', CIAM_PLUGIN_URL . 'activation/assets/css/style.css', array(), CIAM_PLUGIN_VERSION);
            }

            wp_enqueue_style('ciam-admin-style');
            
            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), '');
        }

        /*
         * Callback for add_menu_page,
         * This is the first function which is called while plugin admin page is requested
         */

        public static function options_page() {
            include_once CIAM_PLUGIN_DIR . "activation/admin/views/settings.php";
            $obj_CIAM_Activation_Settings = new CIAM_Activation_Settings;
            $obj_CIAM_Activation_Settings->render_options_page();

            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_called_class(), '');
        }

    }

    new CIAM_Activation_Admin();
}
