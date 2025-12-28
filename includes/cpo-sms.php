<?php
if (!defined('ABSPATH')) exit;

class CPO_Full_SMS {
    /**
     * ارسال پیامک اعلان سفارش جدید به مدیر با جایگزینی متغیرها در الگو
     */
    public static function send_notification($placeholders){
        $service    = get_option('cpo_sms_service');
        $apiKey     = get_option('cpo_sms_api_key');
        $sender     = get_option('cpo_sms_sender');
        $adminPhone = get_option('cpo_admin_phone');
        $pattern_code = get_option('cpo_sms_pattern_code'); 

        if (!$service || $service !== 'ippanel' || !$apiKey || !$adminPhone || !$sender || !$pattern_code) {
            if ($service === 'ippanel') { 
                 error_log("CPO Admin SMS Error: Cannot send notification. Missing IPPanel settings.");
            }
            return false;
        }

        $admin_variables_needed = ['product_name', 'customer_name', 'phone', 'qty', 'unit', 'load_location', 'note'];
        $variables = [];
        foreach($admin_variables_needed as $var_name) {
            $placeholder_key = '{' . $var_name . '}';
            $variables[$var_name] = isset($placeholders[$placeholder_key]) ? ($placeholders[$placeholder_key] ?: '-') : '-';
        }

        $sent = self::ippanel_send_pattern($apiKey, $sender, $adminPhone, $pattern_code, $variables);
        if (!$sent) {
             error_log("CPO Admin SMS FAILED to send to ".$adminPhone." using pattern ".$pattern_code);
        }
        return $sent;
    }

    public static function ippanel_send_pattern($apiKey, $sender, $to, $pattern_code, $variables){
        if (empty($apiKey) || empty($sender) || empty($to) || empty($pattern_code) || !is_array($variables)) {
            error_log("CPO ippanel_send_pattern Error: Invalid parameters provided.");
            return false;
        }

        $url = 'https://api2.ippanel.com/api/v1/sms/pattern/normal/send';
        $data = [
            'code'      => $pattern_code,
            'sender'    => $sender,
            'recipient' => $to,
            'variable'  => $variables,
        ];

        if (is_array($data['recipient'])) {
             $data['recipient'] = $data['recipient'][0];
        }
        $data['recipient'] = preg_replace('/[^\d]/', '', $data['recipient']); 

        $body = json_encode($data);
        if (json_last_error() !== JSON_ERROR_NONE) {
             return false;
        }

        $headers = [ 'Content-Type' => 'application/json', 'apikey' => $apiKey ];
        $args = [ 'body' => $body, 'headers' => $headers, 'method' => 'POST', 'data_format' => 'body', 'timeout' => 20 ];

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            error_log('CPO IPPanel WP HTTP Error: ' . $response->get_error_message());
            return false;
        } else {
            $http_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);

            if ($http_code >= 200 && $http_code < 300) {
                $result = json_decode($response_body);
                if ($result && isset($result->status->code) && $result->status->code == 0) {
                    return true; 
                } else {
                     return false;
                }
            } else {
                return false;
            }
        }
    } 
} 
?>