<?php
/**
 * Sjálfvirk samstilling við banka API
 * Keyrir daglega til að sækja nýjar bankafærslur
 */

if (!defined('ABSPATH')) {
    exit;
}

class HB_Bank_Sync_Scheduler {
    
    public function __construct() {
        // Bæta við cron hook
        add_action('hb_daily_bank_sync', array($this, 'sync_bank_transactions'));
        
        // Skrá cron event við activation
        register_activation_hook(__FILE__, array($this, 'schedule_sync'));
        register_deactivation_hook(__FILE__, array($this, 'unschedule_sync'));
        
        // Bæta við admin notice ef sync mistókst
        add_action('admin_notices', array($this, 'sync_error_notice'));
    }
    
    /**
     * Skrá daglega samstillingu
     */
    public function schedule_sync() {
        if (!wp_next_scheduled('hb_daily_bank_sync')) {
            wp_schedule_event(time(), 'daily', 'hb_daily_bank_sync');
        }
    }
    
    /**
     * Afskrá samstillingu
     */
    public function unschedule_sync() {
        $timestamp = wp_next_scheduled('hb_daily_bank_sync');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'hb_daily_bank_sync');
        }
    }
    
    /**
     * Keyra sjálfvirka samstillingu
     */
    public function sync_bank_transactions() {
        $selected_bank = get_option('hb_selected_bank', '');
        $account_id = get_option('hb_bank_account_id', '');
        $bank_account = intval(get_option('hb_sync_bank_account'));
        $income_account = intval(get_option('hb_sync_income_account'));
        $expense_account = intval(get_option('hb_sync_expense_account'));
        
        // Athuga hvort API sé stillt
        if (empty($selected_bank) || empty($account_id) || !$bank_account || !$income_account || !$expense_account) {
            $this->log_sync_error('API stillingar vantar');
            return false;
        }
        
        try {
            require_once HB_PLUGIN_PATH . 'includes/api/bank-api-client.php';
            $api_client = new HB_Bank_API_Client($selected_bank);
            
            // Sækja færslur síðustu 7 daga
            $date_from = date('Y-m-d', strtotime('-7 days'));
            $transactions = $api_client->get_transactions($account_id, $date_from);
            
            if (isset($transactions['transactions']) && is_array($transactions['transactions'])) {
                global $wpdb;
                $table_faerslur = $wpdb->prefix . 'hb_faerslur';
                
                $imported_count = 0;
                $errors = array();
                
                foreach ($transactions['transactions'] as $transaction) {
                    try {
                        $hb_transaction = $api_client->convert_transaction_to_hb_format($transaction);
                        
                        // Athuga hvort færsla sé þegar til
                        $exists = $wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(*) FROM $table_faerslur WHERE kvittun = %s AND dagsetning = %s",
                            $hb_transaction['kvittun'],
                            $hb_transaction['dagsetning']
                        ));
                        
                        if (!$exists) {
                            $upphad = $hb_transaction['upphad'];
                            if ($upphad >= 0) {
                                $debet = $bank_account;
                                $kredit = $income_account;
                            } else {
                                $debet = $expense_account;
                                $kredit = $bank_account;
                                $upphad = abs($upphad);
                            }

                            $result = $wpdb->insert(
                                $table_faerslur,
                                array(
                                    'dagsetning' => $hb_transaction['dagsetning'],
                                    'lysing' => $hb_transaction['lysing'] . ' (Sjálfvirk samstilling)',
                                    'upphad' => $upphad,
                                    'debet_reikning_id' => $debet,
                                    'kredit_reikning_id' => $kredit,
                                    'kvittun' => $hb_transaction['kvittun']
                                ),
                                array('%s', '%s', '%f', '%d', '%d', '%s')
                            );
                            
                            if ($result) {
                                $imported_count++;
                            } else {
                                $errors[] = 'Gat ekki vistað færslu: ' . $hb_transaction['lysing'];
                            }
                        }
                    } catch (Exception $e) {
                        $errors[] = 'Villa við að vinna úr færslu: ' . $e->getMessage();
                    }
                }
                
                // Log niðurstöður
                $this->log_sync_result($imported_count, $errors);
                
                // Hreinsa gamla error log
                delete_option('hb_sync_error');
                
                return $imported_count;
                
            } else {
                $this->log_sync_result(0, array());
                return 0;
            }
            
        } catch (Exception $e) {
            $this->log_sync_error('API villa: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Skrá niðurstöður samstillingar
     */
    private function log_sync_result($imported_count, $errors = array()) {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'imported_count' => $imported_count,
            'errors' => $errors,
            'success' => empty($errors)
        );
        
        // Vista síðustu 10 sync logs
        $sync_log = get_option('hb_sync_log', array());
        array_unshift($sync_log, $log_entry);
        $sync_log = array_slice($sync_log, 0, 10);
        update_option('hb_sync_log', $sync_log);
        
        // Ef debug er virkt
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Húsfélags Bókhald Sync: {$imported_count} færslur fluttar inn. Villur: " . count($errors));
        }
    }
    
    /**
     * Skrá sync villu
     */
    private function log_sync_error($error_message) {
        update_option('hb_sync_error', array(
            'message' => $error_message,
            'timestamp' => current_time('mysql')
        ));
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Húsfélags Bókhald Sync Error: " . $error_message);
        }
    }
    
    /**
     * Sýna admin notice ef sync mistókst
     */
    public function sync_error_notice() {
        $error = get_option('hb_sync_error');
        if ($error && isset($error['message'])) {
            $screen = get_current_screen();
            if ($screen && strpos($screen->id, 'husfelag') !== false) {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p><strong>Banka API villa:</strong> ' . esc_html($error['message']) . '</p>';
                echo '<p>Tími: ' . esc_html($error['timestamp']) . '</p>';
                echo '</div>';
            }
        }
    }
    
    /**
     * Handvirk sync (fyrir testing)
     */
    public function manual_sync() {
        return $this->sync_bank_transactions();
    }
    
    /**
     * Sækja sync log
     */
    public function get_sync_log() {
        return get_option('hb_sync_log', array());
    }
    
    /**
     * Hreinsa sync log
     */
    public function clear_sync_log() {
        delete_option('hb_sync_log');
        delete_option('hb_sync_error');
    }
}

// Byrja scheduler
new HB_Bank_Sync_Scheduler();
