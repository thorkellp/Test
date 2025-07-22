<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_gjold = $wpdb->prefix . 'hb_manadargjold';
$table_ibuddir = $wpdb->prefix . 'hb_ibuddir';
$table_faerslur = $wpdb->prefix . 'hb_faerslur';

// Útbúa mánaðargjöld
if (isset($_POST['hb_create_gjold']) && wp_verify_nonce($_POST['hb_nonce'], 'hb_create_gjold')) {
    $ar = intval($_POST['ar']);
    $manudur = intval($_POST['manudur']);
    $grunngjald = floatval($_POST['grunngjald']);
    
    // Athuga hvort gjöld séu þegar til fyrir þennan mánuð
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_gjold WHERE ar = %d AND manudur = %d",
        $ar, $manudur
    ));
    
    if ($existing > 0) {
        echo '<div class="notice notice-error"><p>Mánaðargjöld eru þegar til fyrir ' . $manudur . '/' . $ar . '</p></div>';
    } else {
        // Sækja allar virkar íbúðir
        $ibuddir = $wpdb->get_results("SELECT * FROM $table_ibuddir WHERE virkur = 1");
        
        $success_count = 0;
        foreach ($ibuddir as $ibud) {
            $upphad_ibud = $grunngjald * $ibud->hlutdeild;
            
            $result = $wpdb->insert(
                $table_gjold,
                array(
                    'ar' => $ar,
                    'manudur' => $manudur,
                    'ibudanumer' => $ibud->ibudanumer,
                    'grunngjald' => $grunngjald,
                    'aukagjold' => 0,
                    'samtals' => $upphad_ibud
                ),
                array('%d', '%d', '%s', '%f', '%f', '%f')
            );
            
            if ($result) {
                $success_count++;
            }
        }
        
        if ($success_count > 0) {
            echo '<div class="notice notice-success"><p>Mánaðargjöld útbúin fyrir ' . $success_count . ' íbúðir</p></div>';
        }
    }
}

// Merkja sem greitt
if (isset($_POST['hb_mark_paid']) && wp_verify_nonce($_POST['hb_nonce'], 'hb_mark_paid')) {
    $gjald_ids = array_map('intval', $_POST['gjald_ids']);
    
    foreach ($gjald_ids as $id) {
        $gjald = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_gjold WHERE id = %d", $id));
        
        if ($gjald) {
            // Uppfæra gjaldið
            $wpdb->update(
                $table_gjold,
                array('greitt' => 1, 'greitt_dags' => current_time('mysql')),
                array('id' => $id),
                array('%d', '%s'),
                array('%d')
            );
            
            // Bæta við fjárhagsfærslu
            $wpdb->insert(
                $table_faerslur,
                array(
                    'dagsetning' => current_time('mysql'),
                    'lysing' => 'Mánaðargjald - ' . $gjald->ibudanumer . ' (' . $gjald->manudur . '/' . $gjald->ar . ')',
                    'upphad' => $gjald->samtals,
                    'tegund' => 'tekjur',
                    'flokkur' => 'Mánaðargjöld',
                    'ibudanumer' => $gjald->ibudanumer
                ),
                array('%s', '%s', '%f', '%s', '%s', '%s')
            );
        }
    }
    
    echo '<div class="notice notice-success"><p>' . count($gjald_ids) . ' gjöld merkt sem greidd</p></div>';
}

// Núverandi ár og mánuður
$current_year = date('Y');
$current_month = date('n');

// Sækja gjöld fyrir núverandi mánuð
$gjold_manudur = $wpdb->get_results($wpdb->prepare(
    "SELECT g.*, i.eigandi FROM $table_gjold g 
     LEFT JOIN $table_ibuddir i ON g.ibudanumer = i.ibudanumer 
     WHERE g.ar = %d AND g.manudur = %d 
     ORDER BY g.ibudanumer",
    $current_year, $current_month
));

// Tölfræði
$samtals_gjold = $wpdb->get_var($wpdb->prepare(
    "SELECT SUM(samtals) FROM $table_gjold WHERE ar = %d AND manudur = %d",
    $current_year, $current_month
));

$greitt_gjold = $wpdb->get_var($wpdb->prepare(
    "SELECT SUM(samtals) FROM $table_gjold WHERE ar = %d AND manudur = %d AND greitt = 1",
    $current_year, $current_month
));

$ogreitt_fjoldi = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $table_gjold WHERE ar = %d AND manudur = %d AND greitt = 0",
    $current_year, $current_month
));

$manudur_nofn = array(
    1 => 'Janúar', 2 => 'Febrúar', 3 => 'Mars', 4 => 'Apríl',
    5 => 'Maí', 6 => 'Júní', 7 => 'Júlí', 8 => 'Ágúst',
    9 => 'September', 10 => 'Október', 11 => 'Nóvember', 12 => 'Desember'
);
?>

<div class="wrap">
    <h1>Mánaðargjöld</h1>
    
    <div class="hb-stats-grid">
        <div class="hb-stat-card">
            <h3>Samtals gjöld þennan mánuð</h3>
            <div class="hb-stat-number"><?php echo number_format($samtals_gjold ?: 0, 0, ',', '.'); ?> kr.</div>
        </div>
        <div class="hb-stat-card">
            <h3>Greidd gjöld</h3>
            <div class="hb-stat-number"><?php echo number_format($greitt_gjold ?: 0, 0, ',', '.'); ?> kr.</div>
        </div>
        <div class="hb-stat-card">
            <h3>Ógreidd gjöld</h3>
            <div class="hb-stat-number"><?php echo $ogreitt_fjoldi ?: 0; ?> íbúðir</div>
        </div>
    </div>
    
    <div class="hb-form-container">
        <h2>Útbúa mánaðargjöld</h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('hb_create_gjold', 'hb_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th><label for="ar">Ár</label></th>
                    <td>
                        <input type="number" id="ar" name="ar" value="<?php echo $current_year; ?>" min="2020" max="2030" required />
                    </td>
                </tr>
                <tr>
                    <th><label for="manudur">Mánuður</label></th>
                    <td>
                        <select id="manudur" name="manudur" required>
                            <?php foreach ($manudur_nofn as $num => $nafn): ?>
                                <option value="<?php echo $num; ?>" <?php selected($num, $current_month); ?>>
                                    <?php echo $nafn; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="grunngjald">Grunngjald (kr.)</label></th>
                    <td>
                        <input type="number" step="1" id="grunngjald" name="grunngjald" value="50000" required />
                        <p class="description">Heildarupphæð sem skiptist á allar íbúðir eftir hlutdeild</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="hb_create_gjold" class="button-primary" value="Útbúa mánaðargjöld" />
            </p>
        </form>
    </div>
    
    <?php if ($gjold_manudur): ?>
    <div class="hb-table-container">
        <h2>Mánaðargjöld - <?php echo $manudur_nofn[$current_month] . ' ' . $current_year; ?></h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('hb_mark_paid', 'hb_nonce'); ?>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th class="check-column"><input type="checkbox" id="select-all" /></th>
                        <th>Íbúðanúmer</th>
                        <th>Eigandi</th>
                        <th>Grunngjald</th>
                        <th>Aukagjöld</th>
                        <th>Samtals</th>
                        <th>Staða</th>
                        <th>Greiðsludagur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gjold_manudur as $gjald): ?>
                        <tr>
                            <td class="check-column">
                                <?php if (!$gjald->greitt): ?>
                                    <input type="checkbox" name="gjald_ids[]" value="<?php echo $gjald->id; ?>" />
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($gjald->ibudanumer); ?></td>
                            <td><?php echo esc_html($gjald->eigandi); ?></td>
                            <td><?php echo number_format($gjald->grunngjald * ($wpdb->get_var($wpdb->prepare("SELECT hlutdeild FROM $table_ibuddir WHERE ibudanumer = %s", $gjald->ibudanumer)) ?: 0), 0, ',', '.'); ?> kr.</td>
                            <td><?php echo number_format($gjald->aukagjold, 0, ',', '.'); ?> kr.</td>
                            <td><strong><?php echo number_format($gjald->samtals, 0, ',', '.'); ?> kr.</strong></td>
                            <td>
                                <?php if ($gjald->greitt): ?>
                                    <span style="color: #46b450;">✓ Greitt</span>
                                <?php else: ?>
                                    <span style="color: #dc3232;">○ Ógreitt</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $gjald->greitt_dags ? date('d.m.Y', strtotime($gjald->greitt_dags)) : '-'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="hb_mark_paid" class="button-primary" value="Merkja valin gjöld sem greidd" />
            </p>
        </form>
    </div>
    <?php else: ?>
    <div class="hb-table-container">
        <p>Engin mánaðargjöld útbúin fyrir <?php echo $manudur_nofn[$current_month] . ' ' . $current_year; ?>.</p>
    </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="gjald_ids[]"]');
    checkboxes.forEach(cb => cb.checked = this.checked);
});
</script>
