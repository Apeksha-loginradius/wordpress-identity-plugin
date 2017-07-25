<?php

// Exit if called directly
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('CIAM_Front_Sso')) {

    class CIAM_Front_Sso {

        function __construct() {
            global $ciam_sso_page_settings,$ciam_credencials;
            
            if(!isset($ciam_credencials['apikey']) && empty($ciam_credencials['apikey']) || !isset($ciam_credencials['secret']) && empty($ciam_credencials['secret'])){
                 return;   
             }
            $ciam_sso_page_settings = get_option('Ciam_Sso_Page_settings');
            if (isset($ciam_sso_page_settings['sso_enable']) && $ciam_sso_page_settings['sso_enable'] == '1') {
                add_action('wp_head', array($this, 'load_sso_variables'));
                add_action('in_admin_footer', array($this, 'load_sso_variables'));
                add_action('ciam_sso_logout', array($this, 'ciam_sso_force_logout'));
                add_action('wp_head', array($this, 'ciam_sso_commonoptions'));
            }
            
            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class($this), "");
        }

        
       
            
            
            
        
        
          public function ciam_sso_commonoptions() { 
              global $ciam_credencials;
              if(isset($ciam_credencials) && !empty($ciam_credencials['apikey'])){ // checking for the api key and site name is not blank.
              ?>
             <script type="text/javascript">
             var commonOptions = {};
             commonOptions.apiKey = '<?php echo $ciam_credencials['apikey']; ?>';
             commonOptions.appName = '<?php echo $ciam_credencials['sitename']; ?>';
             var LRObject = new LoginRadiusV2(commonOptions);
             </script>
              <?php 
              }
          }
        
        
        public function ciam_sso_force_logout() {  
            add_action('wp_head', array($this, 'ciam_sso_force_logout_head'));
            
             /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class($this), "");
        }
        
        
         public function ciam_sso_force_logout_head() { 
              global $ciam_api_settings;
            $ciam_api_settings = get_option('Ciam_API_settings');
             ?>
             <script>
                jQuery(document).ready(function () {
                   
                    var logout_options = {};
                    logout_options.onSuccess = function () {
                        // On Success
                        //Write your custom code here
                        window.location.href = '<?php echo (site_url('/')) ?>';
                    };
                    LRObject.init("logout", logout_options);
                })
            </script>
            <?php
            
             /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class($this), "");
        }

        public static function load_sso_variables() {
            global $ciam_api_settings,$ciam_setting;
            $ciam_api_settings = get_option('Ciam_API_settings');
            ?>
            <script>
                jQuery(document).ready(function () {
                  
            <?php if (!is_user_logged_in()) { 
                $server = (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']=='on') ? 'https' : 'http';
                $actual_link = "$server://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                
                $login_url = get_permalink($ciam_setting['login_page_id']);
                $registration_url = get_permalink($ciam_setting['registration_page_id']); 
              
                 if(($actual_link === $registration_url) || ($actual_link === $login_url)){
                ?>
                        // If found activated session, goto the callback function
                        var ssologin_options = {};

                        ssologin_options.onSuccess = function (token) {
                            localStorage.setItem('LRTokenKey', token);// to set token in browser local storage.
                            var form = document.createElement('form');
                            form.action = window.location.href;
                            form.method = 'POST';
                            var hiddenToken = document.createElement('input');
                            hiddenToken.type = 'hidden';
                            hiddenToken.value = token;
                            hiddenToken.name = 'token';
                            form.appendChild(hiddenToken);
                            document.body.appendChild(form);
                            form.submit();
                        };
                        LRObject.util.ready(function () {
                            LRObject.init("ssoLogin", ssologin_options);
                        });
                 <?php } } else { ?>
                        var check_options = {};
                        check_options.onError = function (response) {
                            
                            if(response != ''){
                            if("<?php echo get_user_meta(get_current_user_id(), 'accesstoken',true);?>" != response){
                                // On Error
                            // If user is not log in then this function will execute.
                            window.location.href = "<?php echo html_entity_decode(wp_logout_url(''));?>";
                            }
                        }else{
                        logout("<?php echo html_entity_decode(wp_logout_url(''));?>");
                        }
                            
                            
                        };
                        check_options.onSuccess = function (response) {
                          
                            // On Success
                            // If user is log in then this function will execute.
                        };
                        LRObject.util.ready(function () {
                            
                            LRObject.init("ssoNotLoginThenLogout", check_options);
                        });
                        var href = jQuery('#wp-admin-bar-logout a').attr('href');
                        jQuery('#wp-admin-bar-logout a').css({"cursor": "pointer"});
                        jQuery('#wp-admin-bar-logout a').removeAttr('href');
                        jQuery('#wp-admin-bar-logout').click(function (e) {
                            e.preventDefault();
                            logout(href);
                        });

                        if (jQuery('a[href*="logout"]').length > 0) {
                            href = jQuery('a[href*="logout"]').attr('href');
                            jQuery('a[href*="logout"]').attr('data-action', 'ciam-sso-logout');
                            jQuery('a[href*="logout"]').css({"cursor": "pointer"});
                            jQuery('a[href*="logout"]').removeAttr('href');
                            jQuery('a[data-action="ciam-sso-logout"]').click(function () {
                                logout(href);
                            });
                        }
                        function logout(href) {
                            var logout_options = {};
                            logout_options.onSuccess = function () {
                                window.location.href = href;
                                // On Success
                                //Write your custom code here
                            };
                            LRObject.init("logout", logout_options);
                        }
            <?php } ?>
                });
            </script>
            <?php
            
            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_called_class(), "");
        }

    }

    new CIAM_Front_Sso();
}