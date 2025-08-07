<?php
if (!defined('ABSPATH')) {
    exit;
}

// Öryggisathugun
if (!current_user_can('manage_options')) {
    wp_die(__('Þú hefur ekki heimild til þessa.', 'husfelag-bokhald'));
}

global $wpdb;
$table_faerslur = $wpdb->prefix . 'hb_faerslur';
$table_ibuddir = $wpdb->prefix . 'hb_ibuddir';
$table_reikningar = $wpdb->prefix . 'hb_reikningar';

// Vinna úr form með öryggisathugun
if (isset($_POST['hb_save_faersla']) && wp_verify_nonce($_POST['hb_nonce'], 'hb_save_faersla') && current_user_can('manage_options')) {
    $dagsetning = sanitize_text_field($_POST['dagsetning']);
    $lysing = sanitize_textarea_field($_POST['lysing']);
    $upphad = floatval($_POST['upphad']);
    $debet_reikning_id = intval($_POST['debet_reikning_id']);
    $kredit_reikning_id = intval($_POST['kredit_reikning_id']);
    $ibudanumer = sanitize_text_field($_POST['ibudanumer']);
    $kvittun = sanitize_text_field($_POST['kvittun']);

    $result = $wpdb->insert(
        $table_faerslur,
        array(
            'dagsetning' => $dagsetning,
            'lysing' => $lysing,
            'upphad' => $upphad,
            'debet_reikning_id' => $debet_reikning_id,
            'kredit_reikning_id' => $kredit_reikning_id,
            'ibudanumer' => $ibudanumer ? $ibudanumer : null,
            'kvittun' => $kvittun
        ),
        array('%s', '%s', '%f', '%d', '%d', '%s', '%s')
    );
    
    if ($result) {
        echo '<div class="notice notice-success"><p>Fjárhagsfærsla skráð!</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>Villa við að skrá fjárhagsfærslu.</p></div>';
    }
}

// Eyða færslu
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_faersla')) {
    $id = intval($_GET['id']);
    $wpdb->delete($table_faerslur, array('id' => $id), array('%d'));
    echo '<div class="notice notice-success"><p>Fjárhagsfærsla eytt!</p></div>';
}

// Sækja íbúðir og reikninga fyrir dropdown
$ibuddir = $wpdb->get_results("SELECT ibudanumer, eigandi FROM $table_ibuddir WHERE virkur = 1 ORDER BY ibudanumer");
$reikningar = $wpdb->get_results("SELECT id, nafn FROM $table_reikningar ORDER BY numer");

// Sækja færslur með síun
$where_clause = "WHERE 1=1";
$search_params = array();

if (isset($_GET['manudur']) && $_GET['manudur'] != '') {
    $where_clause .= " AND MONTH(dagsetning) = %d AND YEAR(dagsetning) = %d";
    $search_params[] = date('n', strtotime($_GET['manudur'] . '-01'));
    $search_params[] = date('Y', strtotime($_GET['manudur'] . '-01'));
}

$query = "SELECT f.*, d.nafn AS debet_nafn, k.nafn AS kredit_nafn FROM $table_faerslur f
    LEFT JOIN $table_reikningar d ON f.debet_reikning_id = d.id
    LEFT JOIN $table_reikningar k ON f.kredit_reikning_id = k.id
    $where_clause ORDER BY dagsetning DESC, stofnad DESC";
if (!empty($search_params)) {
    $faerslur = $wpdb->get_results($wpdb->prepare($query, $search_params));
} else {
    $faerslur = $wpdb->get_results($query);
}
?>

<div class="wrap">
    <h1>Fjárhagsfærslur</h1>
    
    <div class="hb-form-container">
        <h2>Bæta við fjárhagsfærslu</h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('hb_save_faersla', 'hb_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th><label for="dagsetning">Dagsetning</label></th>
                    <td>
                        <input type="date" id="dagsetning" name="dagsetning" 
                               value="<?php echo date('Y-m-d'); ?>" required />
                    </td>
                </tr>
                <tr>
                    <th><label for="debet_reikning_id">Debet reikningur</label></th>
                    <td>
                        <select id="debet_reikning_id" name="debet_reikning_id" required>
                            <option value="">Veldu reikning</option>
                            <?php foreach ($reikningar as $r): ?>
                                <option value="<?php echo esc_attr($r->id); ?>"><?php echo esc_html($r->nafn); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="kredit_reikning_id">Kredit reikningur</label></th>
                    <td>
                        <select id="kredit_reikning_id" name="kredit_reikning_id" required>
                            <option value="">Veldu reikning</option>
                            <?php foreach ($reikningar as $r): ?>
                                <option value="<?php echo esc_attr($r->id); ?>"><?php echo esc_html($r->nafn); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="lysing">Lýsing</label></th>
                    <td>
                        <textarea id="lysing" name="lysing" rows="3" cols="50" required></textarea>
                    </td>
                </tr>
                <tr>
                    <th><label for="upphad">Upphæð (kr.)</label></th>
                    <td>
                        <input type="number" step="0.01" id="upphad" name="upphad" required />
                    </td>
                </tr>
                <tr>
                    <th><label for="ibudanumer">Íbúð (valfrjálst)</label></th>
                    <td>
                        <select id="ibudanumer" name="ibudanumer">
                            <option value="">Almennt</option>
                            <?php foreach ($ibuddir as $ibud): ?>
                                <option value="<?php echo esc_attr($ibud->ibudanumer); ?>">
                                    <?php echo esc_html($ibud->ibudanumer . ' - ' . $ibud->eigandi); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="kvittun">Kvittun/Skjal</label></th>
                    <td>
                        <input type="text" id="kvittun" name="kvittun" 
                               placeholder="Kvittunarnúmer eða skjalaheiti" />
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="hb_save_faersla" class="button-primary" value="Skrá fjárhagsfærslu" />
            </p>
        </form>
    </div>
    
    <div class="hb-filter-container">
        <h2>Síun færslna</h2>
        <form method="get" action="">
            <input type="hidden" name="page" value="husfelag-faerslur" />

            <input type="month" name="manudur" value="<?php echo $_GET['manudur'] ?? ''; ?>" />

            <input type="submit" class="button" value="Sía" />
            <a href="?page=husfelag-faerslur" class="button">Hreinsa síu</a>
        </form>
    </div>
    
    <div class="hb-table-container">
        <h2>Fjárhagsfærslur</h2>
        
        <?php if ($faerslur): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Dagsetning</th>
                        <th>Lýsing</th>
                        <th>Upphæð</th>
                        <th>Debet</th>
                        <th>Kredit</th>
                        <th>Íbúð</th>
                        <th>Kvittun</th>
                        <th>Aðgerðir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $samtals = 0;
                    foreach ($faerslur as $faersla):
                        $samtals += $faersla->upphad;
                    ?>
                        <tr>
                            <td><?php echo date('d.m.Y', strtotime($faersla->dagsetning)); ?></td>
                            <td><?php echo esc_html($faersla->lysing); ?></td>
                            <td><?php echo number_format($faersla->upphad, 0, ',', '.'); ?> kr.</td>
                            <td><?php echo esc_html($faersla->debet_nafn); ?></td>
                            <td><?php echo esc_html($faersla->kredit_nafn); ?></td>
                            <td><?php echo esc_html($faersla->ibudanumer); ?></td>
                            <td><?php echo esc_html($faersla->kvittun); ?></td>
                            <td>
                                <a href="?page=husfelag-faerslur&action=delete&id=<?php echo $faersla->id; ?>&_wpnonce=<?php echo wp_create_nonce('delete_faersla'); ?>"
                                   class="button button-small"
                                   onclick="return confirm('Ertu viss um að þú viljir eyða þessari færslu?')">Eyða</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">Samtals</th>
                        <th colspan="6"><?php echo number_format($samtals, 0, ',', '.'); ?> kr.</th>
                    </tr>
                </tfoot>
            </table>
        <?php else: ?>
            <p>Engar fjárhagsfærslur skráðar ennþá.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Engar sérstakar javascript-aðgerðir í dag
</script>
