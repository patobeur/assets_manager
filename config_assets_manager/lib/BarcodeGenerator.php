<?php

class BarcodeGenerator
{
    private $code;
    private $codeset = 'B';
    private $barcode_array = [];

    private $code128_codes = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112'
    ];

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function getBarcode()
    {
        $this->validateCode();
        $this->setBarcodeArray();
        return $this->getSVG();
    }

    private function validateCode()
    {
        // For this simple generator, we'll only support Code 128 B, which covers ASCII 32-127
        if (!preg_match('/^[\x20-\x7F]+$/', $this->code)) {
            throw new \Exception('Invalid characters in barcode data.');
        }
    }

    private function setBarcodeArray()
    {
        $this->barcode_array['start'] = 104; // Start B
        $sum = 104;
        $weight = 1;

        for ($i = 0; $i < strlen($this->code); $i++) {
            $char_val = ord($this->code[$i]) - 32;
            $this->barcode_array[] = $char_val;
            $sum += ($char_val * $weight);
            $weight++;
        }

        $this->barcode_array['checksum'] = $sum % 103;
        $this->barcode_array['stop'] = 106;
    }

    private function getSVG()
    {
        $width = 200;
        $height = 50;
        $bar_width = 1;

        $svg = '<svg width="' . $width . '" height="' . $height . '" version="1.1" xmlns="http://www.w3.org/2000/svg">';
        $x = 0;

        foreach ($this->barcode_array as $code_index) {
            $pattern = $this->code128_codes[$code_index];
            $is_bar = true;
            foreach (str_split($pattern) as $bar_size) {
                if ($is_bar) {
                    $svg .= '<rect x="' . $x . '" y="0" width="' . ($bar_size * $bar_width) . '" height="' . $height . '" />';
                }
                $x += ($bar_size * $bar_width);
                $is_bar = !$is_bar;
            }
        }

        $svg .= '</svg>';
        return $svg;
    }
}