<?php
/**
 * Öryggis og validation functions - PHP 8+ útgáfa
 * Húsfélags Bókhald
 */

if (!defined('ABSPATH')) {
    exit;
}

enum InputType: string {
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case EMAIL = 'email';
    case PHONE = 'phone';
    case CURRENCY = 'currency';
    case INTEGER = 'int';
    case FLOAT = 'float';
    case DATE = 'date';
    case URL = 'url';
}

enum SecurityEvent: string {
    case INVALID_NONCE = 'invalid_nonce';
    case INSUFFICIENT_PERMISSIONS = 'insufficient_permissions';
    case RATE_LIMIT_EXCEEDED = 'rate_limit_exceeded';
    case SUSPICIOUS_ACTIVITY = 'suspicious_activity';
    case LOGIN_ATTEMPT = 'login_attempt';
    case DATA_BREACH_ATTEMPT = 'data_breach_attempt';
}

readonly class ValidationResult {
    public function __construct(
        public bool $isValid,
        public array $errors = [],
        public mixed $sanitizedData = null
    ) {}
    
    public function hasErrors(): bool {
        return !empty($this->errors);
    }
}

class HB_Security_Functions {
    
    private const VALID_CATEGORIES = [
        'tekjur' => ['Mánaðargjöld', 'Sérstök gjöld', 'Vextir', 'Annað'],
        'gjold' => ['Viðhald', 'Þrif', 'Tryggingar', 'Rafmagn', 'Hiti', 'Vatn', 'Umsýsla', 'Annað']
    ];
    
    /**
     * Validate íbúðanúmer með PHP 8+ regex
     */
    public static function validateApartmentNumber(string $number): bool {
        return (bool) preg_match('/^[A-Za-z0-9]{1,10}$/', $number);
    }
    
    /**
     * Validate upphæð með strict typing
     */
    public static function validateAmount(int|float|string $amount): bool {
        $numericAmount = is_numeric($amount) ? (float) $amount : null;
        return $numericAmount !== null && 
               $numericAmount >= 0 && 
               $numericAmount <= 999_999_999.99;
    }
    
    /**
     * Validate hlutdeild með range check
     */
    public static function validateOwnershipShare(float $share): bool {
        return $share > 0 && $share <= 1.0;
    }
    
    /**
     * Validate dagsetning með DateTime
     */
    public static function validateDate(string $date): bool {
        try {
            $dateTime = new DateTimeImmutable($date);
            return $dateTime->format('Y-m-d') === $date;
        } catch (Exception) {
            return false;
        }
    }
    
    /**
     * Validate flokkur með enum-like behavior
     */
    public static function validateCategory(string $category, string $type): bool {
        return isset(self::VALID_CATEGORIES[$type]) && 
               in_array($category, self::VALID_CATEGORIES[$type], true);
    }
    
    /**
     * Sanitize currency með modern PHP
     */
    public static function sanitizeCurrency(string $amount): string {
        $cleaned = preg_replace('/[^\d.,]/', '', $amount);
        $normalized = str_replace(',', '.', $cleaned);
        return number_format((float) $normalized, 2, '.', '');
    }
    
    /**
     * Format currency fyrir display
     */
    public static function formatCurrency(int|float $amount): string {
        return number_format($amount, 0, ',', '.') . ' kr.';
    }
    
    /**
     * Validate símanúmer með improved regex
     */
    public static function validatePhone(string $phone): bool {
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);
        return (bool) preg_match('/^[\+]?[\d]{7,15}$/', $cleaned);
    }
    
    /**
     * Sanitize símanúmer
     */
    public static function sanitizePhone(string $phone): string {
        return preg_replace('/[^\d\+\-\s\(\)]/', '', $phone);
    }
    
    /**
     * Check user permissions með caching
     */
    public static function userCanManage(): bool {
        static $can_manage = null;
        
        return $can_manage ??= current_user_can('manage_options') || 
                              current_user_can('manage_husfelag_bokhald');
    }
    
    /**
     * Log security events með structured data
     */
    public static function logSecurityEvent(
        SecurityEvent $event, 
        string $details = '', 
        array $context = []
    ): void {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        $user = wp_get_current_user();
        $logEntry = [
            'timestamp' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
            'event' => $event->value,
            'user_id' => $user->ID,
            'user_login' => $user->user_login,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details,
            'context' => $context
        ];
        
        error_log('Húsfélags Bókhald Security: ' . json_encode($logEntry, JSON_THROW_ON_ERROR));
    }
    
    /**
     * Rate limiting með Redis-style implementation
     */
    public static function checkRateLimit(
        string $action, 
        int $limit = 10, 
        int $windowSeconds = 300
    ): bool {
        $userId = get_current_user_id();
        $key = "hb_rate_limit_{$action}_{$userId}";
        
        $attempts = get_transient($key);
        if ($attempts === false) {
            set_transient($key, 1, $windowSeconds);
            return true;
        }
        
        if ($attempts >= $limit) {
            self::logSecurityEvent(
                SecurityEvent::RATE_LIMIT_EXCEEDED,
                "Action: {$action}, Attempts: {$attempts}",
                compact('action', 'limit', 'windowSeconds')
            );
            return false;
        }
        
        set_transient($key, $attempts + 1, $windowSeconds);
        return true;
    }
    
    /**
     * Enhanced CSRF verification
     */
    public static function verifyRequest(string $action = 'husfelag_ajax_nonce'): ValidationResult {
        $nonce = $_REQUEST['nonce'] ?? '';
        
        if (!wp_verify_nonce($nonce, $action)) {
            self::logSecurityEvent(
                SecurityEvent::INVALID_NONCE,
                "Action: {$action}",
                ['nonce' => $nonce, 'action' => $action]
            );
            return new ValidationResult(false, ['Invalid security token']);
        }
        
        if (!self::userCanManage()) {
            self::logSecurityEvent(
                SecurityEvent::INSUFFICIENT_PERMISSIONS,
                "Action: {$action}"
            );
            return new ValidationResult(false, ['Insufficient permissions']);
        }
        
        return new ValidationResult(true);
    }
    
    /**
     * Sanitize input array með PHP 8+ match
     */
    public static function sanitizeInputArray(array $input, array $rules): array {
        $sanitized = [];
        
        foreach ($rules as $field => $type) {
            if (!isset($input[$field])) {
                continue;
            }
            
            $value = $input[$field];
            $inputType = InputType::from($type);
            
            $sanitized[$field] = match($inputType) {
                InputType::TEXT => sanitize_text_field($value),
                InputType::TEXTAREA => sanitize_textarea_field($value),
                InputType::EMAIL => sanitize_email($value),
                InputType::PHONE => self::sanitizePhone($value),
                InputType::CURRENCY => self::sanitizeCurrency($value),
                InputType::INTEGER => (int) $value,
                InputType::FLOAT => (float) $value,
                InputType::DATE => sanitize_text_field($value),
                InputType::URL => esc_url_raw($value),
            };
        }
        
        return $sanitized;
    }
    
    /**
     * Validate required fields með detailed errors
     */
    public static function validateRequiredFields(array $data, array $requiredFields): ValidationResult {
        $missing = array_filter(
            $requiredFields, 
            fn($field) => empty($data[$field])
        );
        
        if (empty($missing)) {
            return new ValidationResult(true, [], $data);
        }
        
        $errors = array_map(
            fn($field) => "Reitur '{$field}' er nauðsynlegur",
            $missing
        );
        
        return new ValidationResult(false, $errors);
    }
    
    /**
     * Generate secure nonce field með HTML5
     */
    public static function nonceField(
        string $action = 'husfelag_ajax_nonce', 
        string $name = 'nonce'
    ): string {
        $nonce = wp_create_nonce($action);
        return sprintf(
            '<input type="hidden" name="%s" value="%s" data-security="nonce" />',
            esc_attr($name),
            esc_attr($nonce)
        );
    }
    
    /**
     * Escape output fyrir tables með null safety
     */
    public static function escapeTableData(mixed $data): string {
        return match(true) {
            is_null($data) => '',
            is_array($data) => esc_html(implode(', ', $data)),
            is_bool($data) => $data ? 'Já' : 'Nei',
            is_numeric($data) => esc_html((string) $data),
            default => esc_html((string) $data)
        };
    }
    
    /**
     * Advanced input validation með custom rules
     */
    public static function validateInput(mixed $value, string $type, array $options = []): ValidationResult {
        $inputType = InputType::from($type);
        
        $isValid = match($inputType) {
            InputType::TEXT => is_string($value) && strlen($value) <= ($options['max_length'] ?? 255),
            InputType::EMAIL => is_email($value),
            InputType::PHONE => self::validatePhone($value),
            InputType::CURRENCY => self::validateAmount($value),
            InputType::INTEGER => filter_var($value, FILTER_VALIDATE_INT) !== false,
            InputType::FLOAT => filter_var($value, FILTER_VALIDATE_FLOAT) !== false,
            InputType::DATE => self::validateDate($value),
            InputType::URL => filter_var($value, FILTER_VALIDATE_URL) !== false,
            default => true
        };
        
        if (!$isValid) {
            return new ValidationResult(
                false, 
                ["Ógilt gildi fyrir {$type}: " . self::escapeTableData($value)]
            );
        }
        
        // Additional custom validations
        if (isset($options['min_length']) && strlen($value) < $options['min_length']) {
            return new ValidationResult(false, ["Gildi þarf að vera að minnsta kosti {$options['min_length']} stafir"]);
        }
        
        if (isset($options['pattern']) && !preg_match($options['pattern'], $value)) {
            return new ValidationResult(false, ["Gildi passar ekki við krafið snið"]);
        }
        
        return new ValidationResult(true, [], $value);
    }
    
    /**
     * Bulk validation með detailed results
     */
    public static function validateBulkInput(array $data, array $rules): ValidationResult {
        $sanitized = [];
        $allErrors = [];
        
        foreach ($rules as $field => $config) {
            $type = $config['type'] ?? InputType::TEXT->value;
            $required = $config['required'] ?? false;
            $options = $config['options'] ?? [];
            
            if ($required && empty($data[$field])) {
                $allErrors[$field][] = "Reitur '{$field}' er nauðsynlegur";
                continue;
            }
            
            if (isset($data[$field])) {
                $result = self::validateInput($data[$field], $type, $options);
                if ($result->hasErrors()) {
                    $allErrors[$field] = $result->errors;
                } else {
                    $sanitized[$field] = $result->sanitizedData;
                }
            }
        }
        
        return new ValidationResult(
            empty($allErrors), 
            $allErrors, 
            $sanitized
        );
    }
}

/**
 * Global helper functions
 */
function hb_validate_apartment_number(string $number): bool {
    return HB_Security_Functions::validateApartmentNumber($number);
}

function hb_format_currency(int|float $amount): string {
    return HB_Security_Functions::formatCurrency($amount);
}

function hb_user_can_manage(): bool {
    return HB_Security_Functions::userCanManage();
}

function hb_log_security_event(SecurityEvent $event, string $details = ''): void {
    HB_Security_Functions::logSecurityEvent($event, $details);
}
