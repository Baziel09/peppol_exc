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
    
    // Write CSV headers with the $escape parameter
    fputcsv($out, ['InvoiceNumber', 'InvoiceDate', 'ExclBTW', 'BTW', 'ClientName', 'ClientVAT'], ',', '"', '\\');

    // Write CSV data with the $escape parameter
    foreach ($results as $row) {
        fputcsv($out, $row, ',', '"', '\\');
    }
    fclose($out);
    exit;
}

if ($format === 'pdf') {
    $totalExcl = array_sum(array_column($results, 2));
    $totalBTW = array_sum(array_column($results, 3));
    $totalIncl = $totalExcl + $totalBTW;

    $html = "
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; font-size: 12px; text-align: left; }
        th { background-color: #f0f0f0; }
        .summary { margin-bottom: 20px; }
    </style>

    <h1>Factuuroverzicht</h1>
    <div class='summary'>
        <p><strong>Totaal excl. BTW:</strong> €" . number_format($totalExcl, 2) . "</p>
        <p><strong>Totaal BTW:</strong> €" . number_format($totalBTW, 2) . "</p>
        <p><strong>Totaal incl. BTW:</strong> €" . number_format($totalIncl, 2) . "</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Invoice Number</th>
                <th>Invoice Date</th>
                <th>Excl. BTW (€)</th>
                <th>BTW (%)</th>
                <th>Client Name</th>
                <th>Client VAT</th>
            </tr>
        </thead>
        <tbody>";

    foreach ($results as [$num, $date, $excl, $btw, $client, $vat]) {
        $html .= "
            <tr>
                <td>$num</td>
                <td>$date</td>
                <td>" . number_format($excl, 2) . "</td>
                <td>" . $btw . '%' . "</td>
                <td>$client</td>
                <td>$vat</td>
            </tr>";
    }

    $html .= "</tbody></table>";

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="invoices.pdf"');
    echo $dompdf->output();
    exit;
}
