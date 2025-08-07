<?php
/**
 * Migration: breyta einhliða færslum í tvíhliða
 */
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_faerslur = $wpdb->prefix . 'hb_faerslur';

$bank_account = intval(get_option('hb_sync_bank_account'));
$income_account = intval(get_option('hb_sync_income_account'));
$expense_account = intval(get_option('hb_sync_expense_account'));

$rows = $wpdb->get_results("SELECT id, tegund FROM $table_faerslur WHERE debet_reikning_id IS NULL OR kredit_reikning_id IS NULL");

foreach ($rows as $row) {
    if ($row->tegund === 'tekjur') {
        $debet = $bank_account;
        $kredit = $income_account;
    } else {
        $debet = $expense_account;
        $kredit = $bank_account;
    }

    $wpdb->update(
        $table_faerslur,
        array(
            'debet_reikning_id' => $debet,
            'kredit_reikning_id' => $kredit
        ),
        array('id' => $row->id),
        array('%d', '%d'),
        array('%d')
    );
}

$sum_debet = $wpdb->get_var("SELECT SUM(upphad) FROM $table_faerslur");
$sum_kredit = $wpdb->get_var("SELECT SUM(upphad) FROM $table_faerslur");

if ($sum_debet == $sum_kredit) {
    echo 'Jafnvægi: ' . $sum_debet . PHP_EOL;
} else {
    echo 'Ójafnvægi: debet=' . $sum_debet . ' kredit=' . $sum_kredit . PHP_EOL;
}
