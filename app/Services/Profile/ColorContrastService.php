<?php

declare(strict_types=1);

namespace App\Services\Profile;

class ColorContrastService
{
    /**
     * Compute the contrast ratio between two hex colors according to WCAG 2.1.
     *
     * @param string $hexColor1
     * @param string $hexColor2
     * @return float
     */
    public function getContrastRatio(string $hexColor1, string $hexColor2): float
    {
        $lum1 = $this->calculateRelativeLuminance($hexColor1);
        $lum2 = $this->calculateRelativeLuminance($hexColor2);

        $brightest = max($lum1, $lum2);
        $darkest = min($lum1, $lum2);

        return round(($brightest + 0.05) / ($darkest + 0.05), 2);
    }

    /**
     * Determine if the two colors pass WCAG AA requirement (>= 4.5 for normal text).
     *
     * @param string $textColor
     * @param string $backgroundColor
     * @param bool $isLargeText
     * @return bool
     */
    public function meetsWcagAa(string $textColor, string $backgroundColor, bool $isLargeText = false): bool
    {
        $ratio = $this->getContrastRatio($textColor, $backgroundColor);
        return $isLargeText ? $ratio >= 3.0 : $ratio >= 4.5;
    }

    /**
     * Get the best accessible text color (#ffffff or #0f172a) for a given background color.
     *
     * @param string $backgroundColor
     * @return string
     */
    public function getAccessibleTextColor(string $backgroundColor): string
    {
        $whiteRatio = $this->getContrastRatio('#ffffff', $backgroundColor);
        $darkRatio = $this->getContrastRatio('#0f172a', $backgroundColor);

        return $whiteRatio >= $darkRatio ? '#ffffff' : '#0f172a';
    }

    /**
     * Calculate relative luminance according to WCAG 2.1.
     *
     * @param string $hex
     * @return float
     */
    public function calculateRelativeLuminance(string $hex): float
    {
        $rgb = $this->hexToRgb($hex);

        $r = $this->linearizeColorChannel($rgb['r'] / 255.0);
        $g = $this->linearizeColorChannel($rgb['g'] / 255.0);
        $b = $this->linearizeColorChannel($rgb['b'] / 255.0);

        return (0.2126 * $r) + (0.7152 * $g) + (0.0722 * $b);
    }

    /**
     * Convert channel from sRGB to linear RGB.
     */
    protected function linearizeColorChannel(float $val): float
    {
        return $val <= 0.03928
            ? $val / 12.92
            : pow(($val + 0.055) / 1.055, 2.4);
    }

    /**
     * Convert Hex string to RGB associative array.
     *
     * @param string $hex
     * @return array{r: int, g: int, b: int}
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (strlen($hex) !== 6) {
            return ['r' => 0, 'g' => 0, 'b' => 0];
        }

        return [
            'r' => (int) hexdec(substr($hex, 0, 2)),
            'g' => (int) hexdec(substr($hex, 2, 2)),
            'b' => (int) hexdec(substr($hex, 4, 2)),
        ];
    }
}
