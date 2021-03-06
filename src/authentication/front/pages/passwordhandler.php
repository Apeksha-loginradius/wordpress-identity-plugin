<?php
// Exit if called directly
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('CIAM_Authentication_Passwordhandler')) {

    class CIAM_Authentication_Passwordhandler {
        /*
         * class constructor function
         */

        public function __construct() {
            add_action('init', array($this, 'init'));
        }

        /*
         * load required dependencies
         */

        public function init() {
            add_shortcode('ciam_forgot_form', array($this, 'ciam_forgot_form'));
            add_action('wp_head', array($this, 'ciam_hook_changepassword'));
            add_action('admin_head', array($this, 'ciam_hook_passwordform'));
            add_shortcode('ciam_password_form', array($this, 'ciam_password_form'));
            add_filter('lostpassword_url', array($this, 'custom_forgot_page'), 100);
        }

        /*
         * Forgot password form
         */

        public function ciam_forgot_form() {
            global $ciam_setting;
            if (!empty($ciam_setting['lost_password_page_id'])) {
                $redirect_url = get_permalink($ciam_setting['login_page_id']);
                if (!is_user_logged_in()) {
                    ?>
                    <script>
                        jQuery(document).ready(function () {
                        forgotpass_hook('<?php echo $redirect_url ?>');
                        });</script>
                    <?php
                    $message = '<div  class="messageinfo"></div>';
                    ob_start();
                    $html = '<div class="ciam-user-reg-container">' . $message . '<span id="forgotpasswordmessage"></span><div id="forgotpassword-container" class="forgotpassword-container ciam-input-style"></div><div id="ciam_loading_gif" class="overlay" style="display:none;"><div class="ciam-loading-img"><img class="loading_circle ciam_loading_gif_align ciam_forgot"  src="' . CIAM_PLUGIN_URL . 'authentication/assets/images/loading_icon.gif' . '" alt="loding image" /></div></div><span class="ciam-link"><a href = "' . wp_login_url() . '">Login</a></span><span class="ciam-link btn"><a href = "' . wp_registration_url() . '">Register</a></span></div>';
                    do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), $html);
                    return $html . ob_get_clean();
                }
            }
            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), "");
        }

        /*
         * Hook for change password section.
         */

        public function ciam_hook_changepassword() {
            global $ciam_setting;
            if (isset($ciam_setting) && !empty($ciam_setting['login_page_id'])) {
                $redirect_url = get_permalink($ciam_setting['login_page_id']);
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function () {
                    changepassword('<?php echo $redirect_url ?>');
                    });</script>

                <?php
            }
            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), "");
        }

        /*
         * Reset password form
         */

        public function ciam_password_form() {
            $user_id = get_current_user_id();
            if (!is_user_logged_in()) {
                $db_message = get_user_meta($user_id, 'ciam_message_text', true);

                if (!empty($db_message)) {
                    delete_user_meta($user_id, 'ciam_message_text');
                }

                $message = '<div id="resetpassword" class="messageinfo">' . $db_message . '</div>';
                ob_start();
                add_action('admin_init', array($this, 'change_password_handler'));
                if (isset($_GET['vtype']) && !empty($_GET['vtype'])) { // condition to check if vtype and vtoken is present or not....
                    $html = '<div class="ciam-user-reg-container">' . $message . '<div id="resetpassword-container" class="ciam-input-style"></div><div id="ciam_loading_gif" class="overlay" style="display:none;"><div class="ciam-loading-img"><img class="loading_circle ciam_loading_gif_align ciam_forgot" src="' . CIAM_PLUGIN_URL . 'authentication/assets/images/loading_icon.gif' . '" alt="loding image" /></div></div><span class="ciam-link"><a href = "' . wp_login_url() . '">Login</a></span><span class="ciam-link btn"><a href = "' . wp_registration_url() . '">Register</a></span></div>';
                    do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), $html);
                    return $html . ob_get_clean();
                } else {
                    ?>
                    <div id="error" ></div>
                    <script type="text/javascript">
                        jQuery(document).ready(function(){
                        jQuery("#error").text('You are not allowed to access this page !').css('color', 'red');
                        setTimeout(function(){
                        window.location.href = '<?php echo wp_login_url() ?>';
                        }, 2000);
                        });</script>
                    <?php
                }
            }
            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), "");
        }

        /*
         * Replace old password section in the wp admin
         */

        public function ciam_hook_passwordform() {
            $uri = $_SERVER['REQUEST_URI']; // getting the current page url
            $pagename = explode('?', basename($uri)); // checking for the query string
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function(){
            <?php
            if ($pagename[0] != "user-new.php" && $pagename[0] != "user-edit.php") { // condition to check the default add and edit page
                ?>
                        var lrObjectInterval22 = setInterval(function () {
                if(typeof LRObject !== 'undefined')
                {
                    clearInterval(lrObjectInterval22);
                    setTimeout(function(){ changepasswordform(); }, 500);
                    LRObject.$hooks.register('afterFormRender', function (name) {
                    if (name === "changepassword") {
                    jQuery('#changepassword-container').append('<span class="show-password"></span>');
                    }
                    if(name === 'otp')
                    {
                       
                        jQuery("#updatephone-container").after("<span id='authdiv_success'></span>");
                         ciamfunctions.message("An OTP has been sent.", "#authdiv_success", "success");
                    }
                    });
                    }
                    }, 1);
                    jQuery("#password th,#password td").html('');
                    jQuery("#password th").html('<span>Change Password</span>');
                    var content = '<a id="open_password_popup" class="button open ciam-password-button" href="javascript:void(0);">Change Password</a>';
                    content += '<div class="popup-outer-password" style="display:none;">';
                    content += '<span id="close_password_popup">';
                    content += '<img src="<?php echo CIAM_PLUGIN_URL . 'authentication/assets/images/fancy_close.png'; ?>" alt="close" />';
                    content += '</span>';
                    content += '<div class="popup-inner-password">';
                    content += '<span class="popup-txt">';
                    content += '<h1>';
                    content += '<strong>Please Enter New Password</strong>';
                    content += '</h1>';
                    content += '</span>';
                    content += '<div id="ciam_change_password_notification"></div>';
                    content += '<div id="changepassword-container"></div>';
                    content += '</div>';
                    content += '</div>';
                    content += '<span class="password-input-wrapper show-password">';
                    content += '<input style="display:hidden;" type="password" name="pass1" id="pass1" class="regular-text strong" value="" autocomplete="off" data-pw="Z4G%PbRnMl)krYm)vrCiNV!C" aria-describedby="pass-strength-result">';
                    content += '</span>';
                    jQuery(".user-pass1-wrap td").append(content);
            <?php } else {
                ?>
                    setTimeout(function(){ jQuery("#pass1-text,#pass1").attr('style', 'visibility:visible !important;'); }, 500);
            <?php }
            ?>
                });
            </script> 
            <?php
            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), "");
        }

        /*
         * Change Password handler
         */

        public function change_password_handler() {
            global $ciam_credencials, $message;
            $ciam_message = false;
            $user_id = get_current_user_id();
            $UserAPI = new \LoginRadiusSDK\CustomerRegistration\Authentication\UserAPI($ciam_credencials['apikey'], $ciam_credencials['secret']);
            $passform = isset($_POST['passform']) ? $_POST['passform'] : '';
            $oldpassword = isset($_POST['oldpassword']) ? $_POST['oldpassword'] : '';
            $newpassword = isset($_POST['newpassword']) ? $_POST['newpassword'] : '';

            if (($passform == 1) && !empty($oldpassword) && !empty($newpassword)) {
                    $accessToken = get_user_meta($user_id, 'accesstoken', true);
                    try {
                        $UserAPI->changeAccountPassword($accessToken, $_POST['oldpassword'], $_POST['newpassword']);
                    } catch (\LoginRadiusSDK\LoginRadiusException $e) {
                        $message = isset($e->getErrorResponse()->Description) ? $e->getErrorResponse()->Description : _e("Opps Something Went Wrong !");
                        add_user_meta($user_id, 'ciam_pass_error', sanitize_text_field($message));
                        $ciam_message = true;
                    }
            }
            register_setting('ciam_authentication_settings', 'ciam_authentication_settings', array($this, 'validation'));
            if (isset($_GET['updated']) && $ciam_message == false) {
                if (!empty(get_user_meta($user_id, 'ciam_pass_error', true))) {
                    ?>
                    <div class="updated notice is-dismissible">
                        <p><strong><?php echo get_user_meta($user_id, 'ciam_pass_error', true); ?></strong></p>
                        <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                    </div>
                    <?php
                    delete_user_meta($user_id, 'ciam_pass_error');
                }
            }
            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), '');
        }

        /*
         * change authentication link for the forgotpassword page....
         */

        public function custom_forgot_page() {
            global $ciam_setting;
            $forgot_page = get_permalink($ciam_setting['lost_password_page_id']);
            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), $forgot_page);
            return $forgot_page;
        }

    }

    new CIAM_Authentication_Passwordhandler();
}
