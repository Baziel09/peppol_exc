<?php
header('Content-Type: application/json');

$dir = __DIR__ . '/uploads/';
$files = glob($dir . '*.xml');
$totalExcl = 0;
$totalBTW = 0;

foreach ($files as $file) {
    $xml = simplexml_load_file($file);
    $ns = $xml->getNamespaces(true);
    $xml->registerXPathNamespace('cbc', $ns['cbc']);

    $excl = (float) $xml->xpath('//cbc:TaxExclusiveAmount')[0];
    $btw = (float) $xml->xpath('//cbc:TaxAmount')[0];

    $totalExcl += $excl;
    $totalBTW += $btw;
}

echo json_encode([
    'excl' => $totalExcl,
    'btw' => $totalBTW,
    'incl' => $totalExcl + $totalBTW
]);