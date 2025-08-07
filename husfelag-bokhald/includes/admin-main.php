<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Sækja grunntölur
$table_ibuddir = $wpdb->prefix . 'hb_ibuddir';
$table_faerslur = $wpdb->prefix . 'hb_faerslur';
$table_gjold = $wpdb->prefix . 'hb_manadargjold';

$fjoldi_ibudda = $wpdb->get_var("SELECT COUNT(*) FROM $table_ibuddir WHERE virkur = 1");
$table_reikningar = $wpdb->prefix . 'hb_reikningar';
$tekjur_manudur = $wpdb->get_var("SELECT SUM(f.upphad) FROM $table_faerslur f INNER JOIN $table_reikningar r ON f.kredit_reikning_id = r.id WHERE r.flokkur = 'income' AND MONTH(f.dagsetning) = MONTH(NOW()) AND YEAR(f.dagsetning) = YEAR(NOW())");
$gjold_manudur = $wpdb->get_var("SELECT SUM(f.upphad) FROM $table_faerslur f INNER JOIN $table_reikningar r ON f.debet_reikning_id = r.id WHERE r.flokkur = 'expense' AND MONTH(f.dagsetning) = MONTH(NOW()) AND YEAR(f.dagsetning) = YEAR(NOW())");
$ogreitt_gjold = $wpdb->get_var("SELECT COUNT(*) FROM $table_gjold WHERE greitt = 0 AND ar = YEAR(NOW()) AND manudur = MONTH(NOW())");

$tekjur_manudur = $tekjur_manudur ? $tekjur_manudur : 0;
$gjold_manudur = $gjold_manudur ? $gjold_manudur : 0;
?>

<div class="wrap">
    <h1>Húsfélags Bókhald - Yfirlit</h1>
    
    <div class="hb-dashboard">
        <div class="hb-stats-grid">
            <div class="hb-stat-card">
                <h3>Fjöldi íbúða</h3>
                <div class="hb-stat-number"><?php echo $fjoldi_ibudda; ?></div>
            </div>
            
            <div class="hb-stat-card">
                <h3>Tekjur þessa mánaðar</h3>
                <div class="hb-stat-number"><?php echo number_format($tekjur_manudur, 0, ',', '.'); ?> kr.</div>
            </div>
            
            <div class="hb-stat-card">
                <h3>Gjöld þessa mánaðar</h3>
                <div class="hb-stat-number"><?php echo number_format($gjold_manudur, 0, ',', '.'); ?> kr.</div>
            </div>
            
            <div class="hb-stat-card">
                <h3>Ógreidd mánaðargjöld</h3>
                <div class="hb-stat-number"><?php echo $ogreitt_gjold; ?></div>
            </div>
        </div>
        
        <div class="hb-quick-actions">
            <h2>Flýtiaðgerðir</h2>
            <div class="hb-action-buttons">
                <a href="?page=husfelag-faerslur" class="button button-primary">Bæta við fjárhagsfærslu</a>
                <a href="?page=husfelag-gjold" class="button button-secondary">Útbúa mánaðargjöld</a>
                <a href="?page=husfelag-ibudaskra" class="button button-secondary">Stjórna íbúðum</a>
                <a href="?page=husfelag-skyrslur" class="button button-secondary">Skoða skýrslur</a>
            </div>
        </div>
        
        <div class="hb-recent-activity">
            <h2>Nýjustu færslur</h2>
            <?php
            $recent_faerslur = $wpdb->get_results(
                "SELECT f.*, d.nafn AS debet_nafn, k.nafn AS kredit_nafn FROM $table_faerslur f 
                 LEFT JOIN $table_reikningar d ON f.debet_reikning_id = d.id 
                 LEFT JOIN $table_reikningar k ON f.kredit_reikning_id = k.id 
                 ORDER BY dagsetning DESC, stofnad DESC LIMIT 5"
            );

            if ($recent_faerslur) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th>Dagsetning</th><th>Lýsing</th><th>Upphæð</th><th>Debet</th><th>Kredit</th></tr></thead>';
                echo '<tbody>';
                foreach ($recent_faerslur as $faersla) {
                    echo '<tr>';
                    echo '<td>' . date('d.m.Y', strtotime($faersla->dagsetning)) . '</td>';
                    echo '<td>' . esc_html($faersla->lysing) . '</td>';
                    echo '<td>' . number_format($faersla->upphad, 0, ',', '.') . ' kr.</td>';
                    echo '<td>' . esc_html($faersla->debet_nafn) . '</td>';
                    echo '<td>' . esc_html($faersla->kredit_nafn) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p>Engar færslur skráðar ennþá.</p>';
            }
            ?>
        </div>
    </div>
</div>
