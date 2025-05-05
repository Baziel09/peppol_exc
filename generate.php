<?php

require 'vendor/autoload.php'; // DomPDF
use Dompdf\Dompdf;

$dir = __DIR__ . '/uploads/';
$files = glob($dir . '*.xml');
$results = [];

foreach ($files as $file) {
    $xml = simplexml_load_file($file);
    $ns = $xml->getNamespaces(true);
    $xml->registerXPathNamespace('cbc', $ns['cbc']);
    $xml->registerXPathNamespace('cac', $ns['cac']);

    $invoiceNumber = (string) $xml->xpath('//cbc:ID')[0];
    $invoiceDate = (string) $xml->xpath('//cbc:IssueDate')[0];
    $exclBtw = (float) $xml->xpath('//cbc:TaxExclusiveAmount')[0];
    $btw = (float) $xml->xpath('//cbc:TaxAmount')[0];
    $clientName = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyName/cbc:Name')[0];
    $clientVat = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID')[0];

    $results[] = [$invoiceNumber, $invoiceDate, $exclBtw, $btw, $clientName, $clientVat];
}

$format = $_POST['format'];

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="invoices.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['InvoiceNumber', 'InvoiceDate', 'ExclBTW', 'BTW', 'ClientName', 'ClientVAT']);

    foreach ($results as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

if ($format === 'pdf') {
    $totalExcl = array_sum(array_column($results, 2));
    $totalBTW = array_sum(array_column($results, 3));

    $html = "<h1>Factuuroverzicht</h1>";
    $html .= "<p>Totaal excl. btw: €" . number_format($totalExcl, 2) . "</p>";
    $html .= "<p>Totaal btw: €" . number_format($totalBTW, 2) . "</p>";
    $html .= "<p>Totaal incl. btw: €" . number_format($totalExcl + $totalBTW, 2) . "</p>";

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->render();

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="invoices.pdf"');
    echo $dompdf->output();
    exit;
}

if (empty($files)) {
    die("Geen UBL-bestanden gevonden in uploads/");
}
