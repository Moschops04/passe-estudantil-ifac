<?php
declare(strict_types=1);

interface AssinadorPdfDiretor
{
    /**
     * Envia o PDF à API institucional e devolve o PDF assinado digitalmente.
     */
    public function assinar(string $pdf): string;
}

function assinar_pdf_diretor(string $pdf, AssinadorPdfDiretor $assinador): string
{
    if (substr($pdf, 0, 5) !== '%PDF-' || strpos($pdf, '%%EOF') === false) {
        throw new InvalidArgumentException('O documento de entrada não é um PDF válido.');
    }

    $pdfAssinado = $assinador->assinar($pdf);
    if (substr($pdfAssinado, 0, 5) !== '%PDF-' || strpos($pdfAssinado, '%%EOF') === false) {
        throw new RuntimeException('A API de assinatura não retornou um PDF válido.');
    }

    return $pdfAssinado;
}
