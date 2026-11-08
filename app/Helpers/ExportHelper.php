<?php
namespace App\Helpers;

/**
 * Export Helper Class
 * توابع کمکی برای خروجی داده‌ها (Excel, PDF, CSV)
 */
class ExportHelper
{
    /**
     * Export data to CSV
     * 
     * @param array $data Array of associative arrays
     * @param string $filename Filename without extension
     * @param array $headers Custom column headers (optional)
     * @return void (sends file to browser)
     */
    public static function toCsv($data, $filename = 'export', $headers = [])
    {
        if (empty($data)) {
            throw new \Exception('No data to export');
        }

        $filename = self::sanitizeFilename($filename) . '_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, "\xEF\xBB\xBF");

        // Write headers
        $columnHeaders = !empty($headers) ? $headers : array_keys($data[0]);
        fputcsv($output, $columnHeaders);

        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    /**
     * Export data to Excel (simple CSV-based)
     * 
     * @param array $data Array of associative arrays
     * @param string $filename Filename without extension
     * @param string $sheetName Sheet name
     * @return void
     */
    public static function toExcel($data, $filename = 'export', $sheetName = 'Sheet1')
    {
        // For a real Excel export, use PhpSpreadsheet
        // This is a simple CSV-based approach
        $filename = self::sanitizeFilename($filename) . '_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, "\xEF\xBB\xBF");

        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Export data to JSON
     * 
     * @param array $data
     * @param string $filename
     * @return void
     */
    public static function toJson($data, $filename = 'export')
    {
        $filename = self::sanitizeFilename($filename) . '_' . date('Y-m-d') . '.json';

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Export data to XML
     * 
     * @param array $data
     * @param string $filename
     * @param string $rootElement
     * @return void
     */
    public static function toXml($data, $filename = 'export', $rootElement = 'root')
    {
        $filename = self::sanitizeFilename($filename) . '_' . date('Y-m-d') . '.xml';

        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $rootElement . '/>');

        foreach ($data as $item) {
            $child = $xml->addChild('item');
            foreach ($item as $key => $value) {
                $child->addChild($key, htmlspecialchars((string)$value));
            }
        }

        echo $xml->asXML();
        exit;
    }

    /**
     * Export data to PDF (simple HTML-based)
     * 
     * @param string $html HTML content
     * @param string $filename
     * @param string $orientation portrait|landscape
     * @param string $paperSize A4, Letter, etc.
     * @return void
     */
    public static function toPdf($html, $filename = 'export', $orientation = 'portrait', $paperSize = 'A4')
    {
        // This requires a PDF library like DomPDF or TCPDF
        // This is a placeholder that shows how to implement it
        if (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper($paperSize, $orientation);
            $dompdf->render();
            
            $filename = self::sanitizeFilename($filename) . '_' . date('Y-m-d') . '.pdf';
            $dompdf->stream($filename, ['Attachment' => 1]);
            exit;
        }

        // Fallback: print-friendly HTML
        $filename = self::sanitizeFilename($filename) . '_' . date('Y-m-d') . '.html';
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo '<!DOCTYPE html><html dir="rtl"><head><meta charset="UTF-8"><title>Print</title>';
        echo '<style>body{font-family:Tahoma;direction:rtl}@media print{@page{size:' . $paperSize . ' ' . $orientation . ';margin:10mm}}</style>';
        echo '</head><body>' . $html . '</body></html>';
        exit;
    }

    /**
     * Generate print-friendly HTML table
     * 
     * @param array $data
     * @param array $columns Column definitions [['key'=>'field','title'=>'Title']]
     * @param string $title Report title
     * @return string HTML
     */
    public static function generateHtmlTable($data, $columns = [], $title = '')
    {
        if (empty($data)) {
            return '<p>No data available</p>';
        }

        $html = '';

        if ($title) {
            $html .= '<h2 style="text-align:center;margin-bottom:20px">' . htmlspecialchars($title) . '</h2>';
        }

        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%;border-collapse:collapse;direction:rtl">';
        $html .= '<thead><tr style="background:#f0f0f0">';

        // Headers
        $keys = !empty($columns) ? array_column($columns, 'key') : array_keys($data[0]);
        $titles = !empty($columns) ? array_column($columns, 'title') : $keys;

        foreach ($titles as $title) {
            $html .= '<th>' . htmlspecialchars($title) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        // Data rows
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($keys as $key) {
                $value = $row[$key] ?? '';
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        $html .= '<p style="text-align:left;margin-top:10px">' . date('Y/m/d H:i') . ' | ' . count($data) . ' records</p>';

        return $html;
    }

    /**
     * Export multiple sheets to Excel (requires PhpSpreadsheet)
     * 
     * @param array $sheets ['Sheet Name' => [data]]
     * @param string $filename
     * @return void
     */
    public static function toMultiSheetExcel($sheets, $filename = 'export')
    {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            throw new \Exception('PhpSpreadsheet library is required for multi-sheet Excel export');
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $firstSheet = true;

        foreach ($sheets as $sheetName => $data) {
            if ($firstSheet) {
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle($sheetName);
                $firstSheet = false;
            } else {
                $sheet = $spreadsheet->createSheet();
                $sheet->setTitle($sheetName);
            }

            if (!empty($data)) {
                // Headers
                $col = 'A';
                foreach (array_keys($data[0]) as $header) {
                    $sheet->setCellValue($col . '1', $header);
                    $col++;
                }

                // Data
                $row = 2;
                foreach ($data as $dataRow) {
                    $col = 'A';
                    foreach ($dataRow as $value) {
                        $sheet->setCellValue($col . $row, $value);
                        $col++;
                    }
                    $row++;
                }
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $filename = self::sanitizeFilename($filename) . '_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
    }

    /**
     * Download a file
     * 
     * @param string $filePath Full path to file
     * @param string|null $filename Custom filename
     * @return void
     */
    public static function downloadFile($filePath, $filename = null)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('File not found: ' . $filePath);
        }

        $filename = $filename ?? basename($filePath);
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath);

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $fileSize);
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($filePath);
        exit;
    }

    /**
     * Stream a large file for download
     * 
     * @param string $filePath
     * @param string|null $filename
     * @return void
     */
    public static function streamDownload($filePath, $filename = null)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('File not found');
        }

        $filename = $filename ?? basename($filePath);
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath);

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $fileSize);
        header('Pragma: no-cache');
        header('Expires: 0');

        $handle = fopen($filePath, 'rb');
        while (!feof($handle)) {
            echo fread($handle, 8192);
            ob_flush();
            flush();
        }
        fclose($handle);
        exit;
    }

    /**
     * Sanitize filename for download
     * 
     * @param string $filename
     * @return string
     */
    private static function sanitizeFilename($filename)
    {
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9_\-\x{0600}-\x{06FF}\s]/u', '', $filename);
        // Replace spaces with underscore
        $filename = preg_replace('/\s+/', '_', $filename);
        // Limit length
        if (mb_strlen($filename) > 100) {
            $filename = mb_substr($filename, 0, 100);
        }

        return $filename ?: 'export';
    }
}