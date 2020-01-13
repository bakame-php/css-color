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

namespace BakameTest\Color;

use Bakame\Color\Color;
use Bakame\Color\MalformedColor;
use PHPUnit\Framework\TestCase;

final class ColorTest extends TestCase
{
    public function testItCanBeInstantiatedWithRGBColorAndTransparency(): void
    {
        $color = Color::fromRGB(255, 128, 0, .7);

        self::assertSame(128, $color->green());
        self::assertSame(255, $color->red());
        self::assertSame(0, $color->blue());
        self::assertSame(0.7, $color->alpha());
        self::assertSame(30, $color->hue());
        self::assertSame(100, $color->saturation());
        self::assertSame(50, $color->lightness());
        self::assertSame('rgba(255,128,0,0.7)', $color->asCssRGB(Color::FORMAT_RGB_DEC));
        self::assertSame('hsla(30,100%,50%,0.7)', $color->asCssHSL());
        self::assertSame('#ff8000b3', $color->asCssRGB(Color::FORMAT_RGB_HEX));
        self::assertTrue($color->equals(Color::fromCss($color->asCssRGB(Color::FORMAT_RGB_HEX))));
    }

    public function testItCanBeInstantiatedWithRGBColor(): void
    {
        $color = Color::fromRGB(255, 128, 0);

        self::assertSame(128, $color->green());
        self::assertSame(255, $color->red());
        self::assertSame(0, $color->blue());
        self::assertSame(1.0, $color->alpha());
        self::assertSame(30, $color->hue());
        self::assertSame(100, $color->saturation());
        self::assertSame(50, $color->lightness());
        self::assertSame('rgb(255,128,0)', $color->asCssRGB(Color::FORMAT_RGB_DEC));
        self::assertSame('hsl(30,100%,50%)', $color->asCssHSL());
        self::assertSame('#ff8000', $color->asCssRGB(Color::FORMAT_RGB_HEX));
    }

    /**
     * @dataProvider validCssValueProvider
     */
    public function testFromCss(string $css, string $expected): void
    {
        self::assertSame($expected, Color::fromCss($css)->asCssRGB(Color::FORMAT_RGB_DEC));
    }

    public function validCssValueProvider(): iterable
    {
        return [
            'rgb decimal' => [
                'css' => 'rgb(255,128,0)',
                'expected' => 'rgb(255,128,0)',
            ],
            'rgb decimal with space' => [
                'css' => 'rgb(255, 128 , 0)',
                'expected' => 'rgb(255,128,0)',
            ],
            'rgb decimal with space around' => [
                'css' => '    rgb(255, 128 , 0)    ',
                'expected' => 'rgb(255,128,0)',
            ],
            'rgba decimal' => [
                'css' => 'rgba(255,128,0,0.80)',
                'expected' => 'rgba(255,128,0,0.8)',
            ],
            'rgba decimal with space' => [
                'css' => 'rgba( 255, 128, 0, 0.80 )',
                'expected' => 'rgba(255,128,0,0.8)',
            ],
            'rgb hexadecimal' => [
                'css' => '#336699',
                'expected' => 'rgb(51,102,153)',
            ],
            'rgb hexadecimal short style' => [
                'css' => '#369',
                'expected' => 'rgb(51,102,153)',
            ],
            'rgb hexadecimal with alpha' => [
                'css' => '#33669900',
                'expected' => 'rgba(51,102,153,0)',
            ],
            'rgb hexadecimal is case insensitive' => [
                'css' => '#ff8000',
                'expected' => 'rgb(255,128,0)',
            ],
            'hex 4 digits' => [
                'css' => '#f00f',
                'expected' => 'rgb(255,0,0)',
            ],
            'hsl' => [
                'css' => 'hsl(120,0%,50%)',
                'expected' => 'rgb(128,128,128)',
            ],
            'hsl with spaces' => [
                'css' => 'hsl(120, 50%, 20% )',
                'expected' => 'rgb(26,77,26)',
            ],
            'hsl with transparency' => [
                'css' => 'hsla(120,50%,20%,0.80)',
                'expected' => 'rgba(26,77,26,0.8)',
            ],
            'hsl with transparency and space' => [
                'css' => 'hsla(120, 50%, 20%, 0.80)',
                'expected' => 'rgba(26,77,26,0.8)',
            ],
        ];
    }

    /**
     * @dataProvider invalidCssValueProvider
     */
    public function testFromCssThrowException(string $css): void
    {
        self::expectException(MalformedColor::class);

        Color::fromCss($css);
    }

    public function invalidCssValueProvider(): iterable
    {
        return [
            'empty string' => ['     '],
            'rgb syntax error' => ['rgb(#369)'],
            'rgb channel out of bound' => ['rgb(256,128,0)'],
            'rgb alpha out of bound' => ['rgba(255,128,0,1.1)'],
            'rgb using rgba notation' => ['rgb(255,128,0,0.7)'],
            'rgba using rgb notation' => ['rgba(255,128,0)'],
            'hex without the # prefix' => ['369'],
            'hex without channel out of bound' => ['#FFGG33'],
            'hsl syntax error' => ['hsl(#369)'],
            'hsl alpha out of bound' => ['hsla(250,80%,60%,1.1)'],
            'hsl using hsla notation' => ['hsl(255,80%,60%,0.7)'],
            'hsla using hsl notation' => ['hsla(255,80%,60%)'],
        ];
    }

    public function testItWillTriggerAnExceptionIfAlphaIsInvalidWithRGBA(): void
    {
        self::expectException(MalformedColor::class);

        Color::fromRGB(255, 128, 0, 4);
    }

    public function testItWillTriggerAnExceptionIfTheRGBFormatIsNotRecognized(): void
    {
        self::expectException(MalformedColor::class);

        Color::fromRGB(255, 128, 0, 1)->asCssRGB('foobar');
    }

    /**
     * @dataProvider withRedValidProvider
     */
    public function testChangeRedChannel(string $input, int $channel, string $expected): void
    {
        $color = Color::fromCss($input);
        $expectedColor = Color::fromCss($expected);
        $changedColor = $color->withRed($channel);

        self::assertTrue($changedColor->equals($expectedColor));
        if ($input === $expected) {
            self::assertSame($color, $changedColor);
        }
    }

    public function withRedValidProvider(): iterable
    {
        return [
            'basic' => ['#000', 255, '#f00'],
            'same value' => ['#f00', 255, '#f00'],
        ];
    }

    /**
     * @dataProvider withGreenValidProvider
     */
    public function testChangeGreenChannel(string $input, int $channel, string $expected): void
    {
        $color = Color::fromCss($input);
        $expectedColor = Color::fromCss($expected);
        $changedColor = $color->withGreen($channel);

        self::assertTrue($changedColor->equals($expectedColor));
        if ($input === $expected) {
            self::assertSame($color, $changedColor);
        }
    }

    public function withGreenValidProvider(): iterable
    {
        return [
            'basic' => ['#f00', 255, '#ff0'],
            'same value' => ['#f00', 0, '#f00'],
        ];
    }

    /**
     * @dataProvider withBlueValidProvider
     */
    public function testChangeBlueChannel(string $input, int $channel, string $expected): void
    {
        $color = Color::fromCss($input);
        $expectedColor = Color::fromCss($expected);
        $changedColor = $color->withBlue($channel);

        self::assertTrue($changedColor->equals($expectedColor));
        if ($input === $expected) {
            self::assertSame($color, $changedColor);
        }
    }

    public function withBlueValidProvider(): iterable
    {
        return [
            'basic' => ['#000', 255, '#00f'],
            'same value' => ['#00f', 255, '#00f'],
        ];
    }

    /**
     * @dataProvider withHueValidProvider
     */
    public function testChangeHueChannel(string $input, int $channel, string $expected): void
    {
        $color = Color::fromCss($input);
        $expectedColor = Color::fromCss($expected);
        $changedColor = $color->withHue($channel);

        self::assertTrue($changedColor->equals($expectedColor));
        if ($input === $expected) {
            self::assertSame($color, $changedColor);
        }
    }

    public function withHueValidProvider(): iterable
    {
        return [
            'basic' => ['hsl(180, 50%, 50%)', 360, 'hsl(360, 50%, 50%)'],
            'same value' => ['hsl(180, 50%, 50%)', 180, 'hsl(180, 50%, 50%)'],
            'spin' => ['hsl(180, 50%, 50%)', 540, 'hsl(180, 50%, 50%)'],
            'spin with negative value' => ['hsl(180, 50%, 50%)', -540, 'hsl(180, 50%, 50%)'],
        ];
    }

    /**
     * @dataProvider withSaturationValidProvider
     */
    public function testChangesSaturationChannel(string $input, int $channel, string $expected): void
    {
        $color = Color::fromCss($input);
        $expectedColor = Color::fromCss($expected);
        $changedColor = $color->withSaturation($channel);

        self::assertTrue($changedColor->equals($expectedColor));
        if ($input === $expected) {
            self::assertSame($color, $changedColor);
        }
    }

    public function withSaturationValidProvider(): iterable
    {
        return [
            'basic' => ['hsl(180, 50%, 50%)', 0, 'hsl(360, 0%, 50%)'],
            'same value' => ['hsl(180, 50%, 50%)', 50, 'hsl(180, 50%, 50%)'],
        ];
    }

    /**
     * @dataProvider withLightnessValidProvider
     */
    public function testChangesLightnessChannel(string $input, int $channel, string $expected): void
    {
        $color = Color::fromCss($input);
        $expectedColor = Color::fromCss($expected);
        $changedColor = $color->withLightness($channel);

        self::assertTrue($changedColor->equals($expectedColor));
        if ($input === $expected) {
            self::assertSame($color, $changedColor);
        }
    }

    public function withLightnessValidProvider(): iterable
    {
        return [
            'basic' => ['hsl(180, 50%, 50%)', 0, 'hsl(180, 50%, 0%)'],
            'same value' => ['hsl(180, 50%, 50%)', 50, 'hsl(180, 50%, 50%)'],
        ];
    }

    /**
     * @dataProvider withAlphaValidProvider
     */
    public function testChangesAlphaChannel(string $input, float $channel, string $expected): void
    {
        $color = Color::fromCss($input);
        $expectedColor = Color::fromCss($expected);
        $changedColor = $color->withAlpha($channel);

        self::assertTrue($changedColor->equals($expectedColor));
        if ($input === $expected) {
            self::assertSame($color, $changedColor);
        }
    }

    public function withAlphaValidProvider(): iterable
    {
        return [
            'basic' => ['rgba(180, 50, 50, 0.7)', 0.9, 'rgba(180, 50, 50, 0.9)'],
            'same value' => ['rgba(180, 50, 50, 0.9)', 0.9, 'rgba(180, 50, 50, 0.9)'],
        ];
    }
}
