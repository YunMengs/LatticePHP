<?php

namespace Lattice\TemplateV2;

use Carbon\Traits\Date;
use Lattice\LatticePck\LatticeFont;
use Lattice\LatticePck\LatticeImg;
use Lattice\LatticePck\LatticeOutput;

class LatticeTemplateZTO extends LatticeTemplateBase
{
    /**
     * 状态1模板
     * @param string $number 箱子编号
     * @param string $mac 二维码小程序地址
     * @return string | void | array
     * @throws \Exception
     */
    public function t1(
        string $number = '---',
        string $mac = '无数据'
    )
    {
        $width = 296;
        $height = 128;
        $lattice = new LatticeOutput();
        $lattice->createBlankImage($width, $height);

        // 空
        $x = 6;
        $y = 6;

        // LatticeImg::Rectangle($lattice, 20, 20, [$x, $y], 1);
        // $lattice->text('空', 16, [$x + 2, $y + 2], 0, 1, 1);

        $fontSize = 12;
        $spacing = 0;
        $number = 'ID：' . $number;
        $strlen = strlen($number) * ($fontSize);
        // 居中尺寸
        $c = ($width / 2) - ($strlen / 2);
        $lattice->text($number, $fontSize, [13, 60], 1, $spacing, 0);
        // 扫码寄件
        $lattice->_insertImg($this->images['smjj'], [12, -14, 'bottom-left']);

        // $sizes = 82;
        $sizes = 98;
        // 绘制二维码
        LatticeImg::QrCode($lattice, $mac, $sizes, [-16, 0, 'center-right'], $this->imgPath, $this->imgName);

        return $this->dumpImg($lattice, '02');
    }

    /**
     * 状态2模板
     * @param array $orderInfo 订单信息
     * @return string | void | array
     */
    public function t2(
        array $orderInfo
    )
    {
        $width = 296;
        $height = 128;
        $lattice = new LatticeOutput();
        $lattice->createBlankImage($width, $height);

        // 物流公司
        $lattice->text($orderInfo['numbering'], 48, [0, 3, 'top-center'], 1, 0, 0, 1);
        // 一维码
        LatticeImg::BarCode($lattice, $orderInfo['courier_number'], 28, [0, 30, 'top-center'], 1, $this->imgPath, $this->imgName, 2);
        // 中转包提示
//        if (!empty($orderInfo['transfer']))
//        {
//          、  $lattice->text($orderInfo['transfer'], 12, [12, 3, 'center-left'], 1, 0, 1, 1);
//        }

        // 韵达快递
        $lattice->text('中通快递', 12, [3, 3, 'center-left'], 1, 0, 0, 1);

        // 物流ID
        $lattice->text($orderInfo['courier_number'], 12, [-12, 3, 'center-right'], 1, 0, 0, 1);

        // 中间黑底
        $x = 0;
        $y = 74;
        $fontY = $y + 2; // 字体Y轴
        $fontSize = 12;
        $bold = 0;
        $spacing = 0;
        LatticeImg::Rectangle($lattice, $width, 16, [$x, $y], 1);
        $lattice->text('收', $fontSize, [$x + 2, $fontY], 0, $spacing, $bold);
        if (mb_strlen($orderInfo['consignee_name']) > 3)
        {
            $orderInfo['consignee_name'] = mb_substr($orderInfo['consignee_name'], 0, 3);
            $orderInfo['consignee_name'] .= '*';
        }
        $cd = ((strlen($orderInfo['consignee_name']) - mb_strlen($orderInfo['consignee_name'], 'UTF-8')) / 2) + mb_strlen($orderInfo['consignee_name'], 'UTF-8') ;
        $x += 18;
        // 收件人
        $lattice->text($orderInfo['consignee_name'], $fontSize, [$x, $fontY], 0, $spacing, $bold);
        // 手机号
        $lattice->text($orderInfo['consignee_phone'], $fontSize, [$x + ($cd * 8), $fontY], 0, $spacing, $bold);
        // 打印时间
        $date = Date('Y-m-d H:i');
        $lattice->text($date, $fontSize, [$x - 20, 18, 'center-right'], 0, $spacing, $bold);

        // 收货地址
        $x = $addX = 3;
        $y = 96;
        $fontSize = 12;
        $addressArray = LatticeFont::strWrap($orderInfo['address'], 48, 2);
        $i = 0;
        foreach ($addressArray as $key => &$item) {
            // 渲染收货地址
            $lattice->text($item, $fontSize, [$x, $y + $i], 1);
            $i += 4 + 12;
        }

        // 数组越界截取
        $lattice->interception();

        return $this->dumpImg($lattice, '03');

    }


    /**
     * 状态3模板
     * @param array $data 箱子信息和物流订单信息
     * @return string | void | array
     */
    public function t3(
        array $data
    )
    {
        $width = 296;
        $height = 16;
        $lattice = new LatticeOutput();
        $lattice->createBlankImage($width, $height, 1);

        $fontSize = 12;
        $spacing = 0;
        $y = 1;
        // 物流公司
        $express = '中通快递' . $data['order_info']['courier_number'];
        $lattice->text($express, $fontSize, [4, $y, 'center-left'], 0, $spacing, 0);
        // 收件人尾号
        $text = '收 ' . $data['order_info']['consignee_name'] . '* 尾号' . $data['order_info']['consignee_phone'];
        $lattice->text($text, $fontSize, [-4, $y, 'center-right'], 0, $spacing, 0);

        return $this->dumpImg($lattice, '05');
    }
    /**
     * 状态3原始模板
     * @param string $mac
     * @param array  $data 箱子数据
     * @return string | void | array
     * @throws \Exception
     */
    public function t31(string $mac, $data)
    {
        $width = 296;
        $height = 128;
        $lattice = new LatticeOutput();
        $lattice->createBlankImage($width, $height, 0);

        $fontSize = 12;
        $spacing = 0;

        // 绘制黑边
        LatticeImg::Rectangle($lattice, $width, 18, [0, 0], 1);

        $lattice->text('ID：' . $data['case_number'], $fontSize, [7, 47], 1, $spacing);

        // 温馨提示
        $x = 7;
        $y = 69;
        $fontSize = 12;
        $spacing = 0;
        // 小主人
        $lattice->text('小主人', $fontSize, [$x, $y], 1, $spacing);
        $lattice->text('您的包裹来啦~', $fontSize, [$x, $y + 20], 1, $spacing);
        $lattice->text('微信扫描右边的二维码开锁~', $fontSize, [$x, $y + 40], 1, $spacing);

        // 扫码开锁
        $x = 171;
        $y = 24;
        $fontSize = 16;
        $spacing = 0;
        $fontSpacing = 17;
        $bold = 1;
        $lattice->text('扫', $fontSize, [$x, $y], 1, $spacing, $bold);
        $lattice->text('码', $fontSize, [$x, $y + $fontSpacing * 1], 1, $spacing, $bold);
        $lattice->text('开', $fontSize, [$x, $y + $fontSpacing * 2], 1, $spacing, $bold);
        $lattice->text('锁', $fontSize, [$x, $y + $fontSpacing * 3], 1, $spacing, $bold);

        $sizes = 98;
        // 绘制二维码
        LatticeImg::QrCode($lattice, $mac , $sizes, [-6, 8, 'center-right'], $this->imgPath, $this->imgName);

        // 数组越界截取
        $lattice->interception();

        return $this->dumpImg($lattice, '04');

    }

}
