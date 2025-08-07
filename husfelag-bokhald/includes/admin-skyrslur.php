<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_faerslur = $wpdb->prefix . 'hb_faerslur';
$table_reikningar = $wpdb->prefix . 'hb_reikningar';

$current_year = date('Y');
$selected_year = isset($_GET['ar']) ? intval($_GET['ar']) : $current_year;
$selected_month = isset($_GET['manudur']) ? intval($_GET['manudur']) : 0;

$params = array($selected_year);
$period_filter = "YEAR(f.dagsetning) = %d";
if ($selected_month > 0) {
    $period_filter .= " AND MONTH(f.dagsetning) = %d";
    $params[] = $selected_month;
}

$query = $wpdb->prepare(
    "SELECT r.id, r.nafn, r.flokkur, r.numer,
            SUM(CASE WHEN f.debet_reikning_id = r.id THEN f.upphad ELSE 0 END) AS debet,
            SUM(CASE WHEN f.kredit_reikning_id = r.id THEN f.upphad ELSE 0 END) AS kredit
     FROM $table_reikningar r
     LEFT JOIN $table_faerslur f ON f.debet_reikning_id = r.id OR f.kredit_reikning_id = r.id
     WHERE $period_filter
     GROUP BY r.id, r.nafn, r.flokkur, r.numer
     ORDER BY r.numer",
    $params
);
$reikningar = $wpdb->get_results($query);

$income = $expense = $asset = $liability = array();
$income_total = $expense_total = $asset_total = $liability_total = 0;

foreach ($reikningar as $r) {
    $balance = $r->debet - $r->kredit;
    switch ($r->flokkur) {
        case 'income':
            $amount = $r->kredit - $r->debet;
            $income_total += $amount;
            $income[] = array($r->nafn, $amount);
            break;
        case 'expense':
            $amount = $r->debet - $r->kredit;
            $expense_total += $amount;
            $expense[] = array($r->nafn, $amount);
            break;
        case 'asset':
            $asset_total += $balance;
            $asset[] = array($r->nafn, $balance);
            break;
        case 'liability':
            $amount = $r->kredit - $r->debet;
            $liability_total += $amount;
            $liability[] = array($r->nafn, $amount);
            break;
    }
}
$profit = $income_total - $expense_total;
?>

<div class="wrap">
    <h1>Fjárhagsskýrslur</h1>

    <form method="get" action="">
        <input type="hidden" name="page" value="husfelag-skyrslur" />
        <label for="ar">Ár:</label>
        <input type="number" name="ar" id="ar" value="<?php echo esc_attr($selected_year); ?>" />
        <label for="manudur">Mánuður (0 = allt árið):</label>
        <input type="number" name="manudur" id="manudur" min="0" max="12" value="<?php echo esc_attr($selected_month); ?>" />
        <input type="submit" class="button" value="Skoða" />
    </form>

    <h2>Rekstrarreikningur</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead><tr><th>Reikningur</th><th>Upphæð</th></tr></thead>
        <tbody>
            <?php foreach ($income as $i): ?>
            <tr><td><?php echo esc_html($i[0]); ?></td><td class="hb-income"><?php echo number_format($i[1], 0, ',', '.'); ?> kr.</td></tr>
            <?php endforeach; ?>
            <?php foreach ($expense as $e): ?>
            <tr><td><?php echo esc_html($e[0]); ?></td><td class="hb-expense"><?php echo number_format($e[1], 0, ',', '.'); ?> kr.</td></tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><th>Hagnaður/Tap</th><th><?php echo number_format($profit, 0, ',', '.'); ?> kr.</th></tr>
        </tfoot>
    </table>

    <h2>Efnahagsreikningur</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead><tr><th>Reikningur</th><th>Upphæð</th></tr></thead>
        <tbody>
            <?php foreach ($asset as $a): ?>
            <tr><td><?php echo esc_html($a[0]); ?></td><td><?php echo number_format($a[1], 0, ',', '.'); ?> kr.</td></tr>
            <?php endforeach; ?>
            <?php foreach ($liability as $l): ?>
            <tr><td><?php echo esc_html($l[0]); ?></td><td><?php echo number_format($l[1], 0, ',', '.'); ?> kr.</td></tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><th>Mismunur</th><th><?php echo number_format($asset_total - $liability_total, 0, ',', '.'); ?> kr.</th></tr>
        </tfoot>
    </table>
</div>
