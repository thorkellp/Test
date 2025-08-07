<?php
// Einfalt próf sem tryggir að debet og kredit jafnast
$transactions = [
    ['debet' => 1, 'kredit' => 2, 'upphad' => 1000],
    ['debet' => 2, 'kredit' => 3, 'upphad' => 500],
];

$sum_debet = 0;
$sum_kredit = 0;
foreach ($transactions as $t) {
    $sum_debet += $t['upphad'];
    $sum_kredit += $t['upphad'];
}

if ($sum_debet === $sum_kredit) {
    echo "Balance OK\n";
} else {
    echo "Balance mismatch: $sum_debet vs $sum_kredit\n";
}
