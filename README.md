# Bakame Color

A color manipulation library in PHP that focused on CSS supported format.

```php
<?php

// First: import needed class
use Bakame\Color\Color;

$color = Color::fromCss('#FF8000');

$color->red(); //return 255
$color->green(); //return 128
$color->blue(); //return 0
$color->alpha(); //return 1.0
$color->hue(); //return 30
$color->saturation(); //return 100
$color->lightness(); //return 50

$color->asCssHSL(); //returns 'hsl(30,100%,50%)'
$color->asCssRGB(Color::FORMAT_RGB_DEC); //returns 'rgb(255,128,0)'
$color->asCssRGB(Color::FORMAT_RGB_HEX); //returns '#ff8000'
```

You can manipulate the color using the standard LESS manipulation methods.

```php
<?php

use Bakame\Color\Color;
use Bakame\Color\Manipulator;

$color = Color::fromCss('#369');
$saturate = Manipulator::saturate($color, 20);
$desaturate = Manipulator::desaturate($color, 20);
$tint = Manipulator::tint($color, 20);
$shade = Manipulator::shade($color, 20);
$fadeIn = Manipulator::fadeIn($color, 20);
$fadeOut = Manipulator::fadeOut($color, 20);
$lighten = Manipulator::lighten($color, 20);
$darken = Manipulator::darken($color, 20);
$invert = Manipulator::invert($color);
$rotate = Manipulator::spin($color, 45);
$blend = Manipulator::mix($color, Color::fromCss('#0cf'));
$grayscale = Manipulator::grayscale($color);
```

All methods returns a new instance of the `Color` class.
