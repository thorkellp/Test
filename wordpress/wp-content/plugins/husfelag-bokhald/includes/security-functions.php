<?php
/**
 * Öryggis og validation functions
 * Húsfélags Bókhald
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Validate íbúðanúmer
 */
function hb_validate_ibudanumer($ibudanumer) {
    return preg_match('/^[A-Za-z0-9]{1,10}$/', $ibudanumer);
}

/**
 * Validate upphæð
 */
function hb_validate_amount($amount) {
    return is_numeric($amount) && $amount >= 0 && $amount <= 999999999.99;
}

/**
 * Validate hlutdeild
 */
function hb_validate_hlutdeild($hlutdeild) {
    return is_numeric($hlutdeild) && $hlutdeild > 0 && $hlutdeild <= 1;
}

/**
 * Validate dagsetning
 */
function hb_validate_date($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Validate flokkur
 */
function hb_validate_flokkur($flokkur, $tegund) {
    $valid_flokkar = array(
        'tekjur' => array('Mánaðargjöld', 'Sérstök gjöld', 'Vextir', 'Annað'),
        'gjold' => array('Viðhald', 'Þrif', 'Tryggingar', 'Rafmagn', 'Hiti', 'Vatn', 'Umsýsla', 'Annað')
    );
    
    return isset($valid_flokkar[$tegund]) && in_array($flokkur, $valid_flokkar[$tegund]);
}

/**
 * Sanitize currency amount
 */
function hb_sanitize_currency($amount) {
    $amount = preg_replace('/[^\d.,]/', '', $amount);
    $amount = str_replace(',', '.', $amount);
    return number_format(floatval($amount), 2, '.', '');
}

/**
 * Format currency fyrir display
 */
function hb_format_currency($amount) {
    return number_format($amount, 0, ',', '.') . ' kr.';
}

/**
 * Validate símanúmer
 */
function hb_validate_phone($phone) {
    $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
    return preg_match('/^[\+]?[\d]{7,15}$/', $phone);
}

/**
 * Sanitize símanúmer
 */
function hb_sanitize_phone($phone) {
    return preg_replace('/[^\d\+\-\s\(\)]/', '', $phone);
}

/**
 * Check ef notandi má gera aðgerð
 */
function hb_user_can_manage() {
    return current_user_can('manage_options') || current_user_can('manage_husfelag_bokhald');
}

/**
 * Log security events
 */
function hb_log_security_event($event, $details = '') {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $user = wp_get_current_user();
        $log_entry = sprintf(
            '[%s] Húsfélags Bókhald Security: %s - User: %s (%d) - IP: %s - Details: %s',
            date('Y-m-d H:i:s'),
            $event,
            $user->user_login,
            $user->ID,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $details
        );
        error_log($log_entry);
    }
}

/**
 * Rate limiting fyrir AJAX calls
 */
function hb_check_rate_limit($action, $limit = 10, $window = 300) {
    $user_id = get_current_user_id();
    $key = "hb_rate_limit_{$action}_{$user_id}";
    
    $attempts = get_transient($key);
    if ($attempts === false) {
        set_transient($key, 1, $window);
        return true;
    }
    
    if ($attempts >= $limit) {
        hb_log_security_event('Rate limit exceeded', "Action: $action, Attempts: $attempts");
        return false;
    }
    
    set_transient($key, $attempts + 1, $window);
    return true;
}

/**
 * Validate CSRF token
 */
function hb_verify_request($action = 'husfelag_ajax_nonce') {
    $nonce = sanitize_text_field($_REQUEST['nonce'] ?? '');
    if (!wp_verify_nonce($nonce, $action)) {
        hb_log_security_event('Invalid nonce', "Action: $action");
        return false;
    }
    
    if (!hb_user_can_manage()) {
        hb_log_security_event('Insufficient permissions', "Action: $action");
        return false;
    }
    
    return true;
}

/**
 * Sanitize input array
 */
function hb_sanitize_input_array($input, $rules) {
    $sanitized = array();
    
    foreach ($rules as $field => $rule) {
        if (!isset($input[$field])) {
            continue;
        }
        
        $value = $input[$field];
        
        switch ($rule) {
            case 'text':
                $sanitized[$field] = sanitize_text_field($value);
                break;
            case 'textarea':
                $sanitized[$field] = sanitize_textarea_field($value);
                break;
            case 'email':
                $sanitized[$field] = sanitize_email($value);
                break;
            case 'phone':
                $sanitized[$field] = hb_sanitize_phone($value);
                break;
            case 'currency':
                $sanitized[$field] = hb_sanitize_currency($value);
                break;
            case 'int':
                $sanitized[$field] = intval($value);
                break;
            case 'float':
                $sanitized[$field] = floatval($value);
                break;
            case 'date':
                $sanitized[$field] = sanitize_text_field($value);
                break;
            default:
                $sanitized[$field] = sanitize_text_field($value);
        }
    }
    
    return $sanitized;
}

/**
 * Validate required fields
 */
function hb_validate_required_fields($data, $required_fields) {
    $missing = array();
    
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $missing[] = $field;
        }
    }
    
    return empty($missing) ? true : $missing;
}

/**
 * Generate secure nonce field
 */
function hb_nonce_field($action = 'husfelag_ajax_nonce', $name = 'nonce') {
    return wp_nonce_field($action, $name, true, false);
}

/**
 * Escape output fyrir tables
 */
function hb_escape_table_data($data) {
    if (is_array($data)) {
        return array_map('esc_html', $data);
    }
    return esc_html($data);
}
