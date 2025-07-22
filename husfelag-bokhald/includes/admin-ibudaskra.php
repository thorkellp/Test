<?php
if (!defined('ABSPATH')) {
    exit;
}

// Öryggisathugun
if (!current_user_can('manage_options')) {
    wp_die(__('Þú hefur ekki heimild til þessa.', 'husfelag-bokhald'));
}

global $wpdb;
$table_ibuddir = $wpdb->prefix . 'hb_ibuddir';

// Vinna úr form með öryggisathugun
if (isset($_POST['hb_save_ibud']) && wp_verify_nonce($_POST['hb_nonce'], 'hb_save_ibud') && current_user_can('manage_options')) {
    $ibudanumer = sanitize_text_field($_POST['ibudanumer']);
    $eigandi = sanitize_text_field($_POST['eigandi']);
    $netfang = sanitize_email($_POST['netfang']);
    $simi = sanitize_text_field($_POST['simi']);
    $fermetrar = floatval($_POST['fermetrar']);
    $hlutdeild = floatval($_POST['hlutdeild']);
    $id = intval($_POST['ibud_id']);
    
    if ($id > 0) {
        // Uppfæra
        $wpdb->update(
            $table_ibuddir,
            array(
                'eigandi' => $eigandi,
                'netfang' => $netfang,
                'simi' => $simi,
                'fermetrar' => $fermetrar,
                'hlutdeild' => $hlutdeild
            ),
            array('id' => $id),
            array('%s', '%s', '%s', '%f', '%f'),
            array('%d')
        );
        echo '<div class="notice notice-success"><p>Íbúð uppfærð!</p></div>';
    } else {
        // Bæta við nýrri
        $result = $wpdb->insert(
            $table_ibuddir,
            array(
                'ibudanumer' => $ibudanumer,
                'eigandi' => $eigandi,
                'netfang' => $netfang,
                'simi' => $simi,
                'fermetrar' => $fermetrar,
                'hlutdeild' => $hlutdeild
            ),
            array('%s', '%s', '%s', '%s', '%f', '%f')
        );
        
        if ($result) {
            echo '<div class="notice notice-success"><p>Íbúð bætt við!</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Villa við að bæta við íbúð. Íbúðanúmer gæti verið þegar til.</p></div>';
        }
    }
}

// Eyða íbúð með öryggisathugun
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_ibud') && current_user_can('manage_options')) {
    $id = intval($_GET['id']);
    $wpdb->update(
        $table_ibuddir,
        array('virkur' => 0),
        array('id' => $id),
        array('%d'),
        array('%d')
    );
    echo '<div class="notice notice-success"><p>Íbúð eytt!</p></div>';
}

// Sækja íbúð til breytinga
$edit_ibud = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $edit_ibud = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_ibuddir WHERE id = %d", $id));
}

// Sækja allar íbúðir
$ibuddir = $wpdb->get_results("SELECT * FROM $table_ibuddir WHERE virkur = 1 ORDER BY ibudanumer");
?>

<div class="wrap">
    <h1>Íbúðaskrá</h1>
    
    <div class="hb-form-container">
        <h2><?php echo $edit_ibud ? 'Breyta íbúð' : 'Bæta við íbúð'; ?></h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('hb_save_ibud', 'hb_nonce'); ?>
            <input type="hidden" name="ibud_id" value="<?php echo $edit_ibud ? $edit_ibud->id : 0; ?>">
            
            <table class="form-table">
                <tr>
                    <th><label for="ibudanumer">Íbúðanúmer</label></th>
                    <td>
                        <input type="text" id="ibudanumer" name="ibudanumer" 
                               value="<?php echo $edit_ibud ? esc_attr($edit_ibud->ibudanumer) : ''; ?>" 
                               <?php echo $edit_ibud ? 'readonly' : 'required'; ?> />
                    </td>
                </tr>
                <tr>
                    <th><label for="eigandi">Eigandi</label></th>
                    <td>
                        <input type="text" id="eigandi" name="eigandi" 
                               value="<?php echo $edit_ibud ? esc_attr($edit_ibud->eigandi) : ''; ?>" required />
                    </td>
                </tr>
                <tr>
                    <th><label for="netfang">Netfang</label></th>
                    <td>
                        <input type="email" id="netfang" name="netfang" 
                               value="<?php echo $edit_ibud ? esc_attr($edit_ibud->netfang) : ''; ?>" />
                    </td>
                </tr>
                <tr>
                    <th><label for="simi">Símanúmer</label></th>
                    <td>
                        <input type="text" id="simi" name="simi" 
                               value="<?php echo $edit_ibud ? esc_attr($edit_ibud->simi) : ''; ?>" />
                    </td>
                </tr>
                <tr>
                    <th><label for="fermetrar">Fermetrar</label></th>
                    <td>
                        <input type="number" step="0.01" id="fermetrar" name="fermetrar" 
                               value="<?php echo $edit_ibud ? $edit_ibud->fermetrar : ''; ?>" required />
                    </td>
                </tr>
                <tr>
                    <th><label for="hlutdeild">Hlutdeild (0-1)</label></th>
                    <td>
                        <input type="number" step="0.0001" id="hlutdeild" name="hlutdeild" 
                               value="<?php echo $edit_ibud ? $edit_ibud->hlutdeild : ''; ?>" required />
                        <p class="description">T.d. 0.0245 fyrir 2,45% hlutdeild</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="hb_save_ibud" class="button-primary" 
                       value="<?php echo $edit_ibud ? 'Uppfæra íbúð' : 'Bæta við íbúð'; ?>" />
                <?php if ($edit_ibud): ?>
                    <a href="?page=husfelag-ibudaskra" class="button">Hætta við</a>
                <?php endif; ?>
            </p>
        </form>
    </div>
    
    <div class="hb-table-container">
        <h2>Skráðar íbúðir</h2>
        
        <?php if ($ibuddir): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Íbúðanúmer</th>
                        <th>Eigandi</th>
                        <th>Netfang</th>
                        <th>Símanúmer</th>
                        <th>Fermetrar</th>
                        <th>Hlutdeild</th>
                        <th>Aðgerðir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ibuddir as $ibud): ?>
                        <tr>
                            <td><?php echo esc_html($ibud->ibudanumer); ?></td>
                            <td><?php echo esc_html($ibud->eigandi); ?></td>
                            <td><?php echo esc_html($ibud->netfang); ?></td>
                            <td><?php echo esc_html($ibud->simi); ?></td>
                            <td><?php echo number_format($ibud->fermetrar, 2, ',', '.'); ?> m²</td>
                            <td><?php echo number_format($ibud->hlutdeild * 100, 2, ',', '.'); ?>%</td>
                            <td>
                                <a href="?page=husfelag-ibudaskra&action=edit&id=<?php echo $ibud->id; ?>" 
                                   class="button button-small">Breyta</a>
                                <a href="?page=husfelag-ibudaskra&action=delete&id=<?php echo $ibud->id; ?>&_wpnonce=<?php echo wp_create_nonce('delete_ibud'); ?>" 
                                   class="button button-small" 
                                   onclick="return confirm('Ertu viss um að þú viljir eyða þessari íbúð?')">Eyða</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Engar íbúðir skráðar ennþá.</p>
        <?php endif; ?>
    </div>
</div>
