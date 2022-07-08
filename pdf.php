<?php

/**
 * Класс создания PDF файла
 */
class PDF
{

    private $format;
    private $orientation;
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
    private $outerblocktags;
    private $innerblocktags;
    private $inlinetags;
    private $listtags;
    private $tabletags;
    private $formtags;
    private $pageWidthInPoints;
    private $pageHeightInPoints;
    private $pageWidthInUnits;
    private $pageHeightInUnits;
    private $curPageWidthInPoints;
    private $curPageHeightInPoints;
    private $curPageWidthInUnits;
    private $curPageHeightInUnits;
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
        if (sprintf('%.1f', 1.0) != '1.0') {
            setlocale(LC_NUMERIC, 'C');
        }
        // Standard A4 format
        $this->format = 'A4';
        // Portrait
        $this->orientation = 'P';
        $this->pages = [];
        $this->scaleFactor = self::POINTS_UNIT / self::MILIMETERS_PER_INCH;
        $this->dpi = 96;
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
        $this->outerblocktags = array('DIV', 'FORM', 'CENTER', 'DL');
        $this->innerblocktags = array('P', 'BLOCKQUOTE', 'ADDRESS', 'PRE', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'DT', 'DD');
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
    private function setPageSize($format, &$orientation)
    {
        if (is_string($format)) {
            if ($format == '') {
                $format = 'A4';
            }
            $pfo = 'P';
            if (preg_match('/([0-9a-zA-Z]*)-L/i', $format, $m)) {    // e.g. A4-L = A$ landscape
                $format = $m[1];
                $pfo = 'L';
            }
            $format = $this->getPageFormat($format);
            if (!$format) {
                throw new Exception('Неверный формат страницы: ' . $format);
            } else {
                $orientation = $pfo;
            }

            $this->pageWidthInPoints = $format[0];
            $this->pageHeightInPoints = $format[1];
        } else {
            if (!$format[0] || !$format[1]) {
                throw new Exception('Неверный формат страницы: ' . $format[0] . ' ' . $format[1]);
            }
            $this->pageWidthInPoints = $format[0] * $this->scaleFactor;
            $this->pageWidthInPoints = $format[1] * $this->scaleFactor;
        }
        $this->fw = $this->pageWidthInPoints / $this->scaleFactor;
        $this->fh = $this->pageHeightInPoints / $this->scaleFactor;

        $orientation = strtolower($orientation);
        if ($orientation == 'p' or $orientation == 'portrait') {
            $orientation = 'P';
            $this->curPageWidthInPoints = $this->pageWidthInPoints;
            $this->curPageHeightInPoints = $this->pageHeightInPoints;
        } elseif ($orientation == 'l' or $orientation == 'landscape') {
            $orientation = 'L';
            $this->curPageWidthInPoints = $this->pageHeightInPoints;
            $this->curPageHeightInPoints = $this->pageWidthInPoints;
        } else {
            throw new Exception('Неверная ориентация страницы: ' . $orientation);
        }
        $this->orientation = $orientation;

        $this->curPageWidthInUnits = $this->curPageWidthInPoints / $this->scaleFactor;
        $this->curPageHeightInUnits = $this->curPageHeightInPoints / $this->scaleFactor;
    }

    private function getPageFormat($format)
    {
        switch (strtoupper($format)) {
            case '4A0': {
                    $format = array(4767.87, 6740.79);
                    break;
                }
            case '2A0': {
                    $format = array(3370.39, 4767.87);
                    break;
                }
            case 'A0': {
                    $format = array(2383.94, 3370.39);
                    break;
                }
            case 'A1': {
                    $format = array(1683.78, 2383.94);
                    break;
                }
            case 'A2': {
                    $format = array(1190.55, 1683.78);
                    break;
                }
            case 'A3': {
                    $format = array(841.89, 1190.55);
                    break;
                }
            case 'A4':
            default: {
                    $format = array(595.28, 841.89);
                    break;
                }
            case 'A5': {
                    $format = array(419.53, 595.28);
                    break;
                }
            case 'A6': {
                    $format = array(297.64, 419.53);
                    break;
                }
            case 'A7': {
                    $format = array(209.76, 297.64);
                    break;
                }
            case 'A8': {
                    $format = array(147.40, 209.76);
                    break;
                }
            case 'A9': {
                    $format = array(104.88, 147.40);
                    break;
                }
            case 'A10': {
                    $format = array(73.70, 104.88);
                    break;
                }
            case 'B0': {
                    $format = array(2834.65, 4008.19);
                    break;
                }
            case 'B1': {
                    $format = array(2004.09, 2834.65);
                    break;
                }
            case 'B2': {
                    $format = array(1417.32, 2004.09);
                    break;
                }
            case 'B3': {
                    $format = array(1000.63, 1417.32);
                    break;
                }
            case 'B4': {
                    $format = array(708.66, 1000.63);
                    break;
                }
            case 'B5': {
                    $format = array(498.90, 708.66);
                    break;
                }
            case 'B6': {
                    $format = array(354.33, 498.90);
                    break;
                }
            case 'B7': {
                    $format = array(249.45, 354.33);
                    break;
                }
            case 'B8': {
                    $format = array(175.75, 249.45);
                    break;
                }
            case 'B9': {
                    $format = array(124.72, 175.75);
                    break;
                }
            case 'B10': {
                    $format = array(87.87, 124.72);
                    break;
                }
            case 'C0': {
                    $format = array(2599.37, 3676.54);
                    break;
                }
            case 'C1': {
                    $format = array(1836.85, 2599.37);
                    break;
                }
            case 'C2': {
                    $format = array(1298.27, 1836.85);
                    break;
                }
            case 'C3': {
                    $format = array(918.43, 1298.27);
                    break;
                }
            case 'C4': {
                    $format = array(649.13, 918.43);
                    break;
                }
            case 'C5': {
                    $format = array(459.21, 649.13);
                    break;
                }
            case 'C6': {
                    $format = array(323.15, 459.21);
                    break;
                }
            case 'C7': {
                    $format = array(229.61, 323.15);
                    break;
                }
            case 'C8': {
                    $format = array(161.57, 229.61);
                    break;
                }
            case 'C9': {
                    $format = array(113.39, 161.57);
                    break;
                }
            case 'C10': {
                    $format = array(79.37, 113.39);
                    break;
                }
            case 'RA0': {
                    $format = array(2437.80, 3458.27);
                    break;
                }
            case 'RA1': {
                    $format = array(1729.13, 2437.80);
                    break;
                }
            case 'RA2': {
                    $format = array(1218.90, 1729.13);
                    break;
                }
            case 'RA3': {
                    $format = array(864.57, 1218.90);
                    break;
                }
            case 'RA4': {
                    $format = array(609.45, 864.57);
                    break;
                }
            case 'SRA0': {
                    $format = array(2551.18, 3628.35);
                    break;
                }
            case 'SRA1': {
                    $format = array(1814.17, 2551.18);
                    break;
                }
            case 'SRA2': {
                    $format = array(1275.59, 1814.17);
                    break;
                }
            case 'SRA3': {
                    $format = array(907.09, 1275.59);
                    break;
                }
            case 'SRA4': {
                    $format = array(637.80, 907.09);
                    break;
                }
            case 'LETTER': {
                    $format = array(612.00, 792.00);
                    break;
                }
            case 'LEGAL': {
                    $format = array(612.00, 1008.00);
                    break;
                }
            case 'EXECUTIVE': {
                    $format = array(521.86, 756.00);
                    break;
                }
            case 'FOLIO': {
                    $format = array(612.00, 936.00);
                    break;
                }
            case 'B': {
                    $format = array(362.83, 561.26);
                    break;
                }        //	'B' format paperback size 128x198mm
            case 'A': {
                    $format = array(314.65, 504.57);
                    break;
                }        //	'A' format paperback size 111x178mm
            case 'DEMY': {
                    $format = array(382.68, 612.28);
                    break;
                }        //	'Demy' format paperback size 135x216mm
            case 'ROYAL': {
                    $format = array(433.70, 663.30);
                    break;
                }    //	'Royal' format paperback size 153x234mm
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
