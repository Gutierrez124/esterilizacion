<?php

namespace App\Support;

class XlsxWriter
{
    private const MIME = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    public static function descargar(string $nombreArchivo, array $encabezados, iterable $filas)
    {
        $contenido = self::contenido($encabezados, $filas);

        return response()->streamDownload(function () use ($contenido) {
            echo $contenido;
        }, $nombreArchivo, ['Content-Type' => self::MIME]);
    }

    public static function contenido(array $encabezados, iterable $filas): string
    {
        $hoja = self::hojaXml($encabezados, $filas);

        $ruta = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new \ZipArchive();
        $zip->open($ruta, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', self::contentTypesXml());
        $zip->addFromString('_rels/.rels', self::relsXml());
        $zip->addFromString('xl/workbook.xml', self::workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRelsXml());
        $zip->addFromString('xl/styles.xml', self::stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $hoja);
        $zip->close();

        $contenido = file_get_contents($ruta);
        @unlink($ruta);

        return $contenido;
    }

    private static function hojaXml(array $encabezados, iterable $filas): string
    {
        $filasXml = ['<row r="1">'.self::celdas($encabezados, 1, true).'</row>'];

        $numero = 2;
        foreach ($filas as $fila) {
            $filasXml[] = '<row r="'.$numero.'">'.self::celdas(is_array($fila) ? $fila : iterator_to_array($fila), $numero, false).'</row>';
            $numero++;
        }

        $anchos = '';
        foreach ($encabezados as $indice => $encabezado) {
            $ancho = min(60, max(12, mb_strlen((string) $encabezado) + 6));
            $anchos .= '<col min="'.($indice + 1).'" max="'.($indice + 1).'" width="'.$ancho.'" customWidth="1"/>';
        }

        $ref = self::columna(count($encabezados) - 1).'1';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<cols>'.$anchos.'</cols>'
            .'<sheetData>'.implode('', $filasXml).'</sheetData>'
            .'<autoFilter ref="A1:'.$ref.'"/>'
            .'</worksheet>';
    }

    private static function celdas(array $valores, int $numeroFila, bool $esEncabezado): string
    {
        $xml = '';

        foreach (array_values($valores) as $indice => $valor) {
            $ref = self::columna($indice).$numeroFila;

            if ($esEncabezado) {
                $xml .= '<c r="'.$ref.'" t="inlineStr" s="1"><is><t>'.self::texto((string) $valor).'</t></is></c>';

                continue;
            }

            if (is_int($valor) || is_float($valor)) {
                $xml .= '<c r="'.$ref.'"><v>'.$valor.'</v></c>';

                continue;
            }

            $texto = self::texto((string) ($valor ?? ''));

            if ($texto === '') {
                continue;
            }

            $xml .= '<c r="'.$ref.'" t="inlineStr"><is><t xml:space="preserve">'.$texto.'</t></is></c>';
        }

        return $xml;
    }

    private static function texto(string $valor): string
    {
        $valor = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $valor);

        return htmlspecialchars($valor ?? '', ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function columna(int $indice): string
    {
        $letra = '';
        $indice++;

        while ($indice > 0) {
            $resto = ($indice - 1) % 26;
            $letra = chr(65 + $resto).$letra;
            $indice = intdiv($indice - 1, 26);
        }

        return $letra;
    }

    private static function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private static function relsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private static function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Auditorias" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private static function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private static function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FF1F4E79"/><name val="Calibri"/></font></fonts>'
            .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFD5E8F0"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }
}

