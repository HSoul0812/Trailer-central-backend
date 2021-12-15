<?php

declare(strict_types=1);

namespace App\Nova\Dashboards;

/**
 * This trait is intended to be a simple color generator in the future unless we found a good package.
 */
trait WithColorPalette
{
    /**
     * @return string[]
     */
    public function generateColorPalette(): array
    {
        return [
            '#4C8576',
            '#DBD5BF',
            '#DB8374',
            '#D7D46E',
            '#DB8374',
            '#F8E21B',
            '#E96C20',
            '#72C54C',
            '#537756',
            '#D85935',
            '#6ED2C9',
            '#B4E3C4',
            '#362033',
            '#2449DE',
            '#AC413C',
            '#E5593B',
            '#595F5F',
        ];
    }

    public function hex2rgb(string $colour, float $opacity = 0.2): string
    {
        if ($colour[0] === '#') {
            $colour = substr($colour, 1);
        }

        [$r, $g, $b] = [0, 0, 0]; // black

        if (strlen($colour) === 6) {
            [$r, $g, $b] = [$colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]];
        } elseif (strlen($colour) === 3) {
            [$r, $g, $b] = [$colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]];
        }

        return sprintf('rgba(%d, %d, %d, %f)', hexdec($r), hexdec($g), hexdec($b), $opacity);
    }
}
