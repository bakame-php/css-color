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
use Bakame\Color\Manipulator;
use PHPUnit\Framework\TestCase;

class ManipulatorTest extends TestCase
{
    /**
     * @dataProvider mixColorProvider
     */
    public function testMix(string $colorA, string $colorB, int $percent, string $expected): void
    {
        $color = Manipulator::mix(Color::fromCss($colorA), Color::fromCss($colorB), $percent);

        self::assertSame(Color::fromCss($expected)->asCssRGB(), $color->asCssRGB());
    }

    public function mixColorProvider(): iterable
    {
        return [
            'with 0%' =>  [
                'colorA' => 'rgb(255, 0 , 0)',
                'colorB' => 'rgb(0, 0 , 255)',
                'percent' => 0,
                'expected' => 'rgb(255, 0 , 0)',
            ],
            'with 25%' =>  [
                'colorA' => 'rgb(255, 0 , 0)',
                'colorB' => 'rgb(0, 0 , 255)',
                'percent' => 25,
                'expected' => 'rgb(191, 0, 64)',
            ],
            'with 50%' => [
                'colorA' => 'rgb(255, 0 , 0)',
                'colorB' => 'rgb(0, 0 , 255)',
                'percent' => 50,
                'expected' => 'rgb(128, 0 , 128)',
            ],
            'with 75%' =>  [
                'colorA' => 'rgb(255, 0 , 0)',
                'colorB' => 'rgb(0, 0 , 255)',
                'percent' => 75,
                'expected' => 'rgb(64, 0, 191)',
            ],
            'with 100%' =>  [
                'colorA' => 'rgb(255, 0 , 0)',
                'colorB' => 'rgb(0, 0 , 255)',
                'percent' => 100,
                'expected' => 'rgb(0, 0 , 255)',
            ],
            'with transparency' =>  [
                'colorA' => 'rgba(255, 0, 0, 0.5)',
                'colorB' => 'rgb(0, 0 , 255)',
                'percent' => 50,
                'expected' => 'rgba(64, 0, 191, 0.75)',
            ],
        ];
    }

    public function testSaturate(): void
    {
        $color = Manipulator::saturate(Color::fromCss('hsl(100,40%,50%)'), 50);

        self::assertSame(60, $color->saturation());
        self::assertTrue($color->equals(Color::fromCss('hsl(100,60%,50%)')));
    }

    public function testDesaturate(): void
    {
        $color = Manipulator::desaturate(Color::fromCss('hsl(100,80%,60%)'), 50);

        self::assertSame(40, $color->saturation());
        self::assertTrue($color->equals(Color::fromCss('hsl(100,40%,60%)')));
    }

    public function testManipulatorThrowsIfPercentIsNotInValidRange(): void
    {
        self::expectException(MalformedColor::class);

        Manipulator::desaturate(Color::fromCss('hsl(100,80%,60%)'), 120);
    }

    public function testFadeOut(): void
    {
        $color = Manipulator::fadeOut(Color::fromCss('rgba(10,10,10,0.8)'), 50);

        self::assertEquals(0.4, $color->alpha());
        self::assertTrue($color->equals(Color::fromCss('rgba(10,10,10,0.4)')));
    }

    public function testFadeIn(): void
    {
        $color = Manipulator::fadeIn(Color::fromCss('rgba(10,10,10,0.5)'), 50);

        self::assertEquals(0.75, $color->alpha());
        self::assertTrue($color->equals(Color::fromCss('rgba(10,10,10,0.75)')));
    }

    public function testSpin(): void
    {
        $input = Color::fromCss('hsl(60,10%,10%)');
        $color = Manipulator::spin($input, 180);

        self::assertEquals(240, $color->hue());
        self::assertTrue($color->equals(Color::fromCss('hsl(240,10%,10%)')));
        self::assertTrue($color->equals(Manipulator::spin($input, -180)));
    }

    public function testGrayscale(): void
    {
        $input = Color::fromCss('rgb(67,122,134)');
        $color = Manipulator::grayscale($input);

        self::assertTrue($color->equals(Color::fromCss('rgb(107,107,107)')));
    }
}
