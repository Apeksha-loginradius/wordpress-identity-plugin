<?php
/**
 * The activation settings class.
 */
// Exit if called directly
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('CIAM_Sso_Settings')) {

    class CIAM_Sso_Settings {

        public function __construct() {
            global $ciam_credencials;
            if(!isset($ciam_credencials['apikey']) || empty($ciam_credencials['apikey']) || !isset($ciam_credencials['secret']) || empty($ciam_credencials['secret'])){ 
                 return;   
             }
        }
        
        public static function render_options_page() {
            global $ciam_sso_page_settings;
            $ciam_sso_page_settings = get_option('Ciam_Sso_Page_settings');
            ?>

            <div class="wrap active-wrap cf">
               <header>
                    <h2 class="logo"><a href="//www.loginradius.com" target="_blank">Single Sign On</a></h2>

                </header>

                <div class="cf">             
                       

                        <form action="options.php" method="post">
                            <?php
                            settings_fields('Ciam_Sso_Page_settings');
                            settings_errors();
                            ?>
                            
                            
                                <div class="ciam_options_container">
                                    <div class="active-row">
                                       
                                            <h3><?php _e('Enable SSO', 'CIAM'); ?></h3>
                                            <label class="active-toggle">
                                                <input type="checkbox" class="active-toggle" name="Ciam_Sso_Page_settings[sso_enable]" value="1" <?php echo ( isset($ciam_sso_page_settings['sso_enable']) && $ciam_sso_page_settings['sso_enable'] == '1' ) ? 'checked' : ''; ?> />
                                                <span class="active-toggle-name">
                                                    Do you want to enable sso ?

                                                </span>
                                            </label>
                                       
                                    </div>


                                </div>
                                <p class="submit">
                                    <?php submit_button('Save Settings', 'primary', 'submit', false); ?>
                                </p>
                           
                        </form>
                    
                </div>        
            </div>
            <?php
            
            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_called_class(), "");
        }

    }

}

