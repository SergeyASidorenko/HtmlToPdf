<?php

/**
 * Класс создания PDF файла
 */
class PDF
{

    private $format;
    private $pageOrientation;
    private $unit = 'mm';
    private $html;
    private $pages;
    private $scaleFactor;
    private $dpi;
    private $pdfVersion;
    private $defaultCSS;
    public const POINTS_UNIT = 72;
    public const MILIMETERS_PER_INCH = 25.4;
    private $allowedCSStags;
    private $outerBlockTags;
    private $innerBlocktags;
    private $inlinetags;
    private $listtags;
    private $tabletags;
    private $formtags;
    private $pageWidthInPoints;
    private $pageHeightInPoints;
    private $pageWidthInInches;
    private $pageHeightInInches;
    private $curPageWidthInPoints;
    private $curPageHeightInPoints;
    private $curPageWidthInInches;
    private $curPageHeightInInches;
    private $leftMargin;
    private $topMargin;
    private $rightMargin;
    private $bottomMargin;
    private $coreFonts;          // массив стандартных шрифтов
    private $fonts;              // массив задействованных шрифтов
    private $FontFiles;          // массив файлов шрифтов
    private $diffs;              // массив различий в кодировке
    private $images;             // массив использованных изображений
    private $PageLinks;          // массив ссылок
    private $links;              // массив внутренних ссылок
    public function __construct()
    {
        // Устанавлиеваем разделитель целой и дробной частей
        // в соответствии к требуемым для PDF
        if (sprintf('%.1f', 1.0) != '1.0') {
            setlocale(LC_NUMERIC, 'C');
        }
        // Устанавлиеваем стандартный формат листа A4
        $this->format = 'A4';
        // Устанавлиеваем ориентацию страницы Портрет
        $this->orientation = 'P';
        // Инициализируем массив страниц
        $this->pages = [];
        // Определяем масштаб - как количество типографских пунктов в дюйме
        // Естественный масштаб - это 72 типографских пункта в одном дюйме
        $this->scaleFactor = self::POINTS_UNIT / self::MILIMETERS_PER_INCH;
        // Разрешение изображения
        $this->dpi = 96;
        // Стандартные стили для частей HTML документа
        $this->defaultCSS = array(
            'BODY' => array(
                'FONT-FAMILY' => 'serif',
                'FONT-SIZE' => '11pt',
                'TEXT-INDENT' => '0pt',
                'LINE-HEIGHT' => 'normal',
                'MARGIN-COLLAPSE' => 'collapse', /* Custom property to collapse top/bottom margins at top/bottom of page - ignored in tables/lists */
            ),
            'P' => array(
                'MARGIN' => '1.12em 0',
            ),
            'H1' => array(
                'FONT-SIZE' => '2em',
                'FONT-WEIGHT' => 'bold',
                'MARGIN' => '0.67em 0',
                'PAGE-BREAK-AFTER' => 'avoid',
            ),
            'H2' => array(
                'FONT-SIZE' => '1.5em',
                'FONT-WEIGHT' => 'bold',
                'MARGIN' => '0.75em 0',
                'PAGE-BREAK-AFTER' => 'avoid',
            ),
            'H3' => array(
                'FONT-SIZE' => '1.17em',
                'FONT-WEIGHT' => 'bold',
                'MARGIN' => '0.83em 0',
                'PAGE-BREAK-AFTER' => 'avoid',
            ),
            'H4' => array(
                'FONT-WEIGHT' => 'bold',
                'MARGIN' => '1.12em 0',
                'PAGE-BREAK-AFTER' => 'avoid',
            ),
            'H5' => array(
                'FONT-SIZE' => '0.83em',
                'FONT-WEIGHT' => 'bold',
                'MARGIN' => '1.5em 0',
                'PAGE-BREAK-AFTER' => 'avoid',
            ),
            'H6' => array(
                'FONT-SIZE' => '0.75em',
                'FONT-WEIGHT' => 'bold',
                'MARGIN' => '1.67em 0',
                'PAGE-BREAK-AFTER' => 'avoid',
            ),
            'HR' => array(
                'COLOR' => '#888888',
                'TEXT-ALIGN' => 'center',
                'WIDTH' => '100%',
                'HEIGHT' => '0.2mm',
                'MARGIN-TOP' => '0.83em',
                'MARGIN-BOTTOM' => '0.83em',
            ),
            'PRE' => array(
                'MARGIN' => '0.83em 0',
                'FONT-FAMILY' => 'monospace',
            ),
            'S' => array(
                'TEXT-DECORATION' => 'line-through',
            ),
            'STRIKE' => array(
                'TEXT-DECORATION' => 'line-through',
            ),
            'DEL' => array(
                'TEXT-DECORATION' => 'line-through',
            ),
            'SUB' => array(
                'VERTICAL-ALIGN' => 'sub',
                'FONT-SIZE' => '55%',    /* Recommended 0.83em */
            ),
            'SUP' => array(
                'VERTICAL-ALIGN' => 'super',
                'FONT-SIZE' => '55%',    /* Recommended 0.83em */
            ),
            'U' => array(
                'TEXT-DECORATION' => 'underline',
            ),
            'INS' => array(
                'TEXT-DECORATION' => 'underline',
            ),
            'B' => array(
                'FONT-WEIGHT' => 'bold',
            ),
            'STRONG' => array(
                'FONT-WEIGHT' => 'bold',
            ),
            'I' => array(
                'FONT-STYLE' => 'italic',
            ),
            'CITE' => array(
                'FONT-STYLE' => 'italic',
            ),
            'Q' => array(
                'FONT-STYLE' => 'italic',
            ),
            'EM' => array(
                'FONT-STYLE' => 'italic',
            ),
            'VAR' => array(
                'FONT-STYLE' => 'italic',
            ),
            'SAMP' => array(
                'FONT-FAMILY' => 'monospace',
            ),
            'CODE' => array(
                'FONT-FAMILY' => 'monospace',
            ),
            'KBD' => array(
                'FONT-FAMILY' => 'monospace',
            ),
            'TT' => array(
                'FONT-FAMILY' => 'monospace',
            ),
            'SMALL' => array(
                'FONT-SIZE' => '83%',
            ),
            'BIG' => array(
                'FONT-SIZE' => '117%',
            ),
            'ACRONYM' => array(
                'FONT-SIZE' => '77%',
                'FONT-WEIGHT' => 'bold',
            ),
            'ADDRESS' => array(
                'FONT-STYLE' => 'italic',
            ),
            'BLOCKQUOTE' => array(
                'MARGIN-LEFT' => '40px',
                'MARGIN-RIGHT' => '40px',
                'MARGIN-TOP' => '1.12em',
                'MARGIN-BOTTOM' => '1.12em',
            ),
            'A' => array(
                'COLOR' => '#0000FF',
                'TEXT-DECORATION' => 'underline',
            ),
            'UL' => array(
                'MARGIN' => '0.83em 0',
                'TEXT-INDENT' => '1.3em',
            ),
            'OL' => array(
                'MARGIN' => '0.83em 0',
                'TEXT-INDENT' => '1.3em',
            ),
            'DL' => array(
                'MARGIN' => '1.67em 0',
            ),
            'DT' => array(),
            'DD' => array(
                'PADDING-LEFT' => '40px',
            ),
            'TABLE' => array(
                'MARGIN' => '0',
                'BORDER-COLLAPSE' => 'separate',
                'BORDER-SPACING' => '2px',
                'EMPTY-CELLS' => 'show',
                'LINE-HEIGHT' => '1.2',
                'VERTICAL-ALIGN' => 'middle',
            ),
            'THEAD' => array(),
            'TFOOT' => array(),
            'TH' => array(
                'FONT-WEIGHT' => 'bold',
                'TEXT-ALIGN' => 'center',
                'PADDING-LEFT' => '0.1em',
                'PADDING-RIGHT' => '0.1em',
                'PADDING-TOP' => '0.1em',
                'PADDING-BOTTOM' => '0.1em',
            ),
            'TD' => array(
                'PADDING-LEFT' => '0.1em',
                'PADDING-RIGHT' => '0.1em',
                'PADDING-TOP' => '0.1em',
                'PADDING-BOTTOM' => '0.1em',
            ),
            'IMG' => array(
                'MARGIN' => '0',
                'VERTICAL-ALIGN' => 'baseline',
            ),
            'INPUT' => array(
                'FONT-FAMILY' => 'sans-serif',
                'VERTICAL-ALIGN' => 'middle',
                'FONT-SIZE' => '0.9em',
            ),
            'SELECT' => array(
                'FONT-FAMILY' => 'sans-serif',
                'FONT-SIZE' => '0.9em',
                'VERTICAL-ALIGN' => 'middle',
            ),
            'TEXTAREA' => array(
                'FONT-FAMILY' => 'monospace',
                'FONT-SIZE' => '0.9em',
                'VERTICAL-ALIGN' => 'text-bottom',
            ),
        );
        $this->pdfVersion  = 1.7;
        $this->allowedCSStags = 'DIV|P|H1|H2|H3|H4|H5|H6|FORM|IMG|A|BODY|TABLE|HR|THEAD|TFOOT|TBODY|TH|TR|TD|UL|OL|LI|PRE|BLOCKQUOTE|ADDRESS|DL|DT|DD';
        $this->allowedCSStags .= '|SPAN|TT|I|B|BIG|SMALL|EM|STRONG|DFN|CODE|SAMP|KBD|VAR|CITE|ABBR|ACRONYM|STRIKE|S|U|DEL|INS|Q|FONT';
        $this->allowedCSStags .= '|SELECT|INPUT|TEXTAREA';
        $this->outerBlockTags = array('DIV', 'FORM', 'CENTER', 'DL');
        $this->innerBlocktags = array('P', 'BLOCKQUOTE', 'ADDRESS', 'PRE', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'DT', 'DD');
        $this->inlinetags = array('SPAN', 'TT', 'I', 'B', 'BIG', 'SMALL', 'EM', 'STRONG', 'DFN', 'CODE', 'SAMP', 'KBD', 'VAR', 'CITE', 'ABBR', 'ACRONYM', 'STRIKE', 'S', 'U', 'DEL', 'INS', 'Q', 'FONT', 'TTS', 'TTZ', 'TTA');
        $this->listtags = array('UL', 'OL', 'LI');
        $this->tabletags = array('TABLE', 'THEAD', 'TFOOT', 'TBODY', 'TFOOT', 'TR', 'TH', 'TD');
        $this->formtags = array('TEXTAREA', 'INPUT', 'SELECT');
    }

    /**
     * @param string $html
     */
    public function loadHtml($html)
    {
        $this->html = $html;
    }
    /**
     * @param string|array $format
     * @param string $pageOrientation
     */
    private function setPageSize($format, &$pageOrientation)
    {
        if (is_string($format)) {
            if ($format == '') {
                $format = 'A4';
            }
            $preferedPageOrientation = 'P';
            if (preg_match('/([0-9a-zA-Z]*)-L/i', $format, $m)) {    // например A4-L = A$ альбомная
                $format = $m[1];
                $preferedPageOrientation = 'L';
            }
            $pageDimensionsInPoints = $this->getPageDimensions($format);
            $pageOrientation = $preferedPageOrientation;
            // Устанавливаем размер страницы в соответствии с форматом
            $this->pageWidthInPoints = $pageDimensionsInPoints[0];
            $this->pageHeightInPoints = $pageDimensionsInPoints[1];
        } else {
            $pageDimensionsInPoints = $format;
            if (!$pageDimensionsInPoints[0] || !$pageDimensionsInPoints[1]) {
                throw new Exception('Неверный формат страницы: ' . $pageDimensionsInPoints[0] . ' ' . $pageDimensionsInPoints[1]);
            }
            $this->pageWidthInPoints = $pageDimensionsInPoints[0] * $this->scaleFactor;
            $this->pageWidthInPoints = $pageDimensionsInPoints[1] * $this->scaleFactor;
        }
        $this->pageWidthInInches = $this->pageWidthInPoints / $this->scaleFactor;
        $this->pageHeightInInches = $this->pageHeightInPoints / $this->scaleFactor;

        $pageOrientation = strtolower($pageOrientation);
        if ($pageOrientation == 'p' or $pageOrientation == 'portrait') {
            $pageOrientation = 'P';
            $this->curPageWidthInPoints = $this->pageWidthInPoints;
            $this->curPageHeightInPoints = $this->pageHeightInPoints;
        } elseif ($pageOrientation == 'l' or $pageOrientation == 'landscape') {
            $pageOrientation = 'L';
            $this->curPageWidthInPoints = $this->pageHeightInPoints;
            $this->curPageHeightInPoints = $this->pageWidthInPoints;
        }
        $this->pageOrientation = $pageOrientation;
        $this->curPageWidthInInches = $this->curPageWidthInPoints / $this->scaleFactor;
        $this->curPageHeightInInches = $this->curPageHeightInPoints / $this->scaleFactor;
    }

    /**
     * @return array
     */
    private function getPageDimensions($format)
    {
        switch (strtoupper($format)) {
            case '4A0': {
                    $format = [4767.87, 6740.79];
                    break;
                }
            case '2A0': {
                    $format = [3370.39, 4767.87];
                    break;
                }
            case 'A0': {
                    $format = [2383.94, 3370.39];
                    break;
                }
            case 'A1': {
                    $format = [1683.78, 2383.94];
                    break;
                }
            case 'A2': {
                    $format = [1190.55, 1683.78];
                    break;
                }
            case 'A3': {
                    $format = [841.89, 1190.55];
                    break;
                }
            case 'A4': {
                    $format = [595.28, 841.89];
                    break;
                }
            case 'A5': {
                    $format = [419.53, 595.28];
                    break;
                }
            case 'A6': {
                    $format = [297.64, 419.53];
                    break;
                }
            case 'A7': {
                    $format = [209.76, 297.64];
                    break;
                }
            case 'A8': {
                    $format = [147.40, 209.76];
                    break;
                }
            case 'A9': {
                    $format = [104.88, 147.40];
                    break;
                }
            case 'A10': {
                    $format = [73.70, 104.88];
                    break;
                }
            case 'B0': {
                    $format = [2834.65, 4008.19];
                    break;
                }
            case 'B1': {
                    $format = [2004.09, 2834.65];
                    break;
                }
            case 'B2': {
                    $format = [1417.32, 2004.09];
                    break;
                }
            case 'B3': {
                    $format = [1000.63, 1417.32];
                    break;
                }
            case 'B4': {
                    $format = [708.66, 1000.63];
                    break;
                }
            case 'B5': {
                    $format = [498.90, 708.66];
                    break;
                }
            case 'B6': {
                    $format = [354.33, 498.90];
                    break;
                }
            case 'B7': {
                    $format = [249.45, 354.33];
                    break;
                }
            case 'B8': {
                    $format = [175.75, 249.45];
                    break;
                }
            case 'B9': {
                    $format = [124.72, 175.75];
                    break;
                }
            case 'B10': {
                    $format = [87.87, 124.72];
                    break;
                }
            case 'C0': {
                    $format = [2599.37, 3676.54];
                    break;
                }
            case 'C1': {
                    $format = [1836.85, 2599.37];
                    break;
                }
            case 'C2': {
                    $format = [1298.27, 1836.85];
                    break;
                }
            case 'C3': {
                    $format = [918.43, 1298.27];
                    break;
                }
            case 'C4': {
                    $format = [649.13, 918.43];
                    break;
                }
            case 'C5': {
                    $format = [459.21, 649.13];
                    break;
                }
            case 'C6': {
                    $format = [323.15, 459.21];
                    break;
                }
            case 'C7': {
                    $format = [229.61, 323.15];
                    break;
                }
            case 'C8': {
                    $format = [161.57, 229.61];
                    break;
                }
            case 'C9': {
                    $format = [113.39, 161.57];
                    break;
                }
            case 'C10': {
                    $format = [79.37, 113.39];
                    break;
                }
            case 'RA0': {
                    $format = [2437.80, 3458.27];
                    break;
                }
            case 'RA1': {
                    $format = [1729.13, 2437.80];
                    break;
                }
            case 'RA2': {
                    $format = [1218.90, 1729.13];
                    break;
                }
            case 'RA3': {
                    $format = [864.57, 1218.90];
                    break;
                }
            case 'RA4': {
                    $format = [609.45, 864.57];
                    break;
                }
            case 'SRA0': {
                    $format = [2551.18, 3628.35];
                    break;
                }
            case 'SRA1': {
                    $format = [1814.17, 2551.18];
                    break;
                }
            case 'SRA2': {
                    $format = [1275.59, 1814.17];
                    break;
                }
            case 'SRA3': {
                    $format = [907.09, 1275.59];
                    break;
                }
            case 'SRA4': {
                    $format = [637.80, 907.09];
                    break;
                }
            case 'LETTER': {
                    $format = [612.00, 792.00];
                    break;
                }
            case 'LEGAL': {
                    $format = [612.00, 1008.00];
                    break;
                }
            case 'EXECUTIVE': {
                    $format = [521.86, 756.00];
                    break;
                }
            case 'FOLIO': {
                    $format = [612.00, 936.00];
                    break;
                }
            case 'B': {
                    $format = [362.83, 561.26];
                    break;
                }        //	'B' format paperback size 128x198mm
            case 'A': {
                    $format = [314.65, 504.57];
                    break;
                }        //	'A' format paperback size 111x178mm
            case 'DEMY': {
                    $format = [382.68, 612.28];
                    break;
                }        //	'Demy' format paperback size 135x216mm
            case 'ROYAL': {
                    $format = [433.70, 663.30];
                    break;
                }    //	'Royal' format paperback size 153x234mm
            default: {
                    $format = [595.28, 841.89];
                    break;
                }
        }
        return $format;
    }


    /**
     * @param string $filePath
     */
    public function create($filePath)
    {
        $fileContent = '';
        $fileContent .= '%PDF-' . $this->pdf_version;
        $fileContent .= '%' . chr(226) . chr(227) . chr(207) . chr(211);
        file_put_contents($filePath, $fileContent);
    }
}
