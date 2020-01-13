<?php

namespace spec\Bakame\Color;

use Bakame\Color\Color;
use Bakame\Color\MalformedColor;
use PhpSpec\ObjectBehavior;

class ColorSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('fromCss',['rgb(255,255,255)']);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(Color::class);
    }

    public function its_color_channel_should_be_exposed(): void
    {
        $this->green()->shouldBe(255);
        $this->red()->shouldBe(255);
        $this->blue()->shouldBe(255);
        $this->alpha()->shouldBe(1.0);
        $this->hue()->shouldBe(0);
        $this->saturation()->shouldBe(0);
        $this->lightness()->shouldBe(100);
    }

    public function its_css_format_should_be_returned(): void
    {
        $this->asCssRGB(Color::FORMAT_RGB_DEC)->shouldBe('rgb(255,255,255)');
        $this->asCssRGB(Color::FORMAT_RGB_HEX)->shouldBe('#ffffff');
        $this->asCssHSL()->shouldBe('hsl(0,0%,100%)');
    }

    public function it_should_be_build_from_hsl_css_representation(): void
    {
        $this->beConstructedThrough('fromCss',['hsl(0,0%,100%)']);

        $this->asCssRGB()->shouldBe('rgb(255,255,255)');
        $this->asCssHSL()->shouldBe('hsl(0,0%,100%)');
    }

    public function it_should_be_build_from_rgb_hex_css_representation(): void
    {
        $this->beConstructedThrough('fromCss',['#fff']);

        $this->asCssRGB()->shouldBe('rgb(255,255,255)');
        $this->asCssHSL()->shouldBe('hsl(0,0%,100%)');
    }

    public function it_should_return_a_new_instance_when_a_color_value_is_modified(): void
    {
        $color = $this
            ->withAlpha(0.5)
            ->withGreen(255)
            ->withRed(255)
            ->withBlue(255)
            ->withHue(0)
            ->withSaturation(0)
            ->withLightness(100)
        ;

        $color->equals($this)->shouldBe(false);
        $color->shouldNotBe($this->getWrappedObject());
    }

    public function it_should_return_the_same_instance_when_a_color_value_is_not_modified(): void
    {
        $color = $this
            ->withAlpha(1.0)
            ->withGreen(255)
            ->withRed(255)
            ->withBlue(255)
            ->withHue(0)
            ->withSaturation(0)
            ->withLightness(100)
        ;

        $color->equals($this)->shouldBe(true);
        $color->shouldBe($this->getWrappedObject());
    }

    public function it_should_throw_if_the_channel_range_value_is_invalid(): void
    {
        $this->shouldThrow(MalformedColor::class)->during('withGreen', [256]);
    }

    public function it_should_throw_if_the_transparency_range_value_is_invalid(): void
    {
        $this->shouldThrow(MalformedColor::class)->during('withAlpha', [-0.0001]);
    }

    public function it_should_be_handling_the_transparency_with_rgba(): void
    {
        $this->beConstructedThrough('fromCss',['rgba(255, 255, 255, 0.5)']);

        $this->asCssRGB(Color::FORMAT_RGB_DEC)->shouldBe('rgba(255,255,255,0.5)');
        $this->asCssRGB(Color::FORMAT_RGB_HEX)->shouldBe('#ffffff80');
        $this->asCssHSL()->shouldBe('hsla(0,0%,100%,0.5)');
    }

    public function it_should_be_handling_the_transparency_with_hexa(): void
    {
        $this->beConstructedThrough('fromCss',['#ffffff80']);

        $this->asCssRGB()->shouldBe('rgba(255,255,255,0.5)');
        $this->asCssHSL()->shouldBe('hsla(0,0%,100%,0.5)');
    }

    public function it_should_be_handling_the_transparency_with_hsla(): void
    {
        $this->beConstructedThrough('fromCss',['hsla(0, 0%, 100%, 0.5)']);

        $this->asCssRGB()->shouldBe('rgba(255,255,255,0.5)');
        $this->asCssHSL()->shouldBe('hsla(0,0%,100%,0.5)');
    }

    public function it_should_normalize_opaque_transparency_from_hsla(): void
    {
        $this->beConstructedThrough('fromCss',['rgba(255,255,255,1.0)']);

        $this->asCssRGB()->shouldBe('rgb(255,255,255)');
        $this->asCssHSL()->shouldBe('hsl(0,0%,100%)');
    }

    public function it_should_normalize_opaque_transparency_from_rgba(): void
    {
        $this->beConstructedThrough('fromCss',['hsla(0, 0%, 100%, 1.0)']);

        $this->asCssRGB()->shouldBe('rgb(255,255,255)');
        $this->asCssHSL()->shouldBe('hsl(0,0%,100%)');
    }


    public function it_should_normalize_opaque_transparency_from_hexa(): void
    {
        $this->beConstructedThrough('fromCss',['#ffff']);

        $this->asCssRGB()->shouldBe('rgb(255,255,255)');
        $this->asCssHSL()->shouldBe('hsl(0,0%,100%)');
    }

    public function it_should_throw_if_the_css_format_is_not_recognized(): void
    {
        $this->beConstructedThrough('fromCss',['hsl(0, 0%, 100%, 1.0)']);

        $this->shouldThrow(MalformedColor::class)->duringInstantiation();
    }
}
