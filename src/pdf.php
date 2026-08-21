<?php
declare(strict_types=1);

// Esconde warnings/deprecateds desta lib antiga para não quebrar o JSON
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);

require_once __DIR__ . '/vendor/fpdf/fpdf.php';

/**
 * Gera o PDF da declaração de passe estudantil e salva em storage/declaracoes.
 * Retorna o caminho absoluto do arquivo salvo.
 */
function gerar_pdf_declaracao(array $aluno, string $protocolo): string
{
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetMargins(20, 20, 20);
    
    // Cabeçalho IFAC
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, mb_convert_encoding('INSTITUTO FEDERAL DO ACRE - IFAC', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, mb_convert_encoding('Pró-Reitoria de Ensino', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Título
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, mb_convert_encoding('DECLARAÇÃO PARA PASSE ESTUDANTIL', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
    $pdf->Ln(15);
    
    // Corpo do texto
    $pdf->SetFont('Arial', '', 12);
    $texto = "Declaramos, para fins de obtenção/renovação de passe estudantil, que o(a) aluno(a) " . 
             strtoupper($aluno['nome']) . ", portador(a) da matrícula nº " . $aluno['matricula'] . 
             ", encontra-se regularmente matriculado(a) no curso de " . $aluno['curso'] . 
             ", cursando o " . $aluno['semestre'] . "º semestre no ano letivo atual.";
             
    $pdf->MultiCell(0, 8, mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8'), 0, 'J');
    $pdf->Ln(15);
    
    $texto2 = "Esta declaração foi solicitada eletronicamente através do Portal do Estudante e possui validade apenas se verificada através do código de autenticidade.";
    $pdf->MultiCell(0, 8, mb_convert_encoding($texto2, 'ISO-8859-1', 'UTF-8'), 0, 'J');
    $pdf->Ln(20);
    
    // Data
    $meses = ['', 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
    $dataAtual = date('d') . ' de ' . $meses[(int)date('n')] . ' de ' . date('Y');
    
    $pdf->Cell(0, 10, mb_convert_encoding('Rio Branco - AC, ' . $dataAtual, 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
    $pdf->Ln(30);
    
    // Assinatura digital (Código de validação)
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 5, mb_convert_encoding('CÓDIGO DE AUTENTICIDADE:', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
    
    $pdf->SetFont('Courier', 'B', 14);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(0, 10, $protocolo, 0, 1, 'C', true);
    
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(0, 5, mb_convert_encoding('Para verificar a autenticidade deste documento, acesse:', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $link = 'http://' . $host . '/passe-estudantil-ifac/public/validar.php';
    $pdf->SetTextColor(0, 0, 255);
    $pdf->Cell(0, 5, $link, 0, 1, 'C', false, $link);
    
    // Salvar o arquivo
    $caminhoArquivo = __DIR__ . '/../storage/declaracoes/' . $protocolo . '.pdf';
    $pdf->Output('F', $caminhoArquivo);
    
    return $caminhoArquivo;
}
