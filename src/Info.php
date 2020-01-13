<?php

/**
 * Bakame.color
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bakame\Color;

use function array_map;
use function round;

final class Info
{
    public const LEVEL_AAA = 'AAA';

    public const LEVEL_AA = 'AA';

    public const LEVEL_FAILED = '';

    public const FONT_NORMAL = 'normal';

    public const FONT_LARGE = 'large';

    public static function isDark(Color $color): bool
    {
        return !self::isLight($color);
    }

    /**
     * @link http://24ways.org/2010/calculating-color-contrast
     * @link https://en.wikipedia.org/wiki/Luma_(video)
     */
    public static function isLight(Color $color): bool
    {
        $darkness = 1 - (299 * $color->red() + 587 * $color->green() + 114 * $color->blue()) / 255;

        return $darkness < 128;
    }

    /**
     * @link http://www.w3.org/TR/WCAG20/#relativeluminancedef
     */
    public function luminosity(Color $color): float
    {
        $mapper = static function (int $channel): float {
            $channel /= 255;

            return ($channel <= 0.03928) ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4;
        };

        $luminosity = array_map($mapper, [$color->red(), $color->green(), $color->blue()]);

        return round(0.2126 * $luminosity[0] + 0.7152 * $luminosity[1] + 0.0722 * $luminosity[2], 2);
    }

    public function contrast(Color $colorA, Color $colorB): float
    {
        $luminosityA = self::luminosity($colorA);
        $luminosityB = self::luminosity($colorB);

        if ($luminosityA > $luminosityB) {
            return ($luminosityA + 0.05) / ($luminosityB + 0.05);
        }

        return ($luminosityB + 0.05) / ($luminosityA + 0.05);
    }

    /**
     * Constrast ratio level for ordinary text as specified by WCAG 2.0.
     *
     * @see https://www.w3.org/TR/UNDERSTANDING-WCAG20/visual-audio-contrast-contrast.html
     */
    public function level(Color $colorA, Color $colorB, string $fontSize = self::FONT_NORMAL): string
    {
        $contrast = self::contrast($colorA, $colorB);
        if ($contrast >= 7.0) {
            return self::LEVEL_AAA;
        }

        if ($contrast >= 4.5) {
            if (self::FONT_LARGE === $fontSize) {
                return self::LEVEL_AAA;
            }

            return self::LEVEL_AA;
        }

        if (self::FONT_NORMAL === $fontSize) {
            return self::LEVEL_FAILED;
        }

        if ($contrast >= 3.0) {
            return self::LEVEL_AA;
        }

        return self::LEVEL_FAILED;
    }
}
