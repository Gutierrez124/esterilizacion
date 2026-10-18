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

    public static function descargarReporte(string $nombreArchivo, array $reporte)
    {
        $contenido = self::contenidoReporte($reporte);

        return response()->streamDownload(function () use ($contenido) {
            echo $contenido;
        }, $nombreArchivo, ['Content-Type' => self::MIME]);
    }

    public static function contenido(array $encabezados, iterable $filas): string
    {
        return self::zip(self::hojaXml($encabezados, $filas), 'Auditorias');
    }

    public static function contenidoReporte(array $reporte): string
    {
        return self::zip(self::hojaReporteXml($reporte), (string) ($reporte['hoja'] ?? 'Reporte'));
    }

    private static function zip(string $hoja, string $nombreHoja): string
    {
        $ruta = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new \ZipArchive();
        $zip->open($ruta, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', self::contentTypesXml());
        $zip->addFromString('_rels/.rels', self::relsXml());
        $zip->addFromString('xl/workbook.xml', self::workbookXml($nombreHoja));
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

        return self::hojaBaseXml($anchos, implode('', $filasXml), '<autoFilter ref="A1:'.$ref.'"/>');
    }

    private static function hojaBaseXml(string $columnas, string $datos, string $extra = '', string $apertura = ''): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .$apertura
            .'<cols>'.$columnas.'</cols>'
            .'<sheetData>'.$datos.'</sheetData>'
            .$extra
            .'</worksheet>';
    }

    private static function hojaReporteXml(array $reporte): string
    {
        $encabezados = array_values($reporte['encabezados'] ?? []);
        $totalColumnas = max(1, count($encabezados));
        $ultimaColumna = self::columna($totalColumnas - 1);

        $filasXml = [];
        $fusiones = [];
        $numero = 1;

        $titulo = trim((string) ($reporte['titulo'] ?? ''));
        if ($titulo !== '') {
            $filasXml[] = '<row r="'.$numero.'" ht="24" customHeight="1">'.self::rangoCeldas($numero, 0, $totalColumnas - 1, 2, $titulo).'</row>';
            $fusiones[] = 'A'.$numero.':'.$ultimaColumna.$numero;
            $numero++;
        }

        foreach ((array) ($reporte['subtitulos'] ?? []) as $subtitulo) {
            $subtitulo = trim((string) $subtitulo);
            if ($subtitulo === '') {
                continue;
            }

            $filasXml[] = '<row r="'.$numero.'" ht="18" customHeight="1">'.self::rangoCeldas($numero, 0, $totalColumnas - 1, 3, $subtitulo).'</row>';
            $fusiones[] = 'A'.$numero.':'.$ultimaColumna.$numero;
            $numero++;
        }

        $estilosGrupo = ['proceso' => 4, 'roja' => 5, 'azul' => 6, 'verde' => 7, 'observacion' => 8];
        $indiceGrupo = 0;
        $filaGrupos = '';

        foreach (array_values($reporte['grupos'] ?? []) as $grupo) {
            $columnas = max(1, (int) ($grupo['columnas'] ?? 1));
            $primera = $indiceGrupo;
            $ultima = min($totalColumnas - 1, $indiceGrupo + $columnas - 1);
            $estilo = $estilosGrupo[$grupo['estilo'] ?? 'proceso'] ?? 4;

            $filaGrupos .= self::rangoCeldas($numero, $primera, $ultima, $estilo, (string) ($grupo['titulo'] ?? ''), $primera);

            if ($ultima > $primera) {
                $fusiones[] = self::columna($primera).$numero.':'.self::columna($ultima).$numero;
            }

            $indiceGrupo = $ultima + 1;
        }

        if ($filaGrupos !== '') {
            $filasXml[] = '<row r="'.$numero.'" ht="20" customHeight="1">'.$filaGrupos.'</row>';
            $numero++;
        }

        $filaEncabezados = $numero;
        $filasXml[] = '<row r="'.$numero.'" ht="32" customHeight="1">'.self::celdasEstilizadas($encabezados, $numero, 9).'</row>';
        $numero++;

        $filas = [];
        foreach (($reporte['filas'] ?? []) as $fila) {
            $filas[] = is_array($fila) ? $fila : iterator_to_array($fila);
        }

        foreach ($filas as $fila) {
            $filasXml[] = '<row r="'.$numero.'">'.self::celdasEstilizadas(array_values($fila), $numero, 10).'</row>';
            $numero++;
        }

        $anchosReporte = array_values((array) ($reporte['anchos'] ?? []));
        $anchos = '';
        for ($indice = 0; $indice < $totalColumnas; $indice++) {
            $ancho = $anchosReporte[$indice] ?? min(60, max(12, mb_strlen((string) ($encabezados[$indice] ?? '')) + 6));
            $anchos .= '<col min="'.($indice + 1).'" max="'.($indice + 1).'" width="'.$ancho.'" customWidth="1"/>';
        }

        $extra = '';
        if ($filas !== []) {
            $extra .= '<autoFilter ref="A'.$filaEncabezados.':'.$ultimaColumna.($numero - 1).'"/>';
        }
        if ($fusiones !== []) {
            $extra .= '<mergeCells count="'.count($fusiones).'">';
            foreach ($fusiones as $fusion) {
                $extra .= '<mergeCell ref="'.$fusion.'"/>';
            }
            $extra .= '</mergeCells>';
        }
        $extra .= '<printOptions horizontalCentered="1"/>';
        $extra .= '<pageMargins left="0.4" right="0.4" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>';
        $extra .= '<pageSetup paperSize="9" orientation="landscape" fitToWidth="1" fitToHeight="0"/>';

        $apertura = '<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>'
            .'<sheetViews><sheetView workbookViewId="0">'
            .'<pane ySplit="'.$filaEncabezados.'" topLeftCell="A'.($filaEncabezados + 1).'" activePane="bottomLeft" state="frozen"/>'
            .'</sheetView></sheetViews>';

        return self::hojaBaseXml($anchos, implode('', $filasXml), $extra, $apertura);
    }

    private static function rangoCeldas(int $fila, int $primera, int $ultima, int $estilo, string $texto, ?int $colValor = null): string
    {
        $xml = '';
        $colValor = $colValor ?? $primera;

        for ($indice = $primera; $indice <= $ultima; $indice++) {
            $xml .= self::celdaReporte(self::columna($indice).$fila, $indice === $colValor ? $texto : '', $estilo);
        }

        return $xml;
    }

    private static function celdasEstilizadas(array $valores, int $numeroFila, int $estilo): string
    {
        $xml = '';

        foreach ($valores as $indice => $valor) {
            $xml .= self::celdaReporte(self::columna($indice).$numeroFila, $valor, $estilo);
        }

        return $xml;
    }

    private static function celdaReporte(string $ref, mixed $valor, int $estilo): string
    {
        if (is_array($valor)) {
            $estilo = (int) ($valor['s'] ?? $estilo);
            $valor = $valor['v'] ?? null;
        }

        if (is_int($valor) || is_float($valor)) {
            return '<c r="'.$ref.'" s="'.$estilo.'"><v>'.$valor.'</v></c>';
        }

        $texto = self::texto((string) ($valor ?? ''));

        if ($texto === '') {
            return '<c r="'.$ref.'" s="'.$estilo.'"/>';
        }

        return '<c r="'.$ref.'" s="'.$estilo.'" t="inlineStr"><is><t xml:space="preserve">'.$texto.'</t></is></c>';
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

    private static function workbookXml(string $nombreHoja = 'Auditorias'): string
    {
        $nombreHoja = str_replace(['"', '<', '>'], '', $nombreHoja);
        $nombreHoja = $nombreHoja !== '' ? $nombreHoja : 'Auditorias';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="'.$nombreHoja.'" sheetId="1" r:id="rId1"/></sheets>'
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
        $fuentes = [
            '<font><sz val="11"/><name val="Calibri"/></font>',
            '<font><b/><sz val="11"/><color rgb="FF1F4E79"/><name val="Calibri"/></font>',
            '<font><b/><sz val="14"/><color rgb="FF1F4E79"/><name val="Calibri"/></font>',
            '<font><i/><sz val="11"/><color rgb="FF595959"/><name val="Calibri"/></font>',
            '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>',
            '<font><b/><sz val="11"/><color rgb="FF7F3F00"/><name val="Calibri"/></font>',
        ];

        $rellenos = [
            '<fill><patternFill patternType="none"/></fill>',
            '<fill><patternFill patternType="gray125"/></fill>',
            '<fill><patternFill patternType="solid"><fgColor rgb="FFD5E8F0"/><bgColor indexed="64"/></patternFill></fill>',
            '<fill><patternFill patternType="solid"><fgColor rgb="FF1F4E79"/><bgColor indexed="64"/></patternFill></fill>',
            '<fill><patternFill patternType="solid"><fgColor rgb="FFC0504D"/><bgColor indexed="64"/></patternFill></fill>',
            '<fill><patternFill patternType="solid"><fgColor rgb="FF4F81BD"/><bgColor indexed="64"/></patternFill></fill>',
            '<fill><patternFill patternType="solid"><fgColor rgb="FF70AD47"/><bgColor indexed="64"/></patternFill></fill>',
            '<fill><patternFill patternType="solid"><fgColor rgb="FFFFD966"/><bgColor indexed="64"/></patternFill></fill>',
        ];

        $bordes = [
            '<border><left/><right/><top/><bottom/><diagonal/></border>',
            '<border><left style="thin"><color indexed="64"/></left><right style="thin"><color indexed="64"/></right><top style="thin"><color indexed="64"/></top><bottom style="thin"><color indexed="64"/></bottom><diagonal/></border>',
        ];

        $estilos = [
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>',
            '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>',
            '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>',
            '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>',
            '<xf numFmtId="0" fontId="4" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>',
            '<xf numFmtId="0" fontId="4" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>',
            '<xf numFmtId="0" fontId="4" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>',
            '<xf numFmtId="0" fontId="4" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>',
            '<xf numFmtId="0" fontId="5" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>',
            '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>',
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="top" wrapText="1"/></xf>',
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>',
        ];

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="'.count($fuentes).'">'.implode('', $fuentes).'</fonts>'
            .'<fills count="'.count($rellenos).'">'.implode('', $rellenos).'</fills>'
            .'<borders count="'.count($bordes).'">'.implode('', $bordes).'</borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="'.count($estilos).'">'.implode('', $estilos).'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }
}

