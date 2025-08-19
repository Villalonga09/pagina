<?php
class PDF {
  public static function receipt($html, $filename='comprobante.pdf') {
    $pdfContent = null;
    if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
      require_once BASE_PATH . '/vendor/autoload.php';
      if (class_exists('Dompdf\Dompdf')) {
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
      }
    }
    if ($pdfContent === null) {
      // Remove style/script blocks to avoid exposing raw CSS when Dompdf is absent
      $clean = preg_replace('#<(style|script)[^>]*>.*?</\\1>#si', '', $html);
      $text  = trim(strip_tags($clean));
      $pdfContent = self::basicTextPdf($text);
    }
    if (empty($pdfContent)) {
      throw new RuntimeException('No PDF generated');
    }
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="'.$filename.'"');
    echo $pdfContent;
  }

  private static function basicTextPdf($text) {
    $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    $content = "BT /F1 12 Tf 50 750 Td (".$text.") Tj ET";
    $objects = [];
    $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
    $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
    $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>";
    $objects[] = "<< /Length ".strlen($content)." >>\nstream\n".$content."\nendstream";
    $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
    $pdf = "%PDF-1.4\n";
    $offsets = [];
    foreach ($objects as $i => $obj) {
      $offsets[$i+1] = strlen($pdf);
      $pdf .= ($i+1)." 0 obj\n".$obj."\nendobj\n";
    }
    $xrefPos = strlen($pdf);
    $pdf .= "xref\n0 ".(count($objects)+1)."\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i=1; $i<=count($objects); $i++) {
      $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= "trailer << /Size ".(count($objects)+1)." /Root 1 0 R >>\nstartxref\n".$xrefPos."\n%%EOF";
    return $pdf;
  }
}
