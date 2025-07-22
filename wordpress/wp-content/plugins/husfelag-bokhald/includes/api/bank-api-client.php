<?php
/**
 * Bank API Client fyrir íslenska banka
 * Styður PSD2 Account Information Services
 */

if (!defined('ABSPATH')) {
    exit;
}

class HB_Bank_API_Client {
    
    private $bank_config;
    private $access_token;
    private $consent_id;
    
    /**
     * Stillingar fyrir íslenska banka
     */
    private $bank_configs = array(
        'islandsbanki' => array(
            'name' => 'Íslandsbanki',
            'base_url' => 'https://api.islandsbanki.is/psd2/v1', // Dæmi URL
            'client_id' => '', // Þarf að fá frá bankanum
            'client_secret' => '', // Þarf að fá frá bankanum
            'scope' => 'AIS', // Account Information Services
            'country' => 'IS'
        ),
        'arionbanki' => array(
            'name' => 'Arion banki',
            'base_url' => 'https://api.arionbanki.is/psd2/v1', // Dæmi URL
            'client_id' => '',
            'client_secret' => '',
            'scope' => 'AIS',
            'country' => 'IS'
        ),
        'landsbankinn' => array(
            'name' => 'Landsbankinn',
            'base_url' => 'https://api.landsbankinn.is/psd2/v1', // Dæmi URL
            'client_id' => '',
            'client_secret' => '',
            'scope' => 'AIS',
            'country' => 'IS'
        )
    );
    
    public function __construct($bank_code) {
        if (!isset($this->bank_configs[$bank_code])) {
            throw new Exception('Banki ekki studdur: ' . $bank_code);
        }
        
        $this->bank_config = $this->bank_configs[$bank_code];
        
        // Sækja stillingar úr WordPress options
        $this->bank_config['client_id'] = get_option('hb_' . $bank_code . '_client_id', '');
        $this->bank_config['client_secret'] = get_option('hb_' . $bank_code . '_client_secret', '');
        
        if (empty($this->bank_config['client_id']) || empty($this->bank_config['client_secret'])) {
            throw new Exception('API stillingar vantar fyrir ' . $this->bank_config['name']);
        }
    }
    
    /**
     * Byrja OAuth2 flow - Client Credentials Grant
     */
    public function get_client_credentials_token() {
        $url = $this->bank_config['base_url'] . '/oauth2/token';
        
        $body = array(
            'grant_type' => 'client_credentials',
            'scope' => $this->bank_config['scope'],
            'client_id' => $this->bank_config['client_id']
        );
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json'
            ),
            'body' => http_build_query($body),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Villa við að tengjast banka: ' . $response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!isset($body['access_token'])) {
            throw new Exception('Gat ekki fengið access token frá banka');
        }
        
        $this->access_token = $body['access_token'];
        return $body;
    }
    
    /**
     * Búa til consent fyrir Account Information
     */
    public function create_consent() {
        if (empty($this->access_token)) {
            $this->get_client_credentials_token();
        }
        
        $url = $this->bank_config['base_url'] . '/consents';
        
        $body = array(
            'access' => 'ALL_ACCOUNTS',
            'recurringIndicator' => true,
            'validUntil' => date('Y-m-d', strtotime('+90 days')),
            'frequencyPerDay' => 4
        );
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-IBM-Client-Id' => $this->bank_config['client_id'],
                'TPP-Request-ID' => wp_generate_uuid4(),
                'TPP-Transaction-ID' => wp_generate_uuid4(),
                'Country' => $this->bank_config['country']
            ),
            'body' => json_encode($body),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Villa við að búa til consent: ' . $response->get_error_message());
        }
        
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!isset($response_body['consentId'])) {
            throw new Exception('Gat ekki búið til consent');
        }
        
        $this->consent_id = $response_body['consentId'];
        
        // Vista consent ID
        update_option('hb_bank_consent_id', $this->consent_id);
        
        return $response_body;
    }
    
    /**
     * Sækja lista yfir reikninga
     */
    public function get_accounts() {
        if (empty($this->access_token)) {
            throw new Exception('Access token vantar');
        }
        
        $url = $this->bank_config['base_url'] . '/accounts';
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Accept' => 'application/json',
                'X-IBM-Client-Id' => $this->bank_config['client_id'],
                'TPP-Request-ID' => wp_generate_uuid4(),
                'TPP-Transaction-ID' => wp_generate_uuid4(),
                'Country' => $this->bank_config['country']
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Villa við að sækja reikninga: ' . $response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body;
    }
    
    /**
     * Sækja bankafærslur fyrir reikning
     */
    public function get_transactions($account_id, $date_from = null, $date_to = null) {
        if (empty($this->access_token)) {
            throw new Exception('Access token vantar');
        }
        
        $url = $this->bank_config['base_url'] . '/accounts/' . $account_id . '/transactions';
        
        // Bæta við dagsetningar ef gefnar
        $query_params = array();
        if ($date_from) {
            $query_params['dateFrom'] = date('Y-m-d', strtotime($date_from));
        }
        if ($date_to) {
            $query_params['dateTo'] = date('Y-m-d', strtotime($date_to));
        }
        
        if (!empty($query_params)) {
            $url .= '?' . http_build_query($query_params);
        }
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Accept' => 'application/json',
                'X-IBM-Client-Id' => $this->bank_config['client_id'],
                'TPP-Request-ID' => wp_generate_uuid4(),
                'TPP-Transaction-ID' => wp_generate_uuid4(),
                'Country' => $this->bank_config['country']
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Villa við að sækja bankafærslur: ' . $response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body;
    }
    
    /**
     * Sækja stöðu reiknings
     */
    public function get_account_balance($account_id) {
        if (empty($this->access_token)) {
            throw new Exception('Access token vantar');
        }
        
        $url = $this->bank_config['base_url'] . '/accounts/' . $account_id . '/balances';
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Accept' => 'application/json',
                'X-IBM-Client-Id' => $this->bank_config['client_id'],
                'TPP-Request-ID' => wp_generate_uuid4(),
                'TPP-Transaction-ID' => wp_generate_uuid4(),
                'Country' => $this->bank_config['country']
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Villa við að sækja stöðu: ' . $response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body;
    }
    
    /**
     * Umbreyta bankafærslu í húsfélagsformat
     */
    public function convert_transaction_to_hb_format($transaction) {
        // Þetta fer eftir því hvernig bankinn skilar gögnunum
        // Hér er dæmi um almennt format
        
        $amount = abs(floatval($transaction['transactionAmount']['amount']));
        $is_debit = isset($transaction['debitCreditIndicator']) ? 
                   ($transaction['debitCreditIndicator'] === 'DBIT') : 
                   ($transaction['transactionAmount']['amount'] < 0);
        
        return array(
            'dagsetning' => date('Y-m-d', strtotime($transaction['bookingDate'] ?? $transaction['valueDate'])),
            'lysing' => $this->clean_transaction_description($transaction['remittanceInformation'] ?? $transaction['additionalInformation'] ?? 'Bankafærsla'),
            'upphad' => $amount,
            'tegund' => $is_debit ? 'gjold' : 'tekjur',
            'flokkur' => $this->categorize_transaction($transaction),
            'kvittun' => $transaction['transactionId'] ?? '',
            'bank_reference' => $transaction['transactionId'] ?? ''
        );
    }
    
    /**
     * Hreinsa lýsingu bankafærslu
     */
    private function clean_transaction_description($description) {
        // Fjarlægja óþarfa stafi og stytta
        $description = trim($description);
        $description = preg_replace('/\s+/', ' ', $description);
        return substr($description, 0, 200);
    }
    
    /**
     * Flokka bankafærslu sjálfkrafa
     */
    private function categorize_transaction($transaction) {
        $description = strtolower($transaction['remittanceInformation'] ?? '');
        
        // Einfaldar reglur fyrir flokkun
        if (strpos($description, 'húsfélag') !== false || strpos($description, 'mánaðargjald') !== false) {
            return 'Mánaðargjöld';
        } elseif (strpos($description, 'rafmagn') !== false) {
            return 'Rafmagn';
        } elseif (strpos($description, 'hiti') !== false || strpos($description, 'hitaveita') !== false) {
            return 'Hiti';
        } elseif (strpos($description, 'vatn') !== false) {
            return 'Vatn';
        } elseif (strpos($description, 'trygging') !== false) {
            return 'Tryggingar';
        } elseif (strpos($description, 'viðhald') !== false || strpos($description, 'lagfæring') !== false) {
            return 'Viðhald';
        }
        
        return 'Annað';
    }
    
    /**
     * Test API tengingu
     */
    public function test_connection() {
        try {
            $token = $this->get_client_credentials_token();
            return array(
                'success' => true,
                'message' => 'Tenging við ' . $this->bank_config['name'] . ' tókst',
                'token_expires' => $token['expires_in'] ?? 3600
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Villa við tengingu: ' . $e->getMessage()
            );
        }
    }
}

/**
 * Helper function til að búa til UUID
 */
if (!function_exists('wp_generate_uuid4')) {
    function wp_generate_uuid4() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
