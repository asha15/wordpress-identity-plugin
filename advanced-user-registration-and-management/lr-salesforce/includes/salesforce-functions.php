<?php

if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('LR_SalesForce_function')) {

    /**
     * The main class and initialization point of the plugin.
     */
    class LR_SalesForce_function {

        function __construct() {
            add_action('lr_update_social_profile_data', array($this, 'salesforce_user_profile'), 10, 2);
            // ajax for salesforce key verification
            add_action('wp_ajax_login_radius_verify_salesforce_keys', array($this, 'login_radius_verify_salesforce_keys'));
            add_action('wp_ajax_login_radius_sf_authentication', array($this, 'login_radius_sf_authentication'));
        }

        /**
         * 
         * @global type $wpdb
         * @param type $user_id
         * @return type
         */
        function get_data_for_salesforce($user_id) {
            global $wpdb;
            $lr_salesforce_settings = get_option('LR_Salesforce_Settings');

            $params = array(
                'grant_type' => 'password',
                'client_id' => !empty($lr_salesforce_settings['salesforce_key']) ? $lr_salesforce_settings['salesforce_key'] : '',
                'client_secret' => !empty($lr_salesforce_settings['salesforce_secret']) ? $lr_salesforce_settings['salesforce_secret'] : '',
                'username' => !empty($lr_salesforce_settings['salesforce_username']) ? $lr_salesforce_settings['salesforce_username'] : '',
                'password' => !empty($lr_salesforce_settings['salesforce_password']) ? $lr_salesforce_settings['salesforce_password'] : ''
            );

            $result['resonse'] = SalesforceClient::get_access_token($params);
            $tempMergeVars = array();
            if ($result['resonse'] != false && isset($result['resonse']->access_token)) {
                $responsenew = SalesforceClient::get_salesforce_object_fields($result['resonse']->instance_url, $result['resonse']->access_token, $lr_salesforce_settings['salesforce_object_type']);
                if (isset($responsenew->fields)) {
                    foreach ($responsenew->fields as $fieldsParent) {
                        if (isset($fieldsParent->updateable) && $fieldsParent->updateable && $fieldsParent->type != 'reference' && $fieldsParent->type != 'currency' && $fieldsParent->type != 'ID' && $fieldsParent->type != 'masterrecord' && $fieldsParent->type != 'picklist' && $fieldsParent->type != 'boolean' && $fieldsParent->name != 'EmailBouncedDate' && $fieldsParent->name != 'EmailBouncedReason') {
                            $tempMergeVars[] = $fieldsParent->name;
                        }
                    }
                }
                $merge_vars = array();
                foreach ($tempMergeVars as $tempMergeVar) {
                    $value = '';
// if value exists for this merge var
                    if (isset($lr_salesforce_settings['salesforce_mapping_fields']) && isset($lr_salesforce_settings['salesforce_mapping_fields'][$tempMergeVar]) && is_array($lr_salesforce_settings['salesforce_mapping_fields'])) {
// if field is from any separate profile data table
                        if (count($lr_salesforce_settings['salesforce_mapping_fields']) > 1) {
// execute query according to the prefix
                            $tempParts = explode('-', $lr_salesforce_settings['salesforce_mapping_fields'][$tempMergeVar]);
                            $tempParts[1] = isset($tempParts[1]) ? $tempParts[1] : '';
                            switch ($tempParts[1]) {
// basic_profile_data table
                                case 'basic':
                                    $value = $wpdb->get_var('SELECT ' . $tempMergeVar . ' FROM ' . $wpdb->base_prefix . 'lr_basic_profile_data WHERE wp_users_id = ' . $user_id);
                                    if ($tempParts[$tempMergeVar] == 'birth_date') {
                                        if (strlen($value) < 11 && !empty($value)) {
                                            $value = date('Y-m-d', strtotime($value));
                                        } else {
                                            $value = "2015-11-11";
                                        }
                                    }
                                    break;
// extended_location_data table
                                case 'exloc':
                                    $value = $wpdb->get_var('SELECT ' . $tempMergeVar . ' FROM ' . $wpdb->base_prefix . 'lr_extended_location_data WHERE wp_users_id = ' . $user_id);
                                    break;
// extended_profile_data table
                                case 'exprofile':
                                    $value = $wpdb->get_var('SELECT ' . $tempMergeVar . ' FROM ' . $wpdb->base_prefix . 'lr_extended_profile_data WHERE wp_users_id = ' . $user_id);
                                    if ($tempParts[$tempMergeVar] == 'website' && empty($value)) {
                                        $userInfoOnce = get_userdata($user_id);
                                        $value = $userInfoOnce->user_url;
                                    }
                                    break;
                                default :
// fetch user info
                                    $userInfo = get_userdata($user_id);
// get data according to the value
                                    switch ($tempParts[0]) {
                                        case 'Username':
                                            $value = $userInfo->user_login;
                                            break;
                                        case 'First Name':
                                            $value = get_user_meta($user_id, 'first_name', true);
                                            break;
                                        case 'Last Name':
                                            $value = get_user_meta($user_id, 'last_name', true);
                                            break;
                                        case 'Nice Name':
                                            $value = $userInfo->user_nicename;
                                            break;
                                        case 'Email':
                                            $value = $userInfo->user_email;
                                            break;
                                        case 'Profile Url':
                                            $value = $userInfo->user_url;
                                            break;
                                        case 'Registration Date':
                                            $value = date('Y-m-d', strtotime($userInfo->user_registered));
                                            break;
                                        case 'Display Name':
                                            $value = $userInfo->display_name;
                                            break;
                                        case 'Bio':
                                            $value = get_user_meta($user_id, 'description', true);
                                            break;
                                        case 'Phone':
                                            $value = get_user_meta($user_id, 'lr_phone', true);
                                            break;
                                    }
                            }
                        }
                    }
                    if (empty($value)) {
                        if (isset($lr_salesforce_settings['salesforce_mapping_fields_dataType']) && $lr_salesforce_settings['salesforce_mapping_fields_dataType'][$tempMergeVar] == "date") {
                            $value = '2222-11-11';
                        }
                    }
                    $merge_vars[$tempMergeVar] = $value;
                }
                if (isset($merge_vars['Birthdate'])) {
                    if (strlen($merge_vars['Birthdate']) < 11 && !empty($merge_vars['Birthdate'])) {
                        $merge_vars['Birthdate'] = date('Y-m-d', strtotime($merge_vars['Birthdate']));
                    } else {
                        unset($merge_vars['Birthdate']);
                    }
                }

                $requiredFields = array();
                $removableFields = array();
                if ($lr_salesforce_settings['salesforce_object_type'] == 'Lead') {
                    $requiredFields = array('LastName', 'Company');
                    $removableFields = array('CloseDate', 'EmailBouncedDate', 'EmailBouncedReason', 'Jigsaw');
                } else if ($lr_salesforce_settings['salesforce_object_type'] == 'Contact') {
                    $requiredFields = array('LastName');
                }
                foreach ($requiredFields as $value) {
                    if ($value == 'CloseDate') {
                        if (isset($merge_vars['CloseDate']) && strlen($merge_vars[$value]) < 11 && !empty($merge_vars['CloseDate'])) {
                            $merge_vars['CloseDate'] = date('Y-m-d', strtotime($merge_vars['CloseDate']));
                        } else {
                            $merge_vars[$value] = "1111-11-11";
                        }
                    }
                    if (!isset($merge_vars[$value]) || empty($merge_vars[$value])) {
                        $merge_vars[$value] = "NA";
                    }
                }

                foreach ($removableFields as $Removable) {
                    if (isset($merge_vars[$Removable])) {
                        unset($merge_vars[$Removable]);
                    }
                }
                $result['data'] = json_encode($merge_vars);
                return $result;
            }
        }
        /**
         * 
         * @param type $userId
         * @param type $userProfile
         */
        function salesforce_user_profile($userId, $userProfile = '') {
            $lr_salesforce_settings = get_option('LR_Salesforce_Settings');
            if (isset($lr_salesforce_settings['salesforce_subscribe']) && $lr_salesforce_settings['salesforce_subscribe'] == '1') {
                if (isset($lr_salesforce_settings['salesforce_key']) && $lr_salesforce_settings['salesforce_key'] != '' &&
                        isset($lr_salesforce_settings['salesforce_secret']) && $lr_salesforce_settings['salesforce_secret'] != '' &&
                        isset($lr_salesforce_settings['salesforce_username']) && $lr_salesforce_settings['salesforce_username'] != '' &&
                        isset($lr_salesforce_settings['salesforce_password']) && $lr_salesforce_settings['salesforce_password'] != '') {
                    $sfAccountId = get_user_meta($userId, 'login_radius_salesforce_id', true);
                    $sfAccountType = get_user_meta($userId, 'login_radius_salesforce_obj', true);
                    $output = $this->get_data_for_salesforce($userId);
                    if (!empty($sfAccountId) && !empty($sfAccountType)) {
                        if (isset($lr_salesforce_settings['salesforce_subscribe_update']) && $lr_salesforce_settings['salesforce_subscribe_update'] == '1') {
                            $this->update_salesforce_user_profile($userId, $output, $sfAccountType, $sfAccountId);
                        }
                    } else {
                        if( ! empty( $lr_salesforce_settings['salesforce_object_type'] ) ) {
                            $this->create_salesforce_user_profile( $userId, $output, $lr_salesforce_settings['salesforce_object_type'] );
                        }
                    }
                }
            }
        }

        /**
         * 
         * @param type $userId
         * @param type $output
         * @param type $sfAccountType
         */
        function create_salesforce_user_profile($userId, $output, $sfAccountType) {
            if (isset($output['resonse']->instance_url) && isset($output['resonse']->access_token)) {
                $response = SalesforceClient::create_salesforce_object($output['resonse']->instance_url, $output['resonse']->access_token, $output['data'], $sfAccountType);
                if (isset($response->success) && $response->success) {
                    update_user_meta($userId, 'login_radius_salesforce_id', $response->id);
                    update_user_meta($userId, 'login_radius_salesforce_obj', $sfAccountType);
                }
            }
        }

        /**
         * 
         * @param type $userId
         * @param type $output
         * @param type $sfAccountType
         * @param type $sfAccountId
         * @return type
         */
        function update_salesforce_user_profile($userId, $output, $sfAccountType, $sfAccountId) {
            if (isset($output['resonse']->instance_url) && isset($output['resonse']->access_token)) {
                return SalesforceClient::update_account_at_salesforce($output['resonse']->instance_url, $output['resonse']->access_token, $output['data'], $sfAccountId, $sfAccountType);
            }
        }

        /**
         * Verify API Key/Secret
         */
        function login_radius_verify_salesforce_keys() {
            $sfKey = trim($_POST['sfKey']);
            $sfSecret = trim($_POST['sfSecret']);
            $sfUsername = trim($_POST['sfUsername']);
            $sfPassword = trim($_POST['sfPassword']);
            if (!in_array('curl', get_loaded_extensions())) {
                die(json_encode(array('success' => false, 'isCurlEnabled' => false, 'errorMessage' => 'Please enable CURL ( requires "cURL support = enabled" in php.ini )')));
            }
            if (empty($sfKey)) {
                die(json_encode(array('success' => false, 'isEmpty' => true, 'errorField' => '"Salesforce Consumer Key"')));
            }
            if (empty($sfSecret)) {
                die(json_encode(array('success' => false, 'isEmpty' => true, 'errorField' => '"Salesforce Consumer Secret"')));
            }
            if (empty($sfUsername)) {
                die(json_encode(array('success' => false, 'isEmpty' => true, 'errorField' => '"Salesforce Username"')));
            }
            if (empty($sfPassword)) {
                die(json_encode(array('success' => false, 'isEmpty' => true, 'errorField' => '"Salesforce Password"')));
            }
            $params = array(
                'grant_type' => 'password',
                'client_id' => $sfKey,
                'client_secret' => $sfSecret,
                'username' => $sfUsername,
                'password' => $sfPassword
            );
            $validationResponse = $this->access_token_verification('', true, $params);
            if (!isset($validationResponse->access_token)) {
                $message = 'Error in validating Salesforce credentials';
                if (isset($validationResponse->error)) {
                    $message = 'Salesforce api resonse:   ' . $validationResponse->error;
                    if (isset($validationResponse->error_description)) {
                        $message .= ' "' . $validationResponse->error_description . '"';
                    }
                }
                die(json_encode(array('success' => false, 'isEmpty' => false, 'errorMessage' => $message)));
            } else {
                $options = get_option('LR_Salesforce_Settings');
                $options['salesforce_subscribe'] = '1';
                $options['salesforce_key'] = $sfKey;
                $options['salesforce_secret'] = $sfSecret;
                $options['salesforce_username'] = $sfUsername;
                $options['salesforce_password'] = $sfPassword;
                update_option('LR_Salesforce_Settings', $options);
                die(json_encode(array('success' => true)));
            }
        }

        /* Access token verification */

        /**
         * 
         * @param type $url
         * @param type $lr_salesforce_settings
         * @param type $fromAjax
         * @param type $values
         * @return type
         */
        function access_token_verification($lr_salesforce_settings, $fromAjax = false, $values = array()) {
            if (!$fromAjax) {
                $values = array(
                    'grant_type' => 'password',
                    'client_id' => $lr_salesforce_settings['sf_consumer_key'],
                    'client_secret' => $lr_salesforce_settings['sf_consumer_secret'],
                    'username' => $lr_salesforce_settings['sf_user_name'],
                    'password' => $lr_salesforce_settings['sf_password']
                );
            }
            return SalesforceClient::get_access_token($values);
        }

        /**
         * 
         * @global type $lr_salesforce_settings
         */
        function login_radius_sf_authentication() {
            global $lr_salesforce_settings;
            $params = array(
                'grant_type' => 'password',
                'client_id' => isset($_POST['sfKey']) ? trim($_POST['sfKey']) : '',
                'client_secret' => isset($_POST['sfSecret']) ? trim($_POST['sfSecret']) : '',
                'username' => isset($_POST['sfUsername']) ? trim($_POST['sfUsername']) : '',
                'password' => isset($_POST['sfPassword']) ? trim($_POST['sfPassword']) : ''
            );
            $type = isset($_POST['oType']) && !empty($_POST['oType']) ? trim($_POST['oType']) : "Lead";

            $resonse = SalesforceClient::get_access_token($params);
            if ($resonse != false && isset($resonse->access_token)) {
                $fieldNames = array();
                $fieldDataTypes = array();
                $responsenew = SalesforceClient::get_salesforce_object_fields(trim($resonse->instance_url), trim($resonse->access_token), $type);
                if (is_array($responsenew->fields) && count($responsenew->fields) > 0) {
                    foreach ($responsenew->fields as $fieldsParent) {
                        if (isset($fieldsParent->updateable) && $fieldsParent->type != 'reference' && $fieldsParent->type != 'currency' && $fieldsParent->type != 'ID' && $fieldsParent->type != 'id' && $fieldsParent->type != 'datetime' && $fieldsParent->type != 'masterrecord' && $fieldsParent->type != 'picklist' && $fieldsParent->type != 'boolean' && $fieldsParent->name != 'EmailBouncedDate' && $fieldsParent->name != 'LastActivityDate' && $fieldsParent->name != 'EmailBouncedReason') {
                            if (isset($lr_salesforce_settings['salesforce_merge_var_' . $type . '_' . $fieldsParent->name])) {
                                $field_name = $lr_salesforce_settings['salesforce_merge_var_' . $type . '_' . $fieldsParent->name];
                                $fieldNames[$fieldsParent->name] = $field_name;
                            } else {
                                $fieldNames[$fieldsParent->name] = '';
                            }
                            $fieldDataTypes[$fieldsParent->name] = $fieldsParent->type;
                        }
                    }
                }die(json_encode(array('success' => true, 'fields' => $fieldNames, 'objectType' => $type, 'oDataType' => $fieldDataTypes)));
            } else {
                die(json_encode(array('success' => false, 'description' => 'error in accessing token')));
            }
        }

    }

    new LR_SalesForce_function();
}