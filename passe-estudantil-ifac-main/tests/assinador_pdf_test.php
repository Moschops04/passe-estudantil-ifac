<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/assinador_pdf.php';

final class AssinadorPdfTeste implements AssinadorPdfDiretor
{
    public string $pdfRecebido = '';

    public function assinar(string $pdf): string
    {
        $this->pdfRecebido = $pdf;
        return $pdf . "\n% assinatura de teste";
    }
}

$assinador = new AssinadorPdfTeste();
$pdfTeste = "%PDF-1.4\n%%EOF";
$resultado = assinar_pdf_diretor($pdfTeste, $assinador);
if ($assinador->pdfRecebido !== $pdfTeste || $resultado !== $pdfTeste . "\n% assinatura de teste") {
    throw new RuntimeException('O ponto de integração do assinador não encaminhou o PDF corretamente.');
}

$falhouComPdfInvalido = false;
try {
    assinar_pdf_diretor('inválido', $assinador);
} catch (InvalidArgumentException) {
    $falhouComPdfInvalido = true;
}
if (!$falhouComPdfInvalido) {
    throw new RuntimeException('O ponto de integração aceitou PDF de entrada inválido.');
}

echo "Testes do ponto de integração de assinatura OK.\n";
