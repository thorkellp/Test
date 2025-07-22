<?php
if (!defined('ABSPATH')) {
    exit;
}

// Öryggisathugun
if (!current_user_can('manage_options')) {
    wp_die(__('Þú hefur ekki heimild til þessa.', 'husfelag-bokhald'));
}

// Vinna úr stillingum
if (isset($_POST['hb_save_bank_settings']) && wp_verify_nonce($_POST['hb_nonce'], 'hb_save_bank_settings') && current_user_can('manage_options')) {
    $bank = sanitize_text_field($_POST['selected_bank']);
    $client_id = sanitize_text_field($_POST['client_id']);
    $client_secret = sanitize_text_field($_POST['client_secret']);
    $account_id = sanitize_text_field($_POST['account_id']);
    
    update_option('hb_selected_bank', $bank);
    update_option('hb_' . $bank . '_client_id', $client_id);
    update_option('hb_' . $bank . '_client_secret', $client_secret);
    update_option('hb_bank_account_id', $account_id);
    
    echo '<div class="notice notice-success"><p>API stillingar vistaðar!</p></div>';
}

// Test API tengingu
if (isset($_POST['hb_test_connection']) && wp_verify_nonce($_POST['hb_nonce'], 'hb_test_connection') && current_user_can('manage_options')) {
    $selected_bank = get_option('hb_selected_bank', '');
    
    if (!empty($selected_bank)) {
        try {
            require_once HB_PLUGIN_PATH . 'includes/api/bank-api-client.php';
            $api_client = new HB_Bank_API_Client($selected_bank);
            $result = $api_client->test_connection();
            
            if ($result['success']) {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
            }
        } catch (Exception $e) {
            echo '<div class="notice notice-error"><p>Villa: ' . esc_html($e->getMessage()) . '</p></div>';
        }
    }
}

// Sync bankafærslur
if (isset($_POST['hb_sync_transactions']) && wp_verify_nonce($_POST['hb_nonce'], 'hb_sync_transactions') && current_user_can('manage_options')) {
    $selected_bank = get_option('hb_selected_bank', '');
    $account_id = get_option('hb_bank_account_id', '');
    
    if (!empty($selected_bank) && !empty($account_id)) {
        try {
            require_once HB_PLUGIN_PATH . 'includes/api/bank-api-client.php';
            $api_client = new HB_Bank_API_Client($selected_bank);
            
            // Sækja færslur síðustu 30 daga
            $date_from = date('Y-m-d', strtotime('-30 days'));
            $transactions = $api_client->get_transactions($account_id, $date_from);
            
            if (isset($transactions['transactions']) && is_array($transactions['transactions'])) {
                global $wpdb;
                $table_faerslur = $wpdb->prefix . 'hb_faerslur';
                
                $imported_count = 0;
                foreach ($transactions['transactions'] as $transaction) {
                    $hb_transaction = $api_client->convert_transaction_to_hb_format($transaction);
                    
                    // Athuga hvort færsla sé þegar til
                    $exists = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_faerslur WHERE kvittun = %s AND dagsetning = %s",
                        $hb_transaction['kvittun'],
                        $hb_transaction['dagsetning']
                    ));
                    
                    if (!$exists) {
                        $result = $wpdb->insert(
                            $table_faerslur,
                            array(
                                'dagsetning' => $hb_transaction['dagsetning'],
                                'lysing' => $hb_transaction['lysing'] . ' (Sjálfvirk innflutningur)',
                                'upphad' => $hb_transaction['upphad'],
                                'tegund' => $hb_transaction['tegund'],
                                'flokkur' => $hb_transaction['flokkur'],
                                'kvittun' => $hb_transaction['kvittun'],
                                'notandi_id' => get_current_user_id()
                            ),
                            array('%s', '%s', '%f', '%s', '%s', '%s', '%d')
                        );
                        
                        if ($result) {
                            $imported_count++;
                        }
                    }
                }
                
                echo '<div class="notice notice-success"><p>' . $imported_count . ' nýjar bankafærslur fluttar inn!</p></div>';
            } else {
                echo '<div class="notice notice-warning"><p>Engar nýjar bankafærslur fundust.</p></div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="notice notice-error"><p>Villa við að sækja bankafærslur: ' . esc_html($e->getMessage()) . '</p></div>';
        }
    }
}

// Núverandi stillingar
$selected_bank = get_option('hb_selected_bank', '');
$client_id = get_option('hb_' . $selected_bank . '_client_id', '');
$client_secret = get_option('hb_' . $selected_bank . '_client_secret', '');
$account_id = get_option('hb_bank_account_id', '');

$available_banks = array(
    'islandsbanki' => 'Íslandsbanki',
    'arionbanki' => 'Arion banki',
    'landsbankinn' => 'Landsbankinn'
);
?>

<div class="wrap">
    <h1>Banka API Stillingar</h1>
    
    <div class="hb-form-container">
        <h2>API Tenging við banka</h2>
        
        <div class="notice notice-info">
            <p><strong>Athugið:</strong> Til að tengja við banka þarftu að:</p>
            <ul>
                <li>1. Skrá þig sem Third Party Provider (TPP) hjá bankanum</li>
                <li>2. Fá Client ID og Client Secret frá bankanum</li>
                <li>3. Fá samþykki frá húsfélaginu fyrir aðgangi að bankareikningi</li>
                <li>4. Fylgja PSD2 reglugerð um opna bankaþjónustu</li>
            </ul>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('hb_save_bank_settings', 'hb_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th><label for="selected_bank">Banki</label></th>
                    <td>
                        <select id="selected_bank" name="selected_bank" required>
                            <option value="">Veldu banka</option>
                            <?php foreach ($available_banks as $code => $name): ?>
                                <option value="<?php echo esc_attr($code); ?>" <?php selected($selected_bank, $code); ?>>
                                    <?php echo esc_html($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="client_id">Client ID</label></th>
                    <td>
                        <input type="text" id="client_id" name="client_id" 
                               value="<?php echo esc_attr($client_id); ?>" 
                               placeholder="Fengið frá bankanum" required />
                        <p class="description">API Client ID sem þú færð frá bankanum</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="client_secret">Client Secret</label></th>
                    <td>
                        <input type="password" id="client_secret" name="client_secret" 
                               value="<?php echo esc_attr($client_secret); ?>" 
                               placeholder="Fengið frá bankanum" required />
                        <p class="description">API Client Secret sem þú færð frá bankanum</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="account_id">Reikningsnúmer</label></th>
                    <td>
                        <input type="text" id="account_id" name="account_id" 
                               value="<?php echo esc_attr($account_id); ?>" 
                               placeholder="t.d. 0101-01-123456" />
                        <p class="description">Bankareikningur húsfélagsins</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="hb_save_bank_settings" class="button-primary" value="Vista stillingar" />
            </p>
        </form>
    </div>
    
    <?php if (!empty($selected_bank)): ?>
    <div class="hb-form-container">
        <h2>Prófun og samstilling</h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('hb_test_connection', 'hb_nonce'); ?>
            
            <p>
                <input type="submit" name="hb_test_connection" class="button" value="Prófa API tengingu" />
                <span class="description">Prófar hvort tenging við banka virki</span>
            </p>
        </form>
        
        <form method="post" action="" style="margin-top: 20px;">
            <?php wp_nonce_field('hb_sync_transactions', 'hb_nonce'); ?>
            
            <p>
                <input type="submit" name="hb_sync_transactions" class="button button-secondary" value="Sækja bankafærslur" />
                <span class="description">Sækir nýjar bankafærslur síðustu 30 daga</span>
            </p>
        </form>
        
        <div class="notice notice-warning">
            <p><strong>Mikilvægt:</strong></p>
            <ul>
                <li>Bankafærslur eru flokkaðar sjálfkrafa en þarf að fara yfir þær</li>
                <li>Aðeins nýjar færslur eru fluttar inn (engar tvítekningar)</li>
                <li>Allar innfluttar færslur eru merktar sem "Sjálfvirk innflutningur"</li>
            </ul>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="hb-form-container">
        <h2>Upplýsingar um PSD2 og Open Banking</h2>
        
        <h3>Hvað er PSD2?</h3>
        <p>PSD2 (Payment Services Directive 2) er EU reglugerð sem krefst þess að bankar bjóði upp á opna API fyrir:</p>
        <ul>
            <li><strong>Account Information Services (AIS)</strong> - Aðgangur að reikningsupplýsingum</li>
            <li><strong>Payment Initiation Services (PIS)</strong> - Greiðslur í gegnum þriðja aðila</li>
            <li><strong>Confirmation of Funds (COF)</strong> - Staðfesting á fjármagni</li>
        </ul>
        
        <h3>Hvernig á að byrja?</h3>
        <ol>
            <li><strong>Hafa samband við bankann þinn</strong> - Spurðu um PSD2 API aðgang</li>
            <li><strong>Skrá þig sem TPP</strong> - Third Party Provider hjá Fjármálaeftirlitinu</li>
            <li><strong>Fá API lykla</strong> - Client ID og Secret frá bankanum</li>
            <li><strong>Setja upp tengingu</strong> - Nota þessa síðu til að stilla API</li>
        </ol>
        
        <h3>Kostir API tengingar:</h3>
        <ul>
            <li>✅ <strong>Sjálfvirk innflutningur</strong> bankafærslna</li>
            <li>✅ <strong>Rauntíma uppfærslur</strong> á stöðu reiknings</li>
            <li>✅ <strong>Minni handvirk vinna</strong> við bókhald</li>
            <li>✅ <strong>Nákvæmari skýrslur</strong> með nýjustu gögnum</li>
            <li>✅ <strong>Örugg tenging</strong> í gegnum banka API</li>
        </ul>
    </div>
</div>

<style>
.hb-form-container ul {
    margin-left: 20px;
}

.hb-form-container ol {
    margin-left: 20px;
}

.notice ul, .notice ol {
    margin: 10px 0 10px 20px;
}
</style>
