<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;

include 'conexao.php';

$html = "<h1>Relatório de Observações</h1>";
$res = $pdo->query("SELECT * FROM observacoes ORDER BY data DESC LIMIT 10");

while ($row = $res->fetch()) {
    $html .= "<p><b>{$row['data']}</b> - {$row['local']} - Nível: {$row['nivel_agua']}</p>";
}

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream("relatorio.pdf");

