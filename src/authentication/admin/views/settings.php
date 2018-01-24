<?php

// Exit if called directly

if (!defined('ABSPATH')) {

    exit();

}

/**

 * The activation settings class.

 */

if (!class_exists('ciam_authentication_settings')) {



    class ciam_authentication_settings {



        /**

         * generate ciam page selection option

         * 

         * @param type $pages

         * @param type $settings

         * @param type $name

         * @return string

         */

        private function select_field($pages, $settings, $name) {

            $output = '<select class="ciam-row-field" name="ciam_authentication_settings[' . $name . ']" id="ciam_login_page_id">';

            $output .= '<option value="">' . __(' Select Page ', 'ciam-plugin-slug') . '</option>';

            foreach ($pages as $page) {

                $select_page = '';



                if (isset($settings[$name]) && $page->ID == $settings[$name]) {

                    $select_page = ' selected="selected"';

                }

                $output .= '<option value="' . $page->ID . '" ' . $select_page . '>' . $page->post_title . '</option>';

            }

            $output .= '</select>';

            /* action for debug mode */

            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), $output);

            return $output;

        }
        
        private function checkbox($settings , $name , $class)
        {
//            $output = ' <input type="hidden" name="ciam_authentication_settings[' . $name .']">
//                <input type="checkbox" name="ciam_authentication_settings['.$name.']" value="1" ';
            $output = '<input type="checkbox" name="ciam_authentication_settings['.$name.']" value="1" ';
                
            if(isset($settings[$name]) && ($settings[$name] == '1' || $settings[$name] == 'username'))
            {
                $output .= 'checked class="'.$class.'" id="ciam-'.$name .'"/>';
            }
            else {
               
                $output .= ' class="'.$class.'" id="ciam-'.$name.'"/>';
               
            }
            
          return $output;
        }
        private function select_template($template_array , $settings , $name)
        {
            
           // 
            $output = '<select class="ciam-row-field" name="ciam_authentication_settings[' . $name . ']" id="ciam_login_page_id">';
             $output .= '<option value="">' . __(' Select Template ', 'ciam-plugin-slug') . '</option>';
             
            if(!in_array('default', $template_array) || empty($template_array))
            {
                array_push($template_array, 'default');
            }

            foreach ($template_array as $template) {

                $select_template = '';
                if (isset($settings[$name]) && !empty($settings[$name]) && $template == $settings[$name]) {

                    $select_template = ' selected="selected"';

                }

                $output .= '<option value="' . $template . '" ' . $select_template . '>' . $template . '</option>';

            }

            $output .= '</select>';

           

            return $output;
        }



        /*

         * This function will render the HTML.....

         */



        public function render_options_page($args) {

            global $ciam_setting, $ciam_credencials;
            $cloudAPI = new \LoginRadiusSDK\Advance\CloudAPI($ciam_credencials['apikey'], $ciam_credencials['secret']);
                    try{
                    $config = $cloudAPI->getConfigurationList();
                    }
                    catch (\LoginRadiusSDK\LoginRadiusException $e) { 
                          $currentErrorResponse = "Something went wrong: " . $e->getErrorResponse()->description;
                          add_settings_error('ciam_authentication_settings', esc_attr('settings_updated'), $currentErrorResponse, 'error');
            }
                    try{
                     $wpclient = new \LoginRadiusSDK\Clients\WPHttpClient($ciam_credencials['apikey'], $ciam_credencials['secret']);
                     try
                     {
                     $query_array = array('apiKey' => $ciam_credencials['apikey'], 'apiSecret' => $ciam_credencials['secret']);
                    

                $response = json_decode($wpclient->request("https://config.lrcontent.com/ciam/appInfo/templates", $query_array),true);
                
                     }
                      catch (\LoginRadiusSDK\LoginRadiusException $e) { 
                          $currentErrorResponse = "Something went wrong: " . $e->getErrorResponse()->description;
                          add_settings_error('ciam_authentication_settings', esc_attr('settings_updated'), $currentErrorResponse, 'error');
            }
        } catch (\LoginRadiusSDK\LoginRadiusException $e) { 

                $currentErrorResponse = "Something went wrong: " . $e->getErrorResponse()->description;
                          add_settings_error('ciam_authentication_settings', esc_attr('settings_updated'), $currentErrorResponse, 'error');
            }
                     
               



            $pages = get_pages($args);

            $ciam_setting = get_option('Ciam_Authentication_settings');

            ?>

<div class="wrap active-wrap cf">
  <header>
    <h1 class="logo"><a href="//www.loginradius.com" target="_blank">Authentication Page Configuration</a></h1>
  </header>
  <div class="cf">
    <form action="options.php" method="post">
      <?php

                        settings_fields('ciam_authentication_settings');

                        settings_errors();

                        ?>
      <ul class="ciam-options-tab-btns">
        <li class="nav-tab ciam-active" data-tab="ciam_options_tab-1">
          <?php _e('User Registration', 'ciam-plugin-slug') ?>
        </li>
        <li class="nav-tab" data-tab="ciam_options_tab-2">
          <?php _e('Authentication', 'ciam-plugin-slug') ?>
        </li>
       
        
        <li class="nav-tab" data-tab="ciam_options_tab-4">
          <?php _e('Advanced Settings', 'ciam-plugin-slug') ?>
        </li>
        <?php do_action("ciam_auth_tab_title"); ?>
        <li class="nav-tab" data-tab="ciam_options_tab-9">
          <?php _e('Short Codes', 'ciam-plugin-slug') ?>
        </li>
      </ul>
      <div id="ciam_options_tab-1" class="ciam-tab-frame ciam-active">
        <div class="ciam_options_container">
          <div class="ciam-row">
            <h3>
              <?php _e('User Registration integration', 'ciam-plugin-slug'); ?>
            </h3>
            <div>
              <?php

                                        /* action for hosted page */

                                        do_action("hosted_page");

                                        ?>
              <div id="autopage-generate">
                   <input type="hidden" name="ciam_authentication_settings[ciam_autopage]">
                  <?php echo $this->checkbox($ciam_setting , 'ciam_autopage' , 'ciam-toggle');?>
            
                <label class="ciam-show-toggle" for="ciam-ciam_autopage">
                  <?php _e('Auto Generate Authentication Page'); ?>
                  <span class="ciam-tooltip" data-title="<?php _e('This feature when enabled, automatically generates Authentication Pages.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </label>
                <div class="ciam-row ciam-custom-page-settings">
                  <div>
                    <label> <span class="ciam_property_title">
                      <?php _e('Login page', 'ciam-plugin-slug'); ?>
                      <span class="ciam-tooltip" data-title="<?php _e('Add login page short code from Short Code tab in selected page.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </span> <?php echo $this->select_field($pages, $ciam_setting, 'login_page_id'); ?> </label>
                  </div>
                  <div>
                    <label> <span class="ciam_property_title">
                      <?php _e('Registration page', 'ciam-plugin-slug'); ?>
                      <span class="ciam-tooltip" data-title="<?php _e('Add registration page short code from Short Code tab in selected page.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </span> <?php echo $this->select_field($pages, $ciam_setting, 'registration_page_id'); ?> </label>
                  </div>
                  <div>
                    <label> <span class="ciam_property_title">
                      <?php _e('Forgot Password Page', 'ciam-plugin-slug'); ?>
                      <span class="ciam-tooltip" data-title="<?php _e('Add forgot password page short code from Short Code tab in selected page.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </span> <?php echo $this->select_field($pages, $ciam_setting, 'lost_password_page_id'); ?> </label>
                  </div>
                  <div>
                    <label> <span class="ciam_property_title">
                      <?php _e('Reset password page', 'ciam-plugin-slug'); ?>
                      <span class="ciam-tooltip" data-title="<?php _e('Add reset password page short code from Short Code tab in selected page.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </span> <?php echo $this->select_field($pages, $ciam_setting, 'change_password_page_id'); ?> </label>
                  </div>
                </div>
              </div>
            </div>
            <hr>
            <h3>
              <?php _e('Redirection settings after login ', 'ciam-plugin-slug'); ?>
              <span class="active-tooltip" data-title="<?php _e('This feature sets the redirection to the page where user will get redirected to post login.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h3>
            <div class="custom-radio">
              <input id="radio0" type="radio" class="loginRedirectionRadio" name="ciam_authentication_settings[after_login_redirect]" value="samepage" <?php echo (!isset($ciam_setting['after_login_redirect']) || $ciam_setting['after_login_redirect'] == 'samepage' ) ? 'checked' : ''; ?>/>
              <label for="radio0"> <?php _e('Redirect to the same page where the user logged in', 'ciam-plugin-slug'); ?> </label>
            </div>
            <div class="custom-radio">
              <input id="radio2" type="radio" class="loginRedirectionRadio" name="ciam_authentication_settings[after_login_redirect]" value="homepage" <?php echo ( isset($ciam_setting['after_login_redirect']) && $ciam_setting['after_login_redirect'] == 'homepage' ) ? 'checked' : ''; ?>/>
              <label for="radio2">
              <?php _e('Redirect to the home page of your WordPress site', 'ciam-plugin-slug'); ?>
              </label> </div>
             <div class="custom-radio">
              <input id="radio3" type="radio" class="loginRedirectionRadio" name="ciam_authentication_settings[after_login_redirect]" value="dashboard" <?php echo ( isset($ciam_setting['after_login_redirect']) && $ciam_setting['after_login_redirect'] == 'dashboard' ) ? 'checked' : ''; ?> />
              <label for="radio3">
              <?php _e('Redirect to the user\'s account dashboard', 'ciam-plugin-slug'); ?>
              </label></div>
            <div class="custom-radio">
            <input id="radio4" type="radio" class="loginRedirectionRadio custom" id="customUrl" name="ciam_authentication_settings[after_login_redirect]" value="custom"  <?php echo ( isset($ciam_setting['after_login_redirect']) && $ciam_setting['after_login_redirect'] == 'custom' ) ? 'checked' : ''; ?>/>
            <label for="radio4">
            <?php _e('Redirect to a custom URL'); ?>
            </label>
            <div class="ciam-row" id="customRedirectUrlField">
                
                <input type="text" class="ciam-row-field" id="customRedirectOther" name="ciam_authentication_settings[custom_redirect_other]" value="<?php echo (isset($ciam_setting['custom_redirect_other'])) ? $ciam_setting['custom_redirect_other'] : ''; ?>" autofill='off' autocomplete='off' >
              
            </div>
            </div>
          </div>
        </div>
      </div>
      <div id="ciam_options_tab-2" class="ciam-tab-frame"> 
        
        <!-- Authentication Flow Type -->
        
        <div class="ciam_options_container">
          <div class="ciam-row ciam-ur-shortcodes loginoptions">
            
            
            <!-- Phone Login template -->
          
            <div id="emailflowdiv">
              <h3>
              <?php _e('Email Authentication'); ?>
              </h3>
                   <input type="hidden" name="ciam_authentication_settings[prompt_password]">
                   <?php echo $this->checkbox($ciam_setting , 'prompt_password' , 'ciam-toggle');?>
                
                <label class="ciam-show-toggle" for="ciam-prompt_password">
               
                  <?php _e('Enable prompt password on Social login'); ?>
                  <span class="ciam-tooltip" data-title="<?php _e('This feature when enabled, will prompt the user to set the password at the time of login for the time from any social provider.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </label>
                  <input type="hidden" name="ciam_authentication_settings[login_type]">
                   <?php echo $this->checkbox($ciam_setting , 'login_type' , 'ciam-toggle');?>
                 
                <label class="ciam-show-toggle" for="ciam-login_type">
               
                  <?php _e('Enable login with username'); ?>
                  <span class="ciam-tooltip" data-title="<?php _e('This feature when enabled, will let the user to login with username as well as password.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </label>
                  
                 <input type="hidden" name="ciam_authentication_settings[askEmailForUnverifiedProfileAlways]">
                   <?php echo $this->checkbox($ciam_setting , 'askEmailForUnverifiedProfileAlways' , 'ciam-toggle');?>
                <label class="ciam-show-toggle" for="ciam-askEmailForUnverifiedProfileAlways">
               
                  <?php _e('Ask for email from unverified user'); ?>
                  <span class="ciam-tooltip" data-title="<?php _e('This feature when enabled, will ask for email every time user tries to login if email is not verified.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </label>
                  
                 <input type="hidden" name="ciam_authentication_settings[AskRequiredFieldsOnTraditionalLogin]">
                    <?php echo $this->checkbox($ciam_setting , 'AskRequiredFieldsOnTraditionalLogin' , 'ciam-toggle');?>
                <label class="ciam-show-toggle" for="ciam-AskRequiredFieldsOnTraditionalLogin">
               
                  <?php _e('Ask for required field on Traditional Login'); ?>
                  <span class="ciam-tooltip" data-title="<?php _e('This feature when enabled, will ask for newly added required fields on traditional login.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </label>
                 <?php
                 if(isset($response['EmailTemplates']))
                 {
                 
?> 
              <div id="customemailtemplates">
                <div> <span class="ciam_property_title first">
                  <?php _e('Welcome email : ', 'ciam-plugin-slug'); ?>
                        <span class="ciam-tooltip" data-title="<?php _e('Enter the name of the custom Welcome Email template which is created in the LoginRadius Dashboard', 'ciam-plugin-slug'); ?>"> 
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                  </span>
                    <?php echo $this->select_template($response['EmailTemplates']['Welcome'], $ciam_setting , 'welcome-template');?>
                </div>
                <div> <span class="ciam_property_title">
                  <?php _e('Reset password email : ', 'ciam-plugin-slug'); ?>
                        <span class="ciam-tooltip" data-title="<?php _e('Enter the name of the custom Reset Password Email template which is created in the LoginRadius Dashboard', 'ciam-plugin-slug'); ?>"> 
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                  </span>
                    <?php echo $this->select_template($response['EmailTemplates']['ResetPassword'], $ciam_setting , 'reset-template');?>

                </div>
                <div> <span class="ciam_property_title">
                  <?php _e('Account verification email : ', 'ciam-plugin-slug'); ?>
                        <span class="ciam-tooltip" data-title="<?php _e('Enter the name of the custom Verification Email template which is created in the LoginRadius Dashboard', 'ciam-plugin-slug'); ?>"> 
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                  </span>
                     <?php echo $this->select_template($response['EmailTemplates']['Verification'], $ciam_setting , 'account-verification-template');?>

                </div>
              </div>
                <?php
                 }
                 ?>
            </div>
            <hr>
            <?php
           if(isset($config) && $config->IsPhoneLogin)
           {
?>
              <div class="phonediv">
                  <h3>
              <?php _e('Phone Authentication'); ?>
            </h3>
                  <input type="hidden" name="ciam_authentication_settings[existPhoneNumber]">
                   <?php echo $this->checkbox($ciam_setting , 'existPhoneNumber' , 'ciam-toggle');?>
                <label class="ciam-show-toggle" for="ciam-existPhoneNumber">
               
                  <?php _e('Check phone number exist or not?'); ?>
                  <span class="ciam-tooltip" data-title="<?php _e('Turn on, if you want to enable Phone Exist functionality', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </label>
                      <?php
                 if(isset($response['SMSTemplates']))
                 {
?> 
              <div id="customemailtemplates">
              <div>
                  <span class="ciam_property_title">
                <?php _e('Use custom phone verification template', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip" id="custom-phone-temp" data-title="<?php _e('Enter the name of the custom phone verification template which is created in the LoginRadius Dashboard', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </span>
                <span class="" for="custom-phone-template">
                    <?php echo $this->select_template($response['SMSTemplates']['Verification'], $ciam_setting , 'smsTemplatePhoneVerification');?>

                </span>
              </div>
              <div>
                  <span class="ciam_property_title">
                <?php _e('Use custom phone welcome template', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip" id="custom-phone-temp" data-title="<?php _e('Enter the name of the custom phone welcome template which is created in the LoginRadius Dashboard', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </span>
                <span class="" for="custom-phone-welcome-template">
                        <?php echo $this->select_template($response['SMSTemplates']['Welcome'], $ciam_setting , 'smsTemplateWelcome');?>

                </span>
              </div>
             </div>
                  <?php
                 }
                 ?>
            </div>
            <?php
           }
            ?>
            
            
            
            
          </div>
        </div>
      </div>
     
      <div id="ciam_options_tab-4" class="ciam-tab-frame">
        <div class="ciam_options_container">
          <div class="ciam-row">
            <h3>
              <?php _e('Advanced options', 'ciam-plugin-slug'); ?>
            </h3>
              <div>
                  <input type="hidden" name="ciam_authentication_settings[onclicksignin]">
              <input type="checkbox" class="ciam-toggle" id="ciam-oneclicksignin" name="ciam_authentication_settings[onclicksignin]" value='1' <?php echo ( isset($ciam_setting['onclicksignin']) && $ciam_setting['onclicksignin'] == '1' ) ? 'checked' : '' ?> />
              <label class="ciam-show-toggle" for="ciam-oneclicksignin">
                <?php _e('Enable Instant Link Login'); ?>
                <span class="ciam-tooltip oneclick-signin-tooltip" data-title="<?php _e('This feature enables Instant Link Login on the login form.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </label>
            </div>
            
                <?php
              if(isset($response['EmailTemplates']))
              {
?> 
              <div class="ciam-row advance-template" id="hideoneclickdiv">
              <label class="custom-label">
                <?php _e('Instant Link Login custom template', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip" id="custom-oneclick-temp" data-title="<?php _e('Enter the name of the custom template which is created in the LoginRadius Dashboard', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </label>
              <div>
                 
                <label class="" for="custom-onclick-template">
                     <?php echo $this->select_template($response['EmailTemplates']['InstantSignIn'], $ciam_setting , 'instantLinkLoginEmailTemplate');?>
                  
                </label>
              </div>
                 </div>
                <?php
              }
            
               if(isset($config) && $config->IsPhoneLogin)
           {   
               
?>
                <div>
                    <input type="hidden" name="ciam_authentication_settings[instantotplogin]">
              <label class="active-toggle">
                  <input type="checkbox" class="active-toggle" id="ciam-otpsignin" name="ciam_authentication_settings[instantotplogin]" value="1" <?php echo ( isset($ciam_setting['instantotplogin']) && $ciam_setting['instantotplogin'] == '1' ) ? 'checked' : ''; ?> />
                <span class="active-toggle-name">
                <?php _e('Enable instant OTP login'); ?>
                </span> <span class="ciam-tooltip tip-top" data-title="<?php _e('Turn on, if you want to enable instant OTP login', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </label>
            </div>
              <?php
              if(isset($response['SMSTemplates']))
              {
?> 
              <div class="ciam-row advance-template" id="hideotpdiv">
              <label class="custom-label">
                <?php _e('Instant OTP Login custom template', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip" id="custom-oneclick-temp" data-title="<?php _e('Enter the name of the custom template which is created in the LoginRadius Dashboard', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </label>
              <div>
                 
                <label class="" for="custom-otp-template">
                     <?php echo $this->select_template($response['SMSTemplates']['OneTimePassCode'], $ciam_setting , 'instantOTPLoginEmailTemplate');?>
                  
                </label>
              </div>
                 </div>
                <?php
              }
           }
           ?>
         
            <div>
                <input type="hidden" name="ciam_authentication_settings[password-stength]">
              <label class="active-toggle">
                  <?php echo $this->checkbox($ciam_setting , 'password-stength' , 'active-toggle');?>
                
                <span class="active-toggle-name">
                <?php _e('Enable password strength', 'ciam-plugin-slug'); ?>
                </span> <span class="ciam-tooltip tip-top" data-title="<?php _e('This feature when enabled, shows the strength bar under the password field on registration form, reset password form and change password form.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </label>
            </div>
            <div>
                <input type="hidden" name="ciam_authentication_settings[disable_minified_version]">
              <label class="active-toggle">
                  <?php echo $this->checkbox($ciam_setting , 'disable_minified_version' , 'active-toggle');?>
               
                <span class="active-toggle-name">
                <?php _e('Enable minified version of JS/CSS file?', 'ciam-plugin-slug'); ?>
                </span> <span class="ciam-tooltip tip-top" data-title="<?php _e('This feature enables minified version of js/css file.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </label>
            </div>
              
              <?php
              if(isset($config) && $config->TwoFactorAuthentication->IsEnabled && isset($response['SMSTemplates']))
              {
?>
              <div class="ciam-ur-shortcodes loginoptions Notification-timeout-settings-field advance-template">
              <p class="margin-0">&nbsp;</p>
              <h3 class="ciam_property_title">
                <?php _e('Use custom Two Factor Authentication OTP template', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip " id="autohidetime-temp" data-title="<?php _e('Enter the name of the custom OTP template which is created in the LoginRadius Dashboard', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h3>
              <label>
                   <?php echo $this->select_template($response['SMSTemplates']['SecondFactorAuthentication'], $ciam_setting , 'smsTemplate2FA');?>
              </label>
            </div>
              <?php
             }
              ?>
             
              
              
            <div class="ciam-ur-shortcodes loginoptions Notification-timeout-settings-field">
              <p class="margin-0">&nbsp;</p>
              <h3 class="ciam_property_title">
                <?php _e('Notification timeout settings', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip " id="autohidetime-temp" data-title="<?php _e('Enter the duration (in seconds) to hide response message.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h3>
              <label>
                <input placeholder="Time In Seconds" type="number" class="ciam-row-field custom-tooltip" name="ciam_authentication_settings[autohidetime]" id="autohidetime" value="<?php echo (isset($ciam_setting['autohidetime']) && !empty($ciam_setting['autohidetime'])) ? $ciam_setting['autohidetime'] : '' ?>" />
              </label>
            </div>
               
            <div> <br>
              <h3>
                <?php _e('Terms and condition', 'ciam-plugin-slug'); ?>
                <span class="active-tooltip" data-title="<?php _e('Enter the content which needs to be displayed on the registration form.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h3>
              <label>
                <textarea rows="4" cols="50" name="ciam_authentication_settings[terms_conditions]" id="terms_conditions"><?php echo (isset($ciam_setting['terms_conditions']) && !empty($ciam_setting['terms_conditions']) ? $ciam_setting['terms_conditions'] : ''); ?></textarea>
              </label>
            </div>
            <div>
              <h3>
                <?php _e('Enter custom ciam options for LoginRadius interface.', 'ciam-plugin-slug'); ?>
                <span class="active-tooltip" data-title="<?php _e('This feature allows custom CIAM options to be enabled on the LoginRadius interface.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h3>
              <label>
              <textarea rows="4" cols="50" name="ciam_authentication_settings[custom_field_obj]" id="custom_field_obj"><?php echo (isset($ciam_setting['custom_field_obj']) && !empty($ciam_setting['custom_field_obj']) ? $ciam_setting['custom_field_obj'] : ''); ?></textarea>
              <p><b>
                <?php _e('Custom customer registration options that are added in the LoginRadius js. ', 'ciam-plugin-slug'); ?>
                </b></p>
              </label>
            </div>
          </div>
        </div>
        <div class="ciam_options_container">
          <div class="ciam-row ciam-ur-shortcodes">
            <h3>
              <?php _e('Debug log', 'ciam-plugin-slug'); ?>
            </h3>
              <div>
                  <input type="hidden" name="ciam_authentication_settings[debug_enable]">
            <label class="active-toggle">
                <?php echo $this->checkbox($ciam_setting , 'debug_enable' , 'active-toggle');?>
              
              <span class="active-toggle-name">
              <?php _e('Enable log ?', 'ciam-plugin-slug'); ?>
              </span> <span class="ciam-tooltip tip-top" data-title="<?php _e('This feature when enabled, automatically generates debug logs.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </label>
              </div>
          </div>
        </div>
      </div>
      <?php do_action("ciam_auth_tab_page"); ?>
      <div id="ciam_options_tab-9" class="ciam-tab-frame"> 
        
        <!-- Authentication Flow Type -->
        
        <div class="ciam_options_container" id="ciam-shortcodes">
          <div class="ciam-row ciam-ur-shortcodes">
            <h3>
              <?php _e('User registration short codes', 'ciam-plugin-slug'); ?>
            </h3>
            <div class="ciam_shortcode_div">
              <h4>
                <?php _e('Login form', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip tip-top" data-title="<?php _e('Copy and paste the following shortcode into a page or post to display the login form', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h4>
              <textarea rows="1" onclick="this.select()" spellcheck="false" class="ciam-shortcode" readonly>[ciam_login_form]</textarea>
            </div>
            <div class="ciam_shortcode_div">
              <h4>
                <?php _e('Registration form', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip tip-top" data-title="<?php _e('Copy and paste the following shortcode into a page or post to display the registration form', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h4>
              <textarea rows="1" onclick="this.select()" spellcheck="false" class="ciam-shortcode" readonly>[ciam_registration_form]</textarea>
            </div>
            <div class="ciam_shortcode_div">
              <h4>
                <?php _e('Forgot password form', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip tip-top" data-title="<?php _e('Copy and paste the following shortcode into a page or post to display the forgot password form', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h4>
              <textarea rows="1" onclick="this.select()" spellcheck="false" class="ciam-shortcode" readonly>[ciam_forgot_form]</textarea>
            </div>
            <div class="ciam_shortcode_div">
              <h4>
                <?php _e('Reset password form', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip tip-top" data-title="<?php _e('Copy and paste the following shortcode into a page or post to display reset password form', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h4>
              <textarea rows="1" onclick="this.select()" spellcheck="false" class="ciam-shortcode" readonly>[ciam_password_form]</textarea>
            </div>
            <div class="ciam_shortcode_div">
              <h4>
                <?php _e('Default WordPress login form', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip tip-top" data-title="<?php _e('Copy and paste the following shortcode into a page or post to display the default Wordpress login form. This can be used while configuring your site.It is independent from LR forms.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h4>
              <textarea rows="1" onclick="this.select()" spellcheck="false" class="ciam-shortcode" readonly>[ciam_wp_default_login]</textarea>
            </div>
          </div>
        </div>
      </div>
      <div style="position: relative;">
        <div class="ciam-option-disabled-hr" style="display: none;"></div>
      </div>
      <p class="submit" id="savebtn">
        <?php submit_button('Save settings', 'primary', 'submit', false); ?>
      </p>
    </form>
  </div>
</div>
<?php

            /* action for debug mode */

            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), "");

        }



    }



    new ciam_authentication_settings();

}
