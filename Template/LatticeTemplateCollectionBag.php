<?php

namespace Lattice\Template;

use Lattice\LatticePck\LatticeFont;
use Lattice\LatticePck\LatticeImg;
use Lattice\LatticePck\LatticeOutput;

/**
 * 集包袋类
 * Class LatticeTemplateCollectionBag
 * @package Lattice\Template
 */
class LatticeTemplateCollectionBag extends LatticeTemplateBase
{
    /**
     * 状态1模板 空置中
     * @param string $rfid rfid
     * @return string | void | array
     * @throws \Exception
     */
    public function t1(
        string $rfid,
        $param
    )
    {
        $width = 296;
        $height = 128;
        $lattice = new LatticeOutput();
        $lattice->createBlankImage($width, $height);

        $x = 0;
        $y = 5;

        // 空置中
        $lattice->_insertImg(App()->getRootPath() . 'public/static/images/collection_bag_img/kzz.png', [$x, $y, 'top-center']);

        $mac = str_replace(':', '', $param['mac']);
//        $mac = mb_substr($mac, 2);
        // 一维码
        LatticeImg::BarCode($lattice, $mac . $rfid, 58, [0, $y + 47, 'top-center'], 1, $this->imgPath, $this->imgName, 1);

        // rfid
        $lattice->text($rfid, 16, [$x, $y + 105, 'top-center'], 1, 1, 1);

        foreach($lattice->image as &$item)
        {
            $item = strrev($item);
        }

        $lattice->image = array_reverse($lattice->image);

        return $this->subcontract5kb($lattice->image, '02');
    }



}
