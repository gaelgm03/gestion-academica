<?php
/**
 * XlsxExporter - Generador de archivos XLSX sin dependencias externas
 * 
 * Genera archivos Excel (.xlsx) usando el formato Office Open XML
 * que es un archivo ZIP con estructura XML interna.
 */
class XlsxExporter {
    private $sheets = [];
    private $currentSheet = 0;
    private $sharedStrings = [];
    private $stringIndex = 0;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->addSheet('Hoja1');
    }
    
    /**
     * Agregar una nueva hoja
     * @param string $name Nombre de la hoja
     * @return int Índice de la hoja
     */
    public function addSheet($name) {
        $this->sheets[] = [
            'name' => $this->sanitizeSheetName($name),
            'data' => [],
            'colWidths' => []
        ];
        return count($this->sheets) - 1;
    }
    
    /**
     * Seleccionar hoja activa
     * @param int $index Índice de la hoja
     */
    public function setActiveSheet($index) {
        if (isset($this->sheets[$index])) {
            $this->currentSheet = $index;
        }
    }
    
    /**
     * Agregar fila de datos
     * @param array $row Datos de la fila
     * @param bool $isHeader Si es fila de encabezado
     */
    public function addRow($row, $isHeader = false) {
        $this->sheets[$this->currentSheet]['data'][] = [
            'cells' => $row,
            'isHeader' => $isHeader
        ];
        
        // Actualizar anchos de columna
        foreach ($row as $colIndex => $value) {
            $len = mb_strlen((string)$value) + 2;
            if (!isset($this->sheets[$this->currentSheet]['colWidths'][$colIndex]) || 
                $this->sheets[$this->currentSheet]['colWidths'][$colIndex] < $len) {
                $this->sheets[$this->currentSheet]['colWidths'][$colIndex] = min($len, 50);
            }
        }
    }
    
    /**
     * Agregar múltiples filas
     * @param array $rows Array de filas
     * @param array|null $headers Encabezados opcionales
     */
    public function addRows($rows, $headers = null) {
        if ($headers) {
            $this->addRow($headers, true);
        }
        foreach ($rows as $row) {
            $this->addRow(array_values($row));
        }
    }
    
    /**
     * Generar el archivo XLSX
     * @return string Contenido binario del archivo XLSX
     */
    public function generate() {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
        
        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('No se pudo crear el archivo XLSX');
        }
        
        // Estructura básica del XLSX
        $zip->addFromString('[Content_Types].xml', $this->getContentTypes());
        $zip->addFromString('_rels/.rels', $this->getRels());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->getWorkbookRels());
        $zip->addFromString('xl/workbook.xml', $this->getWorkbook());
        $zip->addFromString('xl/styles.xml', $this->getStyles());
        $zip->addFromString('xl/sharedStrings.xml', $this->getSharedStrings());
        
        // Agregar cada hoja
        foreach ($this->sheets as $index => $sheet) {
            $zip->addFromString("xl/worksheets/sheet" . ($index + 1) . ".xml", $this->getSheet($index));
        }
        
        $zip->close();
        
        $content = file_get_contents($tempFile);
        unlink($tempFile);
        
        return $content;
    }
    
    /**
     * Descargar el archivo directamente
     * @param string $filename Nombre del archivo
     */
    public function download($filename) {
        $content = $this->generate();
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        echo $content;
        exit();
    }
    
    // ========== MÉTODOS PRIVADOS ==========
    
    private function sanitizeSheetName($name) {
        // Máximo 31 caracteres, sin caracteres especiales
        $name = preg_replace('/[\\\\\/\?\*\[\]:\'"]/', '', $name);
        return mb_substr($name, 0, 31);
    }
    
    private function getContentTypes() {
        $sheets = '';
        foreach ($this->sheets as $index => $sheet) {
            $sheets .= '<Override PartName="/xl/worksheets/sheet' . ($index + 1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
    <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
    ' . $sheets . '
</Types>';
    }
    
    private function getRels() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
    }
    
    private function getWorkbookRels() {
        $sheets = '';
        foreach ($this->sheets as $index => $sheet) {
            $sheets .= '<Relationship Id="rId' . ($index + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . ($index + 1) . '.xml"/>';
        }
        $nextId = count($this->sheets) + 1;
        
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    ' . $sheets . '
    <Relationship Id="rId' . $nextId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
    <Relationship Id="rId' . ($nextId + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>';
    }
    
    private function getWorkbook() {
        $sheets = '';
        foreach ($this->sheets as $index => $sheet) {
            $sheets .= '<sheet name="' . $this->xmlEncode($sheet['name']) . '" sheetId="' . ($index + 1) . '" r:id="rId' . ($index + 1) . '"/>';
        }
        
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        ' . $sheets . '
    </sheets>
</workbook>';
    }
    
    private function getStyles() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font>
            <sz val="11"/>
            <name val="Calibri"/>
        </font>
        <font>
            <b/>
            <sz val="11"/>
            <name val="Calibri"/>
        </font>
    </fonts>
    <fills count="3">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="gray125"/></fill>
        <fill>
            <patternFill patternType="solid">
                <fgColor rgb="FF4472C4"/>
                <bgColor indexed="64"/>
            </patternFill>
        </fill>
    </fills>
    <borders count="1">
        <border>
            <left/><right/><top/><bottom/><diagonal/>
        </border>
    </borders>
    <cellStyleXfs count="1">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
    </cellStyleXfs>
    <cellXfs count="3">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1">
            <alignment horizontal="center" vertical="center"/>
        </xf>
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1">
            <alignment wrapText="1"/>
        </xf>
    </cellXfs>
</styleSheet>';
    }
    
    private function getSharedStrings() {
        // Recolectar todos los strings
        $this->sharedStrings = [];
        $this->stringIndex = 0;
        
        foreach ($this->sheets as $sheet) {
            foreach ($sheet['data'] as $row) {
                foreach ($row['cells'] as $cell) {
                    if (!is_numeric($cell) && $cell !== null && $cell !== '') {
                        $strVal = (string)$cell;
                        if (!isset($this->sharedStrings[$strVal])) {
                            $this->sharedStrings[$strVal] = $this->stringIndex++;
                        }
                    }
                }
            }
        }
        
        $strings = '';
        foreach (array_keys($this->sharedStrings) as $str) {
            $strings .= '<si><t>' . $this->xmlEncode($str) . '</t></si>';
        }
        
        $count = count($this->sharedStrings);
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . $count . '" uniqueCount="' . $count . '">
    ' . $strings . '
</sst>';
    }
    
    private function getSheet($sheetIndex) {
        $sheet = $this->sheets[$sheetIndex];
        
        // Columnas
        $cols = '';
        foreach ($sheet['colWidths'] as $colIndex => $width) {
            $colNum = $colIndex + 1;
            $cols .= '<col min="' . $colNum . '" max="' . $colNum . '" width="' . $width . '" customWidth="1"/>';
        }
        
        // Filas
        $rows = '';
        $rowNum = 1;
        foreach ($sheet['data'] as $row) {
            $cells = '';
            $colIndex = 0;
            foreach ($row['cells'] as $cell) {
                $cellRef = $this->columnLetter($colIndex) . $rowNum;
                $style = $row['isHeader'] ? ' s="1"' : '';
                
                if ($cell === null || $cell === '') {
                    $cells .= '<c r="' . $cellRef . '"' . $style . '/>';
                } elseif (is_numeric($cell)) {
                    $cells .= '<c r="' . $cellRef . '"' . $style . '><v>' . $cell . '</v></c>';
                } else {
                    $strIndex = $this->sharedStrings[(string)$cell] ?? 0;
                    $cells .= '<c r="' . $cellRef . '" t="s"' . $style . '><v>' . $strIndex . '</v></c>';
                }
                $colIndex++;
            }
            $rows .= '<row r="' . $rowNum . '">' . $cells . '</row>';
            $rowNum++;
        }
        
        // Calcular rango de datos
        $maxRow = count($sheet['data']);
        $maxCol = 0;
        foreach ($sheet['data'] as $row) {
            $maxCol = max($maxCol, count($row['cells']));
        }
        $dimension = 'A1:' . $this->columnLetter($maxCol - 1) . $maxRow;
        
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <dimension ref="' . $dimension . '"/>
    <cols>' . $cols . '</cols>
    <sheetData>' . $rows . '</sheetData>
</worksheet>';
    }
    
    private function columnLetter($index) {
        $letter = '';
        while ($index >= 0) {
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = floor($index / 26) - 1;
        }
        return $letter;
    }
    
    private function xmlEncode($str) {
        return htmlspecialchars((string)$str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
    
    // ========== MÉTODOS ESTÁTICOS DE UTILIDAD ==========
    
    /**
     * Crear XLSX rápidamente desde un array de datos
     * @param array $data Datos
     * @param array|null $headers Encabezados
     * @param string $sheetName Nombre de la hoja
     * @return string Contenido binario XLSX
     */
    public static function fromArray($data, $headers = null, $sheetName = 'Datos') {
        $xlsx = new self();
        $xlsx->sheets[0]['name'] = $xlsx->sanitizeSheetName($sheetName);
        $xlsx->addRows($data, $headers);
        return $xlsx->generate();
    }
    
    /**
     * Crear XLSX con múltiples hojas
     * @param array $sheetsData Array de ['name' => 'Nombre', 'headers' => [...], 'data' => [...]]
     * @return string Contenido binario XLSX
     */
    public static function fromMultipleSheets($sheetsData) {
        $xlsx = new self();
        $xlsx->sheets = []; // Limpiar hoja por defecto
        
        foreach ($sheetsData as $sheetInfo) {
            $sheetIndex = $xlsx->addSheet($sheetInfo['name'] ?? 'Hoja');
            $xlsx->setActiveSheet($sheetIndex);
            $xlsx->addRows(
                $sheetInfo['data'] ?? [],
                $sheetInfo['headers'] ?? null
            );
        }
        
        return $xlsx->generate();
    }
}
