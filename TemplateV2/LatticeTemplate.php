<?php

namespace Lattice\TemplateV2;

use Lattice\LatticePck\LatticeFont;
use Lattice\LatticePck\LatticeImg;
use Lattice\LatticePck\LatticeOutput;

/**
 * Class LatticeTemplate
 * @package Lattice\Template
 */
class LatticeTemplate extends LatticeTemplateBase
{
    /**
     * 状态1模板
     * @param string $number 箱子编号
     * @param string $mac 二维码小程序地址
     * @param string|null $version
     * @param array $data
     * @return string | void | array
     * @throws \Exception
     */
    public function t1(
        string $number = '---',
        string $mac = '无数据',
        string $version = null,
        array $data = array()
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


        if($version)
        {
            // 温度碰撞显示
            $string = 'V：' . $version;
            if(!empty($data['is_collision']) && $data['is_collision'] == 1)
            {
                $string .= ' C ';
            }
            if(!empty($data['is_humidity']) && $data['is_humidity'] == 1)
            {
                $string .= ' T ';
            }

            // 硬件版本号
            $lattice->text($string, $fontSize, [13, 45], 1, $spacing, 0);
        }

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
     * @param string $express 物流
     * @param string $expressId 物流单号
     * @param int $kg ；千克
     * @param string $number 编号
     * @param string $time 时间
     * @param string $consignee 收货人
     * @param string $tel 手机号
     * @param string $address 收货地址
     * @param string $detial
     * @param string $payDe
     * @param array $data
     * @return string | void | array
     */
    public function t2(
        string $express = '---',
        string $expressId = '---',
        int $kg = 0,
        string $number = '---',
        string $time = '---',
        string $consignee = '---',
        string $tel = '---',
        string $address = '---',
        string $detial = '',
        string $payDe = '',
        array $data = []
    )
    {
        $width = 296;
        $height = 128;
        $lattice = new LatticeOutput();
        $lattice->createBlankImage($width, $height);

        // 物流公司
        $lattice->text($express, 12, [3, 3], 1, 0, 0);

        // 一维码
        LatticeImg::BarCode($lattice, $expressId, 18, [0, 0, 'top-center'], 1, $this->imgPath, $this->imgName);
        // 物流ID
        $lattice->text($expressId, 12, [0, 18, 'top-center'], 1, 0, 0);

        $kg .= 'kg';
        $fontSizes = 12;
        $lattice->text($kg, $fontSizes, [0, 0, 'top-right'], 1, 0, 0);
        $str = '深冷2/3';
        $lattice->text($str, $fontSizes, [0, 12, 'top-right'], 1, 0, 0);

        // 编号
        $lattice->text($number, 24, [0, 31], 1, 0, 0);

        // 状态图：补 陆 换 电 预约
        $y = 30;
        $x = 0;
        $len = 14;
        $yb = 1;
        // 服务产品：次日达半日达预约配送等信息
        if (!empty($data['service_products']))
        {
            $arr = ['次日达', '半日达', '预约', '准时达'];
            if (in_array($data['service_products'], $arr)){
                $lattice->_insertImg($this->images[$data['service_products']], [$x, $y, 'top-right']);
                $yb += 1;
            }

        }else{
            //没有次日达
            $x += 13;
        }
        $x += 1;
        // 发票类型
        if (!empty($data['invoice_iden']))
        {
            $arr = ['电', '普'];
            if (in_array($data['invoice_iden'], $arr)){
                $lattice->_insertImg($this->images[$data['invoice_iden']], [$x - ($len * $yb), $y, 'top-right']);
                $yb += 1;
            }
            // dd($x - ($len * $yb));
        }

        // 换货
        if (!empty($data['exchange_mark']))
        {
            $arr = ['换'];
            if (in_array($data['exchange_mark'], $arr)){
                $lattice->_insertImg($this->images[$data['exchange_mark']], [$x - ($len * $yb), $y, 'top-right']);
                $yb += 1;
            }

        }

        // 运输方式: 空陆海
        if (!empty($data['type_shipping']))
        {
            // 空 陆 海
            $arr = ['空', '陆', '海'];
            if (in_array($data['type_shipping'], $arr)){
                $lattice->_insertImg($this->images[$data['type_shipping']], [$x - ($len * $yb), $y, 'top-right']);
                $yb += 1;
            }

        }

        // 面单补打标记
        if (!empty($data['replenishment']))
        {
            $arr = ['补货'];
            if (in_array($data['replenishment'], $arr)){
                $lattice->_insertImg($this->images[$data['replenishment']], [$x - ($len * $yb) - 1, $y, 'top-right']);
            }

        }

        // 时间
        $x = 0;
        $y = 45;
        $fontSize = 12;
        $timeLength = mb_strlen($time, 'UTF-8') * 6 + 2; // time的长度
        $lattice->text($time, $fontSize, [$x, $y, 'top-right'], 1, 0, 0);
        // T
        $lattice->_insertImg($this->images['t'], [$x - $timeLength, $y + 1, 'top-right']);

        // 中间黑底
        $x = 0;
        $y = 58;
        $fontY = $y + 2; // 字体Y轴
        $fontSize = 12;
        $bold = 1;
        $spacing = 0;
        LatticeImg::Rectangle($lattice, $width, 16, [$x, $y], 1);
        $lattice->text('收', $fontSize, [$x + 2, $fontY], 0, $spacing, $bold);
        // 王冠
        $lattice->_insertImg($this->images['wg'], [$x + 17, $fontY - 1]);
        // 收件人与尾号
        $lattice->text($consignee, $fontSize, [$x + 33, $fontY], 0, $spacing, $bold);
        // 手机号tel 12345678910转1234
        $lattice->text($tel, $fontSize, [-1, $fontY, 'top-right'], 0, $spacing, $bold);

        // 收货地址
        $x = $addX = 3;
        $y = 80;
        $fontSize = 12;
        $addressArray = LatticeFont::strWrap($address, 48, 2);
        $i = 0;
        foreach ($addressArray as $key => &$item) {
            // 有备注就不要第三行了
            if ($detial && $key === 2) break;
            // 渲染收货地址
            $lattice->text($item, $fontSize, [$x, $y + $i], 1);
            $i += 4 + 12;
        }

        // 有备注且第三行存在的情况下绘制备注
        if ($detial) {
            $y += 34;
            $fontSize = 12;
            // 线
            LatticeImg::Line($lattice, [0, $y - 1], $width);
            // 备
            $lattice->_insertImg($this->images['b'], [0, $y]);
            // 写备注
            $lattice->text($detial, $fontSize, [16, $y + 1]);

            if (isset($addressArray[2])) {
                // 绘制白边
                $x = -4;
                $y -= 18;
                LatticeImg::Rectangle($lattice, 13, 13, [$x - 13, $y], 0);
                // 绘制箭头
                $lattice->_insertImg($this->images['jt'], [$x, $y, 'top-right']);
            }

            // 绘制白边
            LatticeImg::Rectangle($lattice, 2, 14, [0, -2, 'bottom-right'], 0);
        }

        // 数组越界截取
        $lattice->interception();

        $strlen = 0; // 到付的长度
        // 有到付的情况下绘制到付
        if ($payDe) {

            $fontSize = 12;
            $text = '到付:' . $payDe;
            $strlen = (mb_strlen($payDe, 'UTF-8') * 7) + ($fontSize * 3);
            // 绘制白边
            LatticeImg::Rectangle($lattice, 2, 16, [-$strlen, -2, 'bottom-right'], 0);
            // 绘制黑边
            LatticeImg::Rectangle($lattice, $strlen, 16, [0, -3, 'bottom-right'], 1);
            // 写到付
            $lattice->text($text, $fontSize, [-1, -1, 'bottom-right'], 0, 0, 1);

            // 绘制白边
            LatticeImg::Rectangle($lattice, $strlen, 2, [0, -5, 'bottom-right'], 0);

        }

        // 没备注绘制小箭头
        if (!$detial && isset($addressArray[2])) {
            if ($payDe) {
                // 收货地址长度
                $addStrlen = ((strlen($addressArray[2]) + mb_strlen($addressArray[2], 'UTF-8')) / 2) * 6;
                $addStrlen += $addX;
                // 到付长度
                $payDLen = $lattice->getWidth() - $strlen;
                // 计算第三行是否被覆盖 没覆盖绘制小箭头
                if ($addStrlen > $payDLen) {
                    goto JT;
                }
            } else {
                goto JT;
            }

            if (false) {
                JT:
                // 绘制白边
                LatticeImg::Rectangle($lattice, 16, 15, [-$strlen, -5, 'bottom-right'], 0);
                // 绘制箭头
                $lattice->_insertImg($this->images['jt'], [-$strlen - 3, -3, 'bottom-right']);
            }


        }

        // 数组越界截取
        $lattice->interception();

        return $this->dumpImg($lattice, '03');

    }


    /**
     * 状态3模板
     * @param string $express 物流
     * @param string $expressId 物流单号
     * @param string $consignee 收货人
     * @return string | void | array
     */
    public function t3(
        string $express = '---',
        string $expressId = '---',
        string $consignee = '---'
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
        $express .= $expressId;
        $lattice->text($express, $fontSize, [4, $y, 'center-left'], 0, $spacing, 0);
        // 收件人尾号
        $text = '收 ' . $consignee;
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
