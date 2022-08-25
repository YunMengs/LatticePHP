<?php
require 'vendor/autoload.php';

use Lattice\LatticePck\LatticeImg;
use Lattice\LatticePck\LatticeOutput;




$width = 296;
$height = 128;
$lattice = new LatticeOutput();
$lattice->createBlankImage($width, $height);

// 空
$x = 6;
$y = 6;

// LatticeImg::Rectangle($lattice, 20, 20, [$x, $y], 1);
// $lattice->text('空', 16, [$x + 2, $y + 2], 0, 1, 1);

// 空
$x = 6;
$y = 6;

LatticeImg::Rectangle($lattice, 20, 20, [$x, $y], 1);
$lattice->text('空', 16, [$x + 2, $y + 2], 0, 1, 1);

return dumpImg($lattice, '02');


/**
 * 打印
 * @param $lattice
 * @param string $command
 * @return array
 */
function dumpImg($lattice, string $command = '02')
{
    switch (2) {
        case 1:
            $lattice->getHtml();
            echo($lattice->getBinaryOutHtml());
            $lattice->getFoot();
            break;
        case 2:
            echo '<pre>';
            print_r($lattice->image);
            break;
        case 3:
            echo "<img src='/static/images_temp/1.png'>";
            break;
    }
}