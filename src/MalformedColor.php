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

class MalformedColor extends \InvalidArgumentException
{
    public static function dueToUnsupportedStringColorDefinition(string $color): self
    {
        return new self('The submitted color string definition `'.$color.'` is invalid or not supported.');
    }

    public static function dueToSyntaxError(string $color, string $type): self
    {
        return new self('The submitted color string definition `'.$color.'` is invalid for '.$type.'.');
    }

    /**
     * @param float|int $channel
     */
    public static function dueToInvalidChannelValueRange($channel, string $type): self
    {
        return new self('The color channel '.$type.' value '.$channel.' is out of the supported range.');
    }

    /**
     * @param int|float $alpha
     */
    public static function dueToInvalidAlphaValueRange($alpha): self
    {
        return new self('The color alpha channel value '.$alpha.' is out of the supported range.');
    }

    public static function dueToInvalidPercentValueRange(int $percent): self
    {
        return new self('The percent value '.$percent.' is out of the supported range.');
    }

    public static function dueToUnknownCssFormat(string $format): self
    {
        return new self('The submitted format definition `'.$format.'` is unknown.');
    }
}
