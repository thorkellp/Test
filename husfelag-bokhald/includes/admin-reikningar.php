<?php
if (!defined('ABSPATH')) {
    exit;
}

// Öryggisathugun
if (!current_user_can('manage_options')) {
    wp_die(__('Þú hefur ekki heimild til þessa.', 'husfelag-bokhald'));
}

global $wpdb;
$table_reikningar = $wpdb->prefix . 'hb_reikningar';

// Vinna úr formi
if (isset($_POST['hb_save_reikningur']) && wp_verify_nonce($_POST['hb_nonce'], 'hb_save_reikningur')) {
    $nafn = sanitize_text_field($_POST['nafn']);
    $flokkur = sanitize_text_field($_POST['flokkur']);
    $numer = sanitize_text_field($_POST['numer']);

    $wpdb->insert(
        $table_reikningar,
        array(
            'nafn' => $nafn,
            'flokkur' => $flokkur,
            'numer' => $numer
        ),
        array('%s', '%s', '%s')
    );

    echo '<div class="notice notice-success"><p>Reikningur skráður!</p></div>';
}

$reikningar = $wpdb->get_results("SELECT * FROM $table_reikningar ORDER BY numer");
?>

<div class="wrap">
    <h1>Reikningar</h1>

    <h2>Bæta við reikningi</h2>
    <form method="post" action="">
        <?php wp_nonce_field('hb_save_reikningur', 'hb_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label for="numer">Númer</label></th>
                <td><input type="text" id="numer" name="numer" required /></td>
            </tr>
            <tr>
                <th><label for="nafn">Nafn</label></th>
                <td><input type="text" id="nafn" name="nafn" required /></td>
            </tr>
            <tr>
                <th><label for="flokkur">Flokkur</label></th>
                <td>
                    <select id="flokkur" name="flokkur" required>
                        <option value="asset">Eign</option>
                        <option value="liability">Skuld</option>
                        <option value="income">Tekjur</option>
                        <option value="expense">Gjöld</option>
                    </select>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="hb_save_reikningur" class="button-primary" value="Skrá reikning" />
        </p>
    </form>

    <?php if ($reikningar): ?>
        <h2>Skráðir reikningar</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Númer</th>
                    <th>Nafn</th>
                    <th>Flokkur</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reikningar as $r): ?>
                    <tr>
                        <td><?php echo esc_html($r->numer); ?></td>
                        <td><?php echo esc_html($r->nafn); ?></td>
                        <td><?php echo esc_html($r->flokkur); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
