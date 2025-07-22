<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_faerslur = $wpdb->prefix . 'hb_faerslur';
$table_gjold = $wpdb->prefix . 'hb_manadargjold';
$table_ibuddir = $wpdb->prefix . 'hb_ibuddir';

// Stillingar fyrir skýrslur
$current_year = date('Y');
$selected_year = isset($_GET['ar']) ? intval($_GET['ar']) : $current_year;
$selected_month = isset($_GET['manudur']) ? intval($_GET['manudur']) : 0;

// Ársyfirlit
if ($selected_month == 0) {
    $where_clause = "WHERE YEAR(dagsetning) = %d";
    $params = array($selected_year);
    $title = "Ársyfirlit $selected_year";
} else {
    $where_clause = "WHERE YEAR(dagsetning) = %d AND MONTH(dagsetning) = %d";
    $params = array($selected_year, $selected_month);
    $manudur_nofn = array(
        1 => 'Janúar', 2 => 'Febrúar', 3 => 'Mars', 4 => 'Apríl',
        5 => 'Maí', 6 => 'Júní', 7 => 'Júlí', 8 => 'Ágúst',
        9 => 'September', 10 => 'Október', 11 => 'Nóvember', 12 => 'Desember'
    );
    $title = $manudur_nofn[$selected_month] . " $selected_year";
}

// Sækja gögn
$tekjur_samtals = $wpdb->get_var($wpdb->prepare(
    "SELECT SUM(upphad) FROM $table_faerslur $where_clause AND tegund = 'tekjur'",
    ...$params
));

$gjold_samtals = $wpdb->get_var($wpdb->prepare(
    "SELECT SUM(upphad) FROM $table_faerslur $where_clause AND tegund = 'gjold'",
    ...$params
));

// Tekjur eftir flokkum
$tekjur_flokkar = $wpdb->get_results($wpdb->prepare(
    "SELECT flokkur, SUM(upphad) as samtals FROM $table_faerslur 
     $where_clause AND tegund = 'tekjur' 
     GROUP BY flokkur ORDER BY samtals DESC",
    ...$params
));

// Gjöld eftir flokkum
$gjold_flokkar = $wpdb->get_results($wpdb->prepare(
    "SELECT flokkur, SUM(upphad) as samtals FROM $table_faerslur 
     $where_clause AND tegund = 'gjold' 
     GROUP BY flokkur ORDER BY samtals DESC",
    ...$params
));

// Mánaðarleg þróun (aðeins fyrir ársyfirlit)
$manadarleg_throun = array();
if ($selected_month == 0) {
    for ($i = 1; $i <= 12; $i++) {
        $tekjur_man = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(upphad) FROM $table_faerslur 
             WHERE YEAR(dagsetning) = %d AND MONTH(dagsetning) = %d AND tegund = 'tekjur'",
            $selected_year, $i
        ));
        
        $gjold_man = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(upphad) FROM $table_faerslur 
             WHERE YEAR(dagsetning) = %d AND MONTH(dagsetning) = %d AND tegund = 'gjold'",
            $selected_year, $i
        ));
        
        $manadarleg_throun[$i] = array(
            'tekjur' => $tekjur_man ?: 0,
            'gjold' => $gjold_man ?: 0
        );
    }
}

// Staða mánaðargjalda
$manadargjold_stada = $wpdb->get_results($wpdb->prepare(
    "SELECT 
        COUNT(*) as fjoldi_ibudda,
        SUM(samtals) as samtals_gjold,
        SUM(CASE WHEN greitt = 1 THEN samtals ELSE 0 END) as greitt_samtals,
        COUNT(CASE WHEN greitt = 0 THEN 1 END) as ogreitt_fjoldi
     FROM $table_gjold 
     WHERE ar = %d" . ($selected_month > 0 ? " AND manudur = %d" : ""),
    $selected_month > 0 ? array($selected_year, $selected_month) : array($selected_year)
));

$tekjur_samtals = $tekjur_samtals ?: 0;
$gjold_samtals = $gjold_samtals ?: 0;
$hagnadur = $tekjur_samtals - $gjold_samtals;
?>

<div class="wrap">
    <h1>Fjárhagsskýrslur</h1>
    
    <div class="hb-filter-container">
        <form method="get" action="">
            <input type="hidden" name="page" value="husfelag-skyrslur" />
            
            <label for="ar">Ár:</label>
            <select name="ar" id="ar">
                <?php for ($year = 2020; $year <= date('Y') + 1; $year++): ?>
                    <option value="<?php echo $year; ?>" <?php selected($year, $selected_year); ?>>
                        <?php echo $year; ?>
                    </option>
                <?php endfor; ?>
            </select>
            
            <label for="manudur">Mánuður:</label>
            <select name="manudur" id="manudur">
                <option value="0" <?php selected(0, $selected_month); ?>>Allt árið</option>
                <?php 
                $manudur_nofn = array(
                    1 => 'Janúar', 2 => 'Febrúar', 3 => 'Mars', 4 => 'Apríl',
                    5 => 'Maí', 6 => 'Júní', 7 => 'Júlí', 8 => 'Ágúst',
                    9 => 'September', 10 => 'Október', 11 => 'Nóvember', 12 => 'Desember'
                );
                foreach ($manudur_nofn as $num => $nafn): 
                ?>
                    <option value="<?php echo $num; ?>" <?php selected($num, $selected_month); ?>>
                        <?php echo $nafn; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <input type="submit" class="button" value="Skoða skýrslu" />
            <button type="button" class="button" onclick="window.print()">Prenta skýrslu</button>
        </form>
    </div>
    
    <div class="hb-table-container">
        <h2><?php echo $title; ?></h2>
        
        <div class="hb-report-summary">
            <div class="hb-report-card income">
                <h4>Tekjur samtals</h4>
                <div class="amount"><?php echo number_format($tekjur_samtals, 0, ',', '.'); ?> kr.</div>
            </div>
            <div class="hb-report-card expense">
                <h4>Gjöld samtals</h4>
                <div class="amount"><?php echo number_format($gjold_samtals, 0, ',', '.'); ?> kr.</div>
            </div>
            <div class="hb-report-card <?php echo $hagnadur >= 0 ? 'income' : 'expense'; ?>">
                <h4><?php echo $hagnadur >= 0 ? 'Hagnaður' : 'Tap'; ?></h4>
                <div class="amount"><?php echo number_format($hagnadur, 0, ',', '.'); ?> kr.</div>
            </div>
        </div>
        
        <?php if ($manadargjold_stada): ?>
            <h3>Staða mánaðargjalda</h3>
            <?php $stada = $manadargjold_stada[0]; ?>
            <div class="hb-report-summary">
                <div class="hb-report-card">
                    <h4>Samtals mánaðargjöld</h4>
                    <div class="amount"><?php echo number_format($stada->samtals_gjold ?: 0, 0, ',', '.'); ?> kr.</div>
                </div>
                <div class="hb-report-card income">
                    <h4>Greidd gjöld</h4>
                    <div class="amount"><?php echo number_format($stada->greitt_samtals ?: 0, 0, ',', '.'); ?> kr.</div>
                </div>
                <div class="hb-report-card expense">
                    <h4>Ógreidd gjöld</h4>
                    <div class="amount"><?php echo $stada->ogreitt_fjoldi ?: 0; ?> íbúðir</div>
                </div>
            </div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px;">
            <div>
                <h3>Tekjur eftir flokkum</h3>
                <?php if ($tekjur_flokkar): ?>
                    <table class="wp-list-table widefat">
                        <thead>
                            <tr>
                                <th>Flokkur</th>
                                <th>Upphæð</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tekjur_flokkar as $flokkur): ?>
                                <tr>
                                    <td><?php echo esc_html($flokkur->flokkur); ?></td>
                                    <td class="hb-income"><?php echo number_format($flokkur->samtals, 0, ',', '.'); ?> kr.</td>
                                    <td><?php echo $tekjur_samtals > 0 ? number_format(($flokkur->samtals / $tekjur_samtals) * 100, 1) : 0; ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Engar tekjur skráðar fyrir þetta tímabil.</p>
                <?php endif; ?>
            </div>
            
            <div>
                <h3>Gjöld eftir flokkum</h3>
                <?php if ($gjold_flokkar): ?>
                    <table class="wp-list-table widefat">
                        <thead>
                            <tr>
                                <th>Flokkur</th>
                                <th>Upphæð</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gjold_flokkar as $flokkur): ?>
                                <tr>
                                    <td><?php echo esc_html($flokkur->flokkur); ?></td>
                                    <td class="hb-expense"><?php echo number_format($flokkur->samtals, 0, ',', '.'); ?> kr.</td>
                                    <td><?php echo $gjold_samtals > 0 ? number_format(($flokkur->samtals / $gjold_samtals) * 100, 1) : 0; ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Engin gjöld skráð fyrir þetta tímabil.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($selected_month == 0 && !empty($manadarleg_throun)): ?>
            <h3>Mánaðarleg þróun <?php echo $selected_year; ?></h3>
            <table class="wp-list-table widefat">
                <thead>
                    <tr>
                        <th>Mánuður</th>
                        <th>Tekjur</th>
                        <th>Gjöld</th>
                        <th>Mismunur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($manadarleg_throun as $man_num => $data): ?>
                        <tr>
                            <td><?php echo $manudur_nofn[$man_num]; ?></td>
                            <td class="hb-income"><?php echo number_format($data['tekjur'], 0, ',', '.'); ?> kr.</td>
                            <td class="hb-expense"><?php echo number_format($data['gjold'], 0, ',', '.'); ?> kr.</td>
                            <td class="<?php echo ($data['tekjur'] - $data['gjold']) >= 0 ? 'hb-income' : 'hb-expense'; ?>">
                                <?php echo number_format($data['tekjur'] - $data['gjold'], 0, ',', '.'); ?> kr.
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
