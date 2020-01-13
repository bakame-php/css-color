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

use function floor;
use function hexdec;
use function in_array;
use function max;
use function min;
use function preg_match;
use function round;
use function sprintf;
use function str_split;
use function strlen;
use function strtolower;

final class Color
{
    private const REGEXP_RGB_DEC = '/^(?<type>rgb|rgba)\(
        \s*(?<red>\d+)\s*,
        \s*(?<green>\d+)\s*,
        \s*(?<blue>\d+)\s*
        (,\s*(?<alpha>[0-1]\.\d+)\s*)?
    \)$/xi';

    private const REGEXP_RGB_HEX = '/^#(?<hex>[a-f0-9]{3}|[a-f0-9]{4}|[a-f0-9]{6}|[a-f0-9]{8})$/i';

    private const REGEXP_HSL = '/^(?<type>hsl|hsla)\(
        \s*(?<hue>\d+)\s*,
        \s*(?<saturation>\d+)%\s*,
        \s*(?<lightness>\d+)%\s*
        (,\s*(?<alpha>[0-1]\.\d+)\s*)?
    \)$/xi';

    public const FORMAT_RGB_DEC = 'rgb';

    public const FORMAT_RGB_HEX = 'hex';

    public const FORMAT_HSL = 'hsl';

    private const SUPPORTED_FORMAT_REGEXP = [
        self::REGEXP_RGB_DEC => 'fromCssRGB',
        self::REGEXP_RGB_HEX  => 'fromCssRGB',
        self::REGEXP_HSL => 'fromCssHSL',
    ];

    /**
     * @var int
     */
    private $red;

    /**
     * @var int
     */
    private $green;

    /**
     * @var int
     */
    private $blue;

    /**
     * @var int
     */
    private $hue;

    /**
     * @var int
     */
    private $saturation;

    /**
     * @var int
     */
    private $lightness;

    /**
     * @var float
     */
    private $alpha;

    private function __construct(int $red, int $green, int $blue, float $alpha)
    {
        $this->red = $this->filterChannel($red, 'red');
        $this->green = $this->filterChannel($green, 'green');
        $this->blue = $this->filterChannel($blue, 'blue');
        $this->alpha = $this->filterAlpha($alpha);
        [$this->hue, $this->saturation, $this->lightness] = $this->rgbToHsl($this->red, $this->green, $this->blue);
    }

    public function red(): int
    {
        return $this->red;
    }

    public function green(): int
    {
        return $this->green;
    }

    public function blue(): int
    {
        return $this->blue;
    }

    public function alpha(): float
    {
        return $this->alpha;
    }

    public function lightness(): int
    {
        return $this->lightness;
    }

    public function saturation(): int
    {
        return $this->saturation;
    }

    public function hue(): int
    {
        return $this->hue;
    }

    public function equals(Color $color): bool
    {
        return $this->asCssRGB() === $color->asCssRGB();
    }

    public function asCssHSL(): string
    {
        if (1.0 == $this->alpha) {
            return sprintf('hsl(%s,%s%%,%s%%)', $this->hue, $this->saturation, $this->lightness);
        }

        return sprintf('hsla(%s,%s%%,%s%%,%s)', $this->hue, $this->saturation, $this->lightness, round($this->alpha, 2));
    }

    public function asCssRGB(string $format = self::FORMAT_RGB_DEC): string
    {
        static $supportedRGBFormats = [self::FORMAT_RGB_DEC, self::FORMAT_RGB_HEX];

        if (!in_array($format, $supportedRGBFormats, true)) {
            throw MalformedColor::dueToUnknownCssFormat($format);
        }

        if (1.0 == $this->alpha) {
            if (self::FORMAT_RGB_DEC === $format) {
                return sprintf('rgb(%s,%s,%s)', $this->red, $this->green, $this->blue);
            }

            return sprintf('#%02x%02x%02x', $this->red, $this->green, $this->blue);
        }

        if (self::FORMAT_RGB_HEX === $format) {
            $alpha = round($this->alpha * 255);

            return sprintf('#%02x%02x%02x%02x', $this->red, $this->green, $this->blue, $alpha);
        }

        $alpha = round($this->alpha, 2);

        return sprintf('rgba(%s,%s,%s,%s)', $this->red, $this->green, $this->blue, $alpha);
    }

    public function withRed(int $red): self
    {
        if ($red === $this->red) {
            return $this;
        }

        return new self($red, $this->green, $this->blue, $this->alpha);
    }

    public function withGreen(int $green): self
    {
        if ($green === $this->green) {
            return $this;
        }

        return new self($this->red, $green, $this->blue, $this->alpha);
    }

    public function withBlue(int $blue): self
    {
        if ($blue === $this->blue) {
            return $this;
        }

        return new self($this->red, $this->green, $blue, $this->alpha);
    }

    public function withHue(int $hue): self
    {
        $hue %= 360;
        if (0 > $hue) {
            $hue = 360 + $hue;
        }

        if ($hue === $this->hue) {
            return $this;
        }

        return self::fromHSL($hue, $this->saturation, $this->lightness, $this->alpha);
    }

    public function withSaturation(int $saturation): self
    {
        if ($saturation === $this->saturation) {
            return $this;
        }

        return self::fromHSL($this->hue, $saturation, $this->lightness, $this->alpha);
    }

    public function withLightness(int $lightness): self
    {
        if ($lightness === $this->lightness) {
            return $this;
        }

        return self::fromHSL($this->hue, $this->saturation, $lightness, $this->alpha);
    }

    public function withAlpha(float $alpha): self
    {
        if ($alpha === $this->alpha) {
            return $this;
        }

        $clone = clone $this;
        $clone->alpha = self::filterAlpha($alpha);

        return $clone;
    }

    public static function fromCss(string $color): self
    {
        $color = trim($color);
        foreach (self::SUPPORTED_FORMAT_REGEXP as $regexp => $constructor) {
            if (1 === preg_match($regexp, $color, $matches)) {
                return self::{$constructor}($color, $matches);
            }
        }

        throw MalformedColor::dueToUnsupportedStringColorDefinition($color);
    }

    public static function fromRGB(int $red, int $green, int $blue, float $alpha = 1.0): self
    {
        return new self($red, $green, $blue, $alpha);
    }

    public static function fromHSL(int $hue, int $saturation, int $lightness, float $alpha = 1.0): self
    {
        if ($saturation > 100 || $saturation < 0) {
            throw MalformedColor::dueToInvalidChannelValueRange($saturation, 'saturation');
        }

        if ($lightness > 100 || $lightness < 0) {
            throw MalformedColor::dueToInvalidChannelValueRange($lightness, 'lightness');
        }

        $hue %= 360;
        if (0 > $hue) {
            $hue = 360 + $hue;
        }

        $hue /= 360;
        $saturation /= 100;
        $lightness /= 100;

        $v = $lightness + $saturation - $lightness * $saturation;
        if ($lightness <= 0.5) {
            $v = $lightness * (1 + $saturation);
        }

        if ($v == 0) {
            $channel = (int) round($lightness * 255);

            return new self($channel, $channel, $channel, $alpha);
        }

        $hue *= 6;
        $min = 2 * $lightness - $v;
        $six = (int) floor($hue);
        $vsfract = $v * (($v - $min) / $v) * ($hue - $six);
        switch ($six) {
            case 1:
                $red = $v - $vsfract;
                $green = $v;
                $blue = $min;
                break;
            case 2:
                $red = $min;
                $green = $v;
                $blue = $min + $vsfract;
                break;
            case 3:
                $red = $min;
                $green = $v - $vsfract;
                $blue = $v;
                break;
            case 4:
                $red = $min + $vsfract;
                $green = $min;
                $blue = $v;
                break;
            case 5:
                $red = $v;
                $green = $min;
                $blue = $v - $vsfract;
                break;
            default:
                $red = $v;
                $green = $min + $vsfract;
                $blue = $min;
                break;
        }

        return new self((int) round($red * 255), (int) round($green * 255), (int) round($blue * 255), $alpha);
    }

    private static function fromCssRGB(string $color, array $matches): self
    {
        if (isset($matches['type'])) {
            return self::fromCssRGBDec($color, $matches);
        }

        return self::fromCssRGBHex($matches);
    }

    private static function fromCssRGBDec(string $color, array $channels): self
    {
        if ('rgb' === strtolower($channels['type'])) {
            if (isset($channels['alpha']) && '' !== $channels['alpha']) {
                throw MalformedColor::dueToSyntaxError($color, 'RGB color');
            }

            return new self((int) $channels['red'], (int) $channels['green'], (int) $channels['blue'], 1.0);
        }

        if (!isset($channels['alpha']) || '' === $channels['alpha']) {
            throw MalformedColor::dueToSyntaxError($color, 'RGB color');
        }

        return new self((int) $channels['red'], (int) $channels['green'], (int) $channels['blue'], (float) $channels['alpha']);
    }

    private static function fromCssRGBHex(array $channels): self
    {
        $alpha = 'ff';
        $length = strlen($channels['hex']);

        switch ($length) {
            case 8:
                [$red, $green, $blue, $alpha] = str_split($channels['hex'], 2);
                break;
            case 6:
                [$red, $green, $blue] = str_split($channels['hex'], 2);
                break;
            case 4:
                [$red, $green, $blue, $alpha] = str_split($channels['hex'], 1);
                $red .= $red;
                $green .= $green;
                $blue .= $blue;
                $alpha .= $alpha;
                break;
            case 3:
                [$red, $green, $blue] = str_split($channels['hex'], 1);
                $red .= $red;
                $green .= $green;
                $blue .= $blue;
                break;
            default:
                throw MalformedColor::dueToUnknownCssFormat('The submitted format is not recognized.');
        }

        return new self(self::filterHex($red), self::filterHex($green), self::filterHex($blue), self::filterHexAlpha($alpha));
    }

    private static function fromCssHSL(string $color, array $channels): self
    {
        if ('hsl' === strtolower($channels['type'])) {
            if (isset($channels['alpha']) && '' !== $channels['alpha']) {
                throw MalformedColor::dueToSyntaxError($color, 'HSL color');
            }

            return self::fromHSL((int) $channels['hue'], (int) $channels['saturation'], (int) $channels['lightness']);
        }

        if (!isset($channels['alpha']) || '' === $channels['alpha']) {
            throw MalformedColor::dueToSyntaxError($color, 'HSL color');
        }

        return self::fromHSL((int) $channels['hue'], (int) $channels['saturation'], (int) $channels['lightness'], (float) $channels['alpha']);
    }

    private function filterChannel(int $channel, string $type): int
    {
        if (0 > $channel || 255 < $channel) {
            throw MalformedColor::dueToInvalidChannelValueRange($channel, $type);
        }

        return $channel;
    }

    private function filterAlpha(float $alpha): float
    {
        if (0 > $alpha || 1 < $alpha) {
            throw MalformedColor::dueToInvalidAlphaValueRange($alpha);
        }

        return $alpha;
    }

    private static function filterHex(string $hexColor): int
    {
        return (int) round(hexdec($hexColor));
    }

    private static function filterHexAlpha(string $hexAlpha): float
    {
        return hexdec($hexAlpha) / 255;
    }

    /**
     * @return int[]
     */
    private function rgbToHsl(int $red, int $green, int $blue): array
    {
        $red = max(min($red / 255, 1), 0);
        $green = max(min($green / 255, 1), 0);
        $blue = max(min($blue / 255, 1), 0);
        $max = max($red, $green, $blue);
        $min = min($red, $green, $blue);
        $lightness = ($max + $min) / 2;
        if ($max === $min) {
            return [0, 0, (int) round($lightness * 100)];
        }

        $delta = $max - $min;
        $saturation = $lightness > 0.5 ? $delta / (2 - $max - $min) : $delta / ($max + $min);

        $hue = ($red - $green) / $delta + 4;
        if ($max === $red) {
            $hue = ($green - $blue) / $delta + ($green < $blue ? 6 : 0);
        } elseif ($max === $green) {
            $hue = ($blue - $red) / $delta + 2;
        }

        return [(int) round($hue / 6 * 360), (int) round($saturation * 100), (int) round($lightness * 100)];
    }
}
