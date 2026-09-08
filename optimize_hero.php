<?php
require 'vendor/autoload.php';
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;

$manager = new ImageManager(new Driver());
$image = $manager->decode('public/public.png');
$image->scaleDown(width: 1920);
$image->encode(new WebpEncoder(80))->save('public/public.webp');
echo "Image optimized successfully.\n";
