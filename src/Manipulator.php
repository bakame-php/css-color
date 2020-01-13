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

use function max;
use function min;
use function round;

final class Manipulator
{
    public static function saturate(Color $color, int $percent): Color
    {
        self::filterPercentage($percent);

        $saturation = $color->saturation() + ($color->saturation() * $percent / 100);
        if (100 < $saturation) {
            $saturation = 100;
        }

        if (0 > $saturation) {
            $saturation = 0;
        }

        return $color->withSaturation((int) round($saturation));
    }

    private static function filterPercentage(int $percent): void
    {
        if ($percent > 100 || $percent < 0) {
            throw MalformedColor::dueToInvalidPercentValueRange($percent);
        }
    }

    public static function desaturate(Color $color, int $percent): Color
    {
        self::filterPercentage($percent);

        $saturation = $color->saturation() - ($color->saturation() * $percent / 100);
        if (100 < $saturation) {
            $saturation = 100;
        }

        if (0 > $saturation) {
            $saturation = 0;
        }

        return $color->withSaturation((int) round($saturation));
    }

    public static function lighten(Color $color, int $percent): Color
    {
        self::filterPercentage($percent);

        $lightness = $color->lightness() + $percent;
        if (100 < $lightness) {
            $lightness = 100;
        }

        if (0 > $lightness) {
            $lightness = 0;
        }

        return $color->withLightness($lightness);
    }

    public static function darken(Color $color, int $percent): Color
    {
        self::filterPercentage($percent);

        $lightness = $color->lightness() - $percent;
        if (100 < $lightness) {
            $lightness = 100;
        }

        if (0 > $lightness) {
            $lightness = 0;
        }

        return $color->withLightness($lightness);
    }

    public static function spin(Color $color, int $degrees): Color
    {
        $hue = ($color->hue() + $degrees) % 360;

        return $color->withHue($hue);
    }

    public static function fadeIn(Color $color, int $percent): Color
    {
        self::filterPercentage($percent);

        $alpha = $color->alpha() + ($color->alpha() * $percent / 100);
        if ($alpha > 1) {
            $alpha = 1;
        }

        return $color->withAlpha($alpha);
    }

    public static function fadeOut(Color $color, int $percent): Color
    {
        self::filterPercentage($percent);

        $alpha = $color->alpha() - ($color->alpha() * $percent / 100);
        if ($alpha < 0) {
            $alpha = 0;
        }

        return $color->withAlpha($alpha);
    }

    public static function mix(Color $colorA, Color $colorB, int $percent = 50): Color
    {
        self::filterPercentage($percent);

        $percent = $percent / 100;
        $weight = (2 * $percent) - 1;
        $delta = $colorA->alpha() - $colorB->alpha();
        $multiply = $weight * $delta;
        $theWeight = $weight;
        if ($multiply !== -1.0) {
            $theWeight = ($weight + $delta) / (1 + $multiply);
        }

        $weightB = ($theWeight + 1) / 2;
        $weightA = 1 - $weightB;

        $red = ($colorA->red() * $weightA) + ($colorB->red() * $weightB);
        $green = ($colorA->green() * $weightA) + ($colorB->green() * $weightB);
        $blue = ($colorA->blue() * $weightA) + ($colorB->blue() * $weightB);
        $alpha = ($colorA->alpha() * $percent) + ($colorB->alpha() * (1 - $percent));

        return Color::fromRGB((int) round($red), (int) round($green), (int) round($blue), $alpha);
    }

    public static function tint(Color $color, int $percent): Color
    {
        static $white;

        $white = $white ?? Color::fromRGB(255, 255, 255);

        return self::mix($color, $white, $percent);
    }

    public static function shade(Color $color, int $percent): Color
    {
        static $black;

        $black = $black ?? Color::fromRGB(0, 0, 0);

        return self::mix($color, $black, $percent);
    }

    public static function grayscale(Color $color): Color
    {
        return $color->withSaturation(0);
    }

    public static function invert(Color $color): Color
    {
        return Color::fromRGB(
            255 - $color->red(),
            255 - $color->green(),
            255 - $color->blue(),
            $color->alpha()
        );
    }

    public function brighten(Color $color, int $percent): Color
    {
        self::filterPercentage($percent);

        $percent *= -1;
        $delta = round(255 * ($percent / 100));

        return Color::fromRGB(
            (int) round(max(0, min(255, $color->red() - $delta)), 0),
            (int) round(max(0, min(255, $color->green() - $delta)), 0),
            (int) round(max(0, min(255, $color->blue() - $delta)), 0),
            $color->alpha()
        );
    }
}
