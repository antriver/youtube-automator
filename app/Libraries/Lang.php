<?php

namespace YouTubeAutomator\Libraries;

class Lang
{
    public static function s($num)
    {
        return $num == 1 ? '' : 's';
    }

    public static function are($num)
    {
        return $num == 1 ? 'is' : 'are';
    }

    public static function have($num)
    {
        return $num == 1 ? 'has' : 'have';
    }

    public static function implodeAnd($glue, $array)
    {
        $count = count($array);
        $str = '';
        for ($i = 0; $i < $count; ++$i) {
            if ($count == 2) {
                $str .= ' and ';
            } elseif ($i > 0 && ($i + 1 == $count)) {
                $str .= $glue . ' and ';
            } elseif ($i > 0) {
                $str .= $glue;
            }

            $str .= $array[$i];
        }
        return $str;
    }

    public static function wordTruncate($string, $limit, $cutter = '...', $returnArray = false)
    {
        if (strlen($string) <= $limit) {
            return $string;
        }

        $limit -= strlen($cutter);

        $string = substr($string, 0, $limit);
        $string = trim($string, ' ,.');

        // Find last space in truncated string
        $breakpoint = strrpos($string, ' ');

        if ($breakpoint === false) {
            return $string . $cutter;
        } else {
            $string = substr($string, 0, $breakpoint);
            $string = trim($string, ' ,.');
            $string .= $cutter;
            if ($returnArray) {
                return array(
                    'breakpoint' => $breakpoint,
                    'string' => $string
                );
            } else {
                return $string;
            }
        }
    }

    public static function expressNumberAsWords($number)
    {
        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'fourty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . self::expressNumberAsWords(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . self::expressNumberAsWords($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = self::expressNumberAsWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= self::expressNumberAsWords($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }

    /**
     * http://stackoverflow.com/questions/2690504/php-producing-relative-date-time-from-timestamps
     */
    public static function relativeTime($time, $word = 'ago', $yesterday = true, $longYears = false, $short = false)
    {
        $delta = time() - $time;

        $SECOND = 1;
        $MINUTE = 60;
        $HOUR = 60 * $MINUTE;
        $DAY = 24 * $HOUR;
        $MONTH = 30 * $DAY;
        $YEAR = 365 * $DAY;

        if ($delta < 1 * $MINUTE) {
            if ($delta == 1) {
                return 'one ' . ($short ? 'sec' : 'second') . ' ' . $word;
            }
            return $delta . ' ' . ($short ? 'secs' : 'seconds') . ' ' . $word;
        }
        if ($delta < 2 * $MINUTE) {
            return '1 ' . ($short ? 'min' : 'minute') . ' ' . $word;
        }
        if ($delta < 45 * $MINUTE) {
            return floor($delta / $MINUTE) . ' ' . ($short ? 'mins' : 'minutes') . ' ' . $word;
        }
        if ($delta < 120 * $MINUTE) {
            return '1 ' . ($short ? 'hr' : 'hour') . ' ' . $word;
        }
        if ($delta < 24 * $HOUR) {
            return floor($delta / $HOUR) . ' ' . ($short ? 'hrs' : 'hours') . ' ' . $word;
        }
        if ($yesterday && $delta < 48 * $HOUR) {
            return 'yesterday';
        }
        if ($delta < 48 * $HOUR) {
            return '1 day ' . $word;
        }
        if ($delta < 30 * $DAY) {
            return floor($delta / $DAY) . ' days ' . $word;
        }

        if ($delta < 12 * $MONTH) {
            $months = floor($delta / $DAY / 30);
            if ($months <= 1) {
                return '1 month ' . $word;
            } else {
                return $months . ' months ' . $word;
            }
        }

        $years = floor($delta / $YEAR);

        if ($longYears) {
            $months = floor(($delta % $YEAR) / $MONTH);

            if ($months == 12) {
                $months = '';
                $years++;
                //$months = 11;
            } elseif ($months > 0) {
                $months = 'and ' . $months . ' month' . ($months > 1 ? 's' : '') . ' ';
            } else {
                $months = false;
            }

            if ($years <= 1) {
                return '1 year ' . $months . $word;
            }
            return $years . ' years ' . $months . $word;
        } else {
            if ($years <= 1) {
                return '1 year ' . $word;
            }
            return $years . ' years ' . $word;
        }
    }
}
