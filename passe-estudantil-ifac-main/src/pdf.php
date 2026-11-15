<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/fpdf/fpdf.php';

function texto_pdf(string $texto): string
{
    $convertido = iconv('UTF-8', 'Windows-1252//TRANSLIT', $texto);
    if ($convertido === false) {
        throw new RuntimeException('Não foi possível converter o texto para o PDF.');
    }

    return $convertido;
}

function gerar_pdf_declaracao(array $aluno, string $codigo, int $emitidoEm): string
{
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->SetMargins(22, 18, 22);
    $pdf->SetAutoPageBreak(true, 18);
    $pdf->AddPage();
    $pdf->SetTitle(texto_pdf('Declaração para Passe Estudantil - IFAC'));
    $pdf->SetAuthor(texto_pdf('Instituto Federal do Acre'));

    $logo = __DIR__ . '/../public/assets/imagem/ifac.png';
    $pdf->Image($logo, 164, 15, 22);
    $pdf->SetXY(22, 16);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(130, 5, texto_pdf('MINISTÉRIO DA EDUCAÇÃO'), 0, 1);
    $pdf->SetX(22);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(130, 4, texto_pdf('Secretaria de Educação Profissional e Tecnológica'), 0, 1);
    $pdf->SetX(22);
    $pdf->Cell(130, 4, texto_pdf('Instituto Federal de Educação, Ciência e Tecnologia do Acre'), 0, 1);
    $pdf->SetX(22);
    $pdf->Cell(130, 4, texto_pdf('Coordenação de Curso — IFAC'), 0, 1);

    $pdf->SetDrawColor(26, 107, 58);
    $pdf->SetLineWidth(0.6);
    $pdf->Line(22, 45, 188, 45);
    $pdf->SetY(54);
    $pdf->SetFont('Arial', 'B', 15);
    $pdf->Cell(0, 12, texto_pdf('DECLARAÇÃO PARA AQUISIÇÃO DE PASSE ESTUDANTIL'), 0, 1, 'C');

    $pdf->SetY(76);
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 8, texto_pdf(
        'Declaramos para os devidos fins que ' . $aluno['nome'] .
        ', inscrito(a) no CPF sob o nº ' . $aluno['cpf'] .
        ', está regularmente matriculado(a) no curso de ' . $aluno['curso'] .
        ' nesta Instituição Federal de Ensino, sob a Matrícula SUAP nº ' . $aluno['matricula'] . '.'
    ), 0, 'J');
    $pdf->Ln(5);
    $pdf->MultiCell(0, 7, texto_pdf(
        'Declaramos, ainda, que o(a) referido(a) discente encontra-se com situação acadêmica ' .
        strtoupper($aluno['situacao']) . ' no SUAP/IFAC na data desta emissão.'
    ), 0, 'J');

    $data = (new DateTimeImmutable('@' . $emitidoEm))
        ->setTimezone(new DateTimeZone('America/Rio_Branco'));
    $meses = [
        1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
        5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
        9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
    ];
    $pdf->SetY(176);
    $pdf->Cell(0, 8, texto_pdf(
        'Rio Branco - AC, ' . $data->format('d') . ' de ' .
        $meses[(int) $data->format('n')] . ' de ' . $data->format('Y') . '.'
    ), 0, 1, 'R');

    $urlValidacao = getenv('APP_VALIDATION_URL') ?: 'https://coordenacao.ifac.si/validar.php';
    $pdf->SetY(198);
    $pdf->SetDrawColor(90, 110, 100);
    $pdf->Line(54, 198, 156, 198);
    $pdf->SetY(201);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 5, texto_pdf('Documento Emitido Eletronicamente'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 5, texto_pdf('Coordenação de Curso / Sistema Automatizado de Declarações — IFAC'), 0, 1, 'C');

    $pdf->SetY(224);
    $pdf->SetFillColor(246, 249, 247);
    $pdf->SetDrawColor(200, 215, 205);
    $pdf->Rect(22, 224, 166, 54, 'DF');
    $pdf->SetDrawColor(26, 107, 58);
    $pdf->SetLineWidth(1);
    $pdf->Line(22, 224, 22, 278);
    $pdf->SetXY(26, 227);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(26, 107, 58);
    $pdf->Cell(158, 4, texto_pdf('Código de Verificação (SHA-256 / HMAC-SHA-256)'), 0, 1, 'C');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetX(26);
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(158, 4, texto_pdf('Valide em: ' . $urlValidacao), 0, 1, 'C', false, $urlValidacao);
    $pdf->SetFont('Courier', 'B', 7);
    foreach (str_split($codigo, 72) as $linha) {
        $pdf->SetX(26);
        $pdf->Cell(158, 3.5, $linha, 0, 1, 'C');
    }
    $pdf->SetX(26);
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(158, 4, texto_pdf('Emitida em tempo real via integração com a API oficial do SUAP/IFAC.'), 0, 1, 'C');

    return $pdf->Output('S');
}
