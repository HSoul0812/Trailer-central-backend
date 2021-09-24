<?php

declare(strict_types=1);

namespace TrailerTrader\Insights;

use Laravel\Nova\Card;

class AreaChart extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = 'full';

    /**
     * Get the component name for the element.
     */
    public function component(): string
    {
        return 'area-chart';
    }

    public function filters(array $filters): self
    {
        return $this->withMeta(['filters' => (object) $filters]);
    }

    public function series(array $series): self
    {
        foreach ($series as $key => $data) {
            if (isset($data['backgroundColor']) && empty($data['borderColor'])) {
                $series[$key]['borderColor'] = $this->adjustBrightness($data['backgroundColor'], -40);
            }
        }

        return $this->withMeta(['series' => $series]);
    }

    public function type(string $type): self
    {
        return $this->withMeta(['type' => $type]);
    }

    public function options(array $options): self
    {
        return $this->withMeta(['options' => (object) $options]);
    }

    public function animations(array $animations): self
    {
        return $this->withMeta(['animations' => $animations]);
    }

    public function title(string $title): self
    {
        return $this->withMeta(['title' => $title]);
    }

    public function uriKey(string $uriKey): self
    {
        return $this->withMeta(['uriKey' => $uriKey]);
    }

    private function adjustBrightness(string $hex, int $steps): string
    {
        // Steps should be between -255 and 255. Negative = darker, positive = lighter
        $steps = max(-255, min(255, $steps));

        // Normalize into a six character long hex string
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) === 3) {
            $hex = str_repeat($hex[0], 2) . str_repeat($hex[1], 2) . str_repeat($hex[2], 2);
        }

        // Split into three parts: R, G and B
        $color_parts = str_split($hex, 2);
        $return = '#';

        foreach ($color_parts as $color) {
            $color = hexdec($color); // Convert to decimal
            $color = max(0, min(255, $color + $steps)); // Adjust color
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
        }

        return $return;
    }
}
