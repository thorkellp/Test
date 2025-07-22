<?php
/**
 * Demo gögn fyrir Húsfélags Bókhald
 */

// WordPress setup
define('WP_USE_THEMES', false);
require_once('wp-config.php');
require_once('wp-blog-header.php');

global $wpdb;

echo "🏠 Búi til demo gögn fyrir Húsfélags Bókhald...\n\n";

// 1. Búa til íbúðir
echo "📋 Bæti við íbúðum...\n";
$ibuddir = [
    ['ibudanumer' => '101', 'eigandi' => 'Jón Jónsson', 'netfang' => 'jon@email.is', 'simi' => '581-1234', 'fermetrar' => 85.5, 'hlutdeild' => 0.0855],
    ['ibudanumer' => '102', 'eigandi' => 'Anna Sigurðardóttir', 'netfang' => 'anna@email.is', 'simi' => '581-2345', 'fermetrar' => 92.0, 'hlutdeild' => 0.0920],
    ['ibudanumer' => '201', 'eigandi' => 'Pétur Pétursson', 'netfang' => 'petur@email.is', 'simi' => '581-3456', 'fermetrar' => 78.3, 'hlutdeild' => 0.0783],
    ['ibudanumer' => '202', 'eigandi' => 'María Jóhannsdóttir', 'netfang' => 'maria@email.is', 'simi' => '581-4567', 'fermetrar' => 105.2, 'hlutdeild' => 0.1052],
    ['ibudanumer' => '301', 'eigandi' => 'Gunnar Gunnarsson', 'netfang' => 'gunnar@email.is', 'simi' => '581-5678', 'fermetrar' => 88.7, 'hlutdeild' => 0.0887],
    ['ibudanumer' => '302', 'eigandi' => 'Sigríður Einarsdóttir', 'netfang' => 'sigridur@email.is', 'simi' => '581-6789', 'fermetrar' => 95.4, 'hlutdeild' => 0.0954],
    ['ibudanumer' => '401', 'eigandi' => 'Ólafur Ragnarsson', 'netfang' => 'olafur@email.is', 'simi' => '581-7890', 'fermetrar' => 110.8, 'hlutdeild' => 0.1108],
    ['ibudanumer' => '402', 'eigandi' => 'Ragnhildur Þórsdóttir', 'netfang' => 'ragnhildur@email.is', 'simi' => '581-8901', 'fermetrar' => 82.1, 'hlutdeild' => 0.0821],
    ['ibudanumer' => '501', 'eigandi' => 'Björn Bjarnason', 'netfang' => 'bjorn@email.is', 'simi' => '581-9012', 'fermetrar' => 125.5, 'hlutdeild' => 0.1255],
    ['ibudanumer' => '502', 'eigandi' => 'Helga Helgadóttir', 'netfang' => 'helga@email.is', 'simi' => '581-0123', 'fermetrar' => 89.5, 'hlutdeild' => 0.0895]
];

foreach ($ibuddir as $ibud) {
    $wpdb->insert(
        $wpdb->prefix . 'hb_ibuddir',
        $ibud,
        ['%s', '%s', '%s', '%s', '%f', '%f']
    );
}
echo "✅ Bætti við " . count($ibuddir) . " íbúðum\n\n";

// 2. Búa til tekjur og gjöld
echo "💰 Bæti við fjárhagslegum færslum...\n";

// Tekjur
$tekjur = [
    ['dagsetning' => '2024-01-01', 'lysing' => 'Mánaðargjöld janúar 2024', 'upphad' => 850000, 'tegund' => 'tekjur', 'flokkur' => 'Mánaðargjöld'],
    ['dagsetning' => '2024-02-01', 'lysing' => 'Mánaðargjöld febrúar 2024', 'upphad' => 850000, 'tegund' => 'tekjur', 'flokkur' => 'Mánaðargjöld'],
    ['dagsetning' => '2024-03-01', 'lysing' => 'Mánaðargjöld mars 2024', 'upphad' => 850000, 'tegund' => 'tekjur', 'flokkur' => 'Mánaðargjöld'],
    ['dagsetning' => '2024-04-01', 'lysing' => 'Mánaðargjöld apríl 2024', 'upphad' => 850000, 'tegund' => 'tekjur', 'flokkur' => 'Mánaðargjöld'],
    ['dagsetning' => '2024-05-01', 'lysing' => 'Mánaðargjöld maí 2024', 'upphad' => 850000, 'tegund' => 'tekjur', 'flokkur' => 'Mánaðargjöld'],
    ['dagsetning' => '2024-03-15', 'lysing' => 'Sérstakt gjald - þakviðgerð', 'upphad' => 450000, 'tegund' => 'tekjur', 'flokkur' => 'Sérstök gjöld'],
];

// Gjöld
$gjold = [
    ['dagsetning' => '2024-01-05', 'lysing' => 'Rafmagn - janúar', 'upphad' => 125000, 'tegund' => 'gjold', 'flokkur' => 'Rafmagn'],
    ['dagsetning' => '2024-01-10', 'lysing' => 'Hitun - janúar', 'upphad' => 180000, 'tegund' => 'gjold', 'flokkur' => 'Hitun'],
    ['dagsetning' => '2024-01-15', 'lysing' => 'Vatn og fráveitur', 'upphad' => 85000, 'tegund' => 'gjold', 'flokkur' => 'Vatn og fráveitur'],
    ['dagsetning' => '2024-01-20', 'lysing' => 'Þrif sameignar', 'upphad' => 45000, 'tegund' => 'gjold', 'flokkur' => 'Þrif'],
    ['dagsetning' => '2024-02-05', 'lysing' => 'Rafmagn - febrúar', 'upphad' => 118000, 'tegund' => 'gjold', 'flokkur' => 'Rafmagn'],
    ['dagsetning' => '2024-03-15', 'lysing' => 'Þakviðgerð - efni og vinna', 'upphad' => 380000, 'tegund' => 'gjold', 'flokkur' => 'Viðhald'],
];

$allar_faerslur = array_merge($tekjur, $gjold);

foreach ($allar_faerslur as $faersla) {
    $wpdb->insert(
        $wpdb->prefix . 'hb_faerslur',
        $faersla,
        ['%s', '%s', '%d', '%s', '%s']
    );
}
echo "✅ Bætti við " . count($allar_faerslur) . " færslum\n\n";

echo "🎉 Demo gögn tilbúin!\n";
echo "🌐 Farðu á: http://localhost:8080/wp-admin/admin.php?page=husfelag-bokhald\n";
echo "👤 Notandi: admin\n";
echo "🔑 Lykilorð: demo123\n\n";
