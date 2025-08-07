<?php
/**
 * Bank API Client fyrir íslenska banka - PHP 8+ útgáfa
 * Styður PSD2 Account Information Services
 */

if (!defined('ABSPATH')) {
    exit;
}

enum BankType: string {
    case ISLANDSBANKI = 'islandsbanki';
    case ARION = 'arionbanki';
    case LANDSBANKINN = 'landsbankinn';
    
    public function getDisplayName(): string {
        return match($this) {
            self::ISLANDSBANKI => 'Íslandsbanki',
            self::ARION => 'Arion banki',
            self::LANDSBANKINN => 'Landsbankinn'
        };
    }
}

readonly class BankConfig {
    public function __construct(
        public string $name,
        public string $base_url,
        public string $client_id,
        public string $client_secret,
        public string $scope = 'AIS',
        public string $country = 'IS'
    ) {}
}

class TransactionCategory {
    public const MONTHLY_FEES = 'Mánaðargjöld';
    public const ELECTRICITY = 'Rafmagn';
    public const HEATING = 'Hiti';
    public const WATER = 'Vatn';
    public const INSURANCE = 'Tryggingar';
    public const MAINTENANCE = 'Viðhald';
    public const OTHER = 'Annað';
}

class HB_Bank_API_Client {
    
    private BankConfig $bank_config;
    private ?string $access_token = null;
    private ?string $consent_id = null;
    
    private array $bank_endpoints = [
        BankType::ISLANDSBANKI->value => [
            'name' => 'Íslandsbanki',
            'base_url' => 'https://api.islandsbanki.is/psd2/v1',
        ],
        BankType::ARION->value => [
            'name' => 'Arion banki',
            'base_url' => 'https://api.arionbanki.is/psd2/v1',
        ],
        BankType::LANDSBANKINN->value => [
            'name' => 'Landsbankinn',
            'base_url' => 'https://api.landsbankinn.is/psd2/v1',
        ]
    ];
    
    public function __construct(string|BankType $bank_code) {
        $bank_key = $bank_code instanceof BankType ? $bank_code->value : $bank_code;
        
        if (!array_key_exists($bank_key, $this->bank_endpoints)) {
            throw new InvalidArgumentException("Banki ekki studdur: {$bank_key}");
        }
        
        $endpoint = $this->bank_endpoints[$bank_key];
        $client_id = get_option("hb_{$bank_key}_client_id", '');
        $client_secret = get_option("hb_{$bank_key}_client_secret", '');
        
        if (empty($client_id) || empty($client_secret)) {
            throw new RuntimeException("API stillingar vantar fyrir {$endpoint['name']}");
        }
        
        $this->bank_config = new BankConfig(
            name: $endpoint['name'],
            base_url: $endpoint['base_url'],
            client_id: $client_id,
            client_secret: $client_secret
        );
    }
    
    /**
     * Byrja OAuth2 flow - Client Credentials Grant
     */
    public function getClientCredentialsToken(): array {
        $url = $this->bank_config->base_url . '/oauth2/token';
        
        $body = [
            'grant_type' => 'client_credentials',
            'scope' => $this->bank_config->scope,
            'client_id' => $this->bank_config->client_id
        ];
        
        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json'
            ],
            'body' => http_build_query($body),
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            throw new Exception("Villa við að tengjast banka: {$response->get_error_message()}");
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true, 512, JSON_THROW_ON_ERROR);
        
        if (!isset($body['access_token'])) {
            throw new Exception('Gat ekki fengið access token frá banka');
        }
        
        $this->access_token = $body['access_token'];
        return $body;
    }
    
    /**
     * Búa til consent fyrir Account Information
     */
    public function createConsent(): array {
        $this->access_token ??= $this->getClientCredentialsToken()['access_token'];
        
        $url = $this->bank_config->base_url . '/consents';
        
        $body = [
            'access' => 'ALL_ACCOUNTS',
            'recurringIndicator' => true,
            'validUntil' => date('Y-m-d', strtotime('+90 days')),
            'frequencyPerDay' => 4
        ];
        
        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => "Bearer {$this->access_token}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-IBM-Client-Id' => $this->bank_config->client_id,
                'TPP-Request-ID' => wp_generate_uuid4(),
                'TPP-Transaction-ID' => wp_generate_uuid4(),
                'Country' => $this->bank_config->country
            ],
            'body' => json_encode($body, JSON_THROW_ON_ERROR),
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            throw new Exception("Villa við að búa til consent: {$response->get_error_message()}");
        }
        
        $response_body = json_decode(wp_remote_retrieve_body($response), true, 512, JSON_THROW_ON_ERROR);
        
        if (!isset($response_body['consentId'])) {
            throw new Exception('Gat ekki búið til consent');
        }
        
        $this->consent_id = $response_body['consentId'];
        update_option('hb_bank_consent_id', $this->consent_id);
        
        return $response_body;
    }
    
    /**
     * Sækja lista yfir reikninga
     */
    public function getAccounts(): array {
        if (!$this->access_token) {
            throw new LogicException('Access token vantar');
        }
        
        $url = $this->bank_config->base_url . '/accounts';
        
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => "Bearer {$this->access_token}",
                'Accept' => 'application/json',
                'X-IBM-Client-Id' => $this->bank_config->client_id,
                'TPP-Request-ID' => wp_generate_uuid4(),
                'TPP-Transaction-ID' => wp_generate_uuid4(),
                'Country' => $this->bank_config->country
            ],
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            throw new Exception("Villa við að sækja reikninga: {$response->get_error_message()}");
        }
        
        return json_decode(wp_remote_retrieve_body($response), true, 512, JSON_THROW_ON_ERROR);
    }
    
    /**
     * Sækja bankafærslur fyrir reikning
     */
    public function getTransactions(
        string $account_id, 
        ?DateTimeInterface $date_from = null, 
        ?DateTimeInterface $date_to = null
    ): array {
        if (!$this->access_token) {
            throw new LogicException('Access token vantar');
        }
        
        $url = $this->bank_config->base_url . "/accounts/{$account_id}/transactions";
        
        $query_params = [];
        if ($date_from) {
            $query_params['dateFrom'] = $date_from->format('Y-m-d');
        }
        if ($date_to) {
            $query_params['dateTo'] = $date_to->format('Y-m-d');
        }
        
        if ($query_params) {
            $url .= '?' . http_build_query($query_params);
        }
        
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => "Bearer {$this->access_token}",
                'Accept' => 'application/json',
                'X-IBM-Client-Id' => $this->bank_config->client_id,
                'TPP-Request-ID' => wp_generate_uuid4(),
                'TPP-Transaction-ID' => wp_generate_uuid4(),
                'Country' => $this->bank_config->country
            ],
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            throw new Exception("Villa við að sækja bankafærslur: {$response->get_error_message()}");
        }
        
        return json_decode(wp_remote_retrieve_body($response), true, 512, JSON_THROW_ON_ERROR);
    }
    
    /**
     * Sækja stöðu reiknings
     */
    public function getAccountBalance(string $account_id): array {
        if (!$this->access_token) {
            throw new LogicException('Access token vantar');
        }
        
        $url = $this->bank_config->base_url . "/accounts/{$account_id}/balances";
        
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => "Bearer {$this->access_token}",
                'Accept' => 'application/json',
                'X-IBM-Client-Id' => $this->bank_config->client_id,
                'TPP-Request-ID' => wp_generate_uuid4(),
                'TPP-Transaction-ID' => wp_generate_uuid4(),
                'Country' => $this->bank_config->country
            ],
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            throw new Exception("Villa við að sækja stöðu: {$response->get_error_message()}");
        }
        
        return json_decode(wp_remote_retrieve_body($response), true, 512, JSON_THROW_ON_ERROR);
    }
    
    /**
     * Umbreyta bankafærslu í húsfélagsformat með PHP 8+ eiginleikum
     */
    public function convertTransactionToHbFormat(array $transaction): array {
        $amount = abs(floatval($transaction['transactionAmount']['amount']));
        $is_debit = match(true) {
            isset($transaction['debitCreditIndicator']) => $transaction['debitCreditIndicator'] === 'DBIT',
            default => $transaction['transactionAmount']['amount'] < 0
        };
        
        return [
            'dagsetning' => date('Y-m-d', strtotime($transaction['bookingDate'] ?? $transaction['valueDate'])),
            'lysing' => $this->cleanTransactionDescription(
                $transaction['remittanceInformation'] ??
                $transaction['additionalInformation'] ??
                'Bankafærsla'
            ),
            'upphad' => $amount,
            'kvittun' => $transaction['transactionId'] ?? '',
            'bank_reference' => $transaction['transactionId'] ?? ''
        ];
    }
    
    /**
     * Hreinsa lýsingu bankafærslu með PHP 8+ string functions
     */
    private function cleanTransactionDescription(string $description): string {
        return str($description)
            ->trim()
            ->replaceMatches('/\s+/', ' ')
            ->limit(200)
            ->toString();
    }
    
    /**
     * Flokka bankafærslu sjálfkrafa með match expression
     */
    private function categorizeTransaction(array $transaction): string {
        $description = strtolower($transaction['remittanceInformation'] ?? '');
        
        return match(true) {
            str_contains($description, 'húsfélag') || str_contains($description, 'mánaðargjald') 
                => TransactionCategory::MONTHLY_FEES,
            str_contains($description, 'rafmagn') 
                => TransactionCategory::ELECTRICITY,
            str_contains($description, 'hiti') || str_contains($description, 'hitaveita') 
                => TransactionCategory::HEATING,
            str_contains($description, 'vatn') 
                => TransactionCategory::WATER,
            str_contains($description, 'trygging') 
                => TransactionCategory::INSURANCE,
            str_contains($description, 'viðhald') || str_contains($description, 'lagfæring') 
                => TransactionCategory::MAINTENANCE,
            default => TransactionCategory::OTHER
        };
    }
    
    /**
     * Test API tengingu með improved error handling
     */
    public function testConnection(): array {
        try {
            $token = $this->getClientCredentialsToken();
            return [
                'success' => true,
                'message' => "Tenging við {$this->bank_config->name} tókst",
                'token_expires' => $token['expires_in'] ?? 3600,
                'bank_name' => $this->bank_config->name
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => "Villa við tengingu: {$e->getMessage()}",
                'error_type' => $e::class
            ];
        }
    }
    
    /**
     * Bulk import transactions með PHP 8+ features
     */
    public function bulkImportTransactions(
        string $account_id, 
        DateTimeInterface $date_from, 
        ?DateTimeInterface $date_to = null
    ): array {
        $transactions = $this->getTransactions($account_id, $date_from, $date_to);
        
        if (!isset($transactions['transactions']) || !is_array($transactions['transactions'])) {
            return ['imported' => 0, 'errors' => [], 'skipped' => 0];
        }
        
        global $wpdb;
        $table_faerslur = $wpdb->prefix . 'hb_faerslur';
        
        $imported = 0;
        $skipped = 0;
        $errors = [];
        
        foreach ($transactions['transactions'] as $transaction) {
            try {
                $hb_transaction = $this->convertTransactionToHbFormat($transaction);
                
                // Check if transaction already exists
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_faerslur WHERE kvittun = %s AND dagsetning = %s",
                    $hb_transaction['kvittun'],
                    $hb_transaction['dagsetning']
                ));
                
                if ($exists) {
                    $skipped++;
                    continue;
                }
                
                $result = $wpdb->insert(
                    $table_faerslur,
                    [
                        ...$hb_transaction,
                        'lysing' => $hb_transaction['lysing'] . ' (API Import)',
                        'notandi_id' => get_current_user_id() ?: 1
                    ],
                    ['%s', '%s', '%f', '%s', '%s', '%s', '%s', '%d']
                );
                
                if ($result) {
                    $imported++;
                } else {
                    $errors[] = "Gat ekki vistað: {$hb_transaction['lysing']}";
                }
                
            } catch (Throwable $e) {
                $errors[] = "Villa við færslu: {$e->getMessage()}";
            }
        }
        
        return compact('imported', 'skipped', 'errors');
    }
}

/**
 * Helper function fyrir UUID generation með PHP 8+ syntax
 */
if (!function_exists('wp_generate_uuid4')) {
    function wp_generate_uuid4(): string {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            ...array_map(fn() => mt_rand(0, 0xffff), range(1, 8))
        );
    }
}
