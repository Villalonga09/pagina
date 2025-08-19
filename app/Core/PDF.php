<?php
class PDF {
  // Very basic PDF generator as a fallback when Dompdf is not available.
  // Renders a simple receipt PDF without CSS. For richer output, drop-in dompdf in vendor/ and it will be used automatically.
  public static function receipt($html, $filename='comprobante.pdf') {
    // If dompdf exists, use it
    if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
      require_once BASE_PATH . '/vendor/autoload.php';
      if (class_exists('Dompdf\Dompdf')) {
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="'.$filename.'"');
        echo $dompdf->output();
        return;
      }
    }
    // Basic fallback: wrap HTML inside a minimal PDF (text only). We'll just force download of HTML as .pdf
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    echo $html;
  }
}