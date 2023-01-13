<?php

namespace Lattice\LatticePck;

/**
 * 点阵输出类
 * Class LatticeOutput
 */
class LatticeOutput
{
    public $lattice;

    public function __construct(Lattice $lattice)
    {
        $this->lattice = $lattice;
    }

    /**
     * 用点阵图生成完整的HTML代码，并返回
     * @return string
     */
    public function getHTML(): string
    {
        return $this->getHead() . $this->getBinaryOutHtml() . $this->getFoot();
    }

    /**
     * 返回点阵图数组
     * @return array
     */
    public function getImageArray(): array
    {
        return $this->lattice->image;
    }

    /**
     * 二进制输出方法 HTML 把当前视图输出
     *
     * @return string
     */
    public function getBinaryOutHtml(): string
    {
        $str="<html><body><div class='k'>";
        foreach ($this->lattice->image as $item)
        {
            $str .= $this->getBinaryOutRow($item);
        }
        $str .= "</div></body>
</html>";
        return $str;
    }

    /**
     * 二进制输出方法 HTML 输出一行视图
     * @param string $string
     * @return string
     */
    public function getBinaryOutRow(string $string): string
    {
        $strLen = mb_strlen($string);
        $html = [
            "<div></div>",
            "<div class='h'></div>",
        ];
        $str="<div class='g'>";
        for($i=0;$i<$strLen;$i++)
        {
            $str .= $html[$string[$i]];
        }
        $str.="</div>";

        return $str;
    }

    public function getHead()
    {
        return "<!DOCTYPE\">
        <html xmlns=\"http://www.w3.org/1999/xhtml\">
        <head>
        <meta name=\"viewport\" content=\"width=device-width,user-scalabl
        e=no\">
        <title>测试</title>
        <style>
            .k {
                margin: 0px;
                padding: 0px;
                width: 296px;
                height:128px;
                background-color: #F8F8F8;
                float: none;
                border: 2px solid #999999;
            }
            .g {
                margin: 0px;
                padding: 0px;
                width: 100%;
                float: none;
                height: 1px;
            }
            .h{
                background-color: #000000;
            
            }
            .g div{
                margin: 0px;
                padding: 0px;
                width: 1px;
                float: left;
                height: 1px;
                position: relative;
            }
            .g div.h{
                background-color: #000000;
            }
        </style>
        </head>
        <body>";
    }

    public function getFoot()
    {
        return '</body>
        </html>';

    }

    /**
     * 将二进制数据转换16进制数串的函数（并反转）
     * @param array $olbImage 点阵数组 默认整张画布
     * @param bool $strrev 是否反转图片（将图片倒置并且竖直）
     * @return string $_32hexa
     */
    public function toHexa(array $olbImage = [], bool $strrev = false): string
    {
        if (!$olbImage)
        {
            $olbImage = $this->lattice->image;
        }

        // 颜色反转
        $arr = [1, 0];
        foreach ($olbImage as &$item) {
            $strLen = mb_strlen($item, $this->encoding);
            for ($i = 0; $i < $strLen; $i++) {
                $item[$i] = $arr[$item[$i]];
            }
        }

        if($strrev)
        {
            // 倒置并竖着
            $image = [];
            // 一行长度
            $strLen = mb_strlen($olbImage[0]);
            foreach ($olbImage as $key => &$item) {
                // 倒置
                $item = strrev($item);
                // 并竖着
                for ($i = 0; $i < $strLen; $i++) {
                    try {
                        $image[$i] .= $item[$i];
                    } catch (\Exception $e) {
                        $image[$i] = $item[$i];
                    }
                }
            }
        }else{
            $image = $olbImage;
        }

        $binary = implode('', $image);
        $_32hexa = "";
        $index = -4; // 从后面4个开始取
        $long = mb_strlen($binary);
        // $i = 1;
        while (abs($index) <= $long) {
            $a = substr($binary, $index, 4);
            $index = $index - 4;
            $_32hexa = base_convert($a, 2, 16) . $_32hexa;
            // 每2个加 "0x" 和 逗号
            // if ($i % 2 === 0)
            // {
            //     $_32hexa = ',0x' . $_32hexa;
            // }
            // $i++;
        }

        /** @var $str */
        $str = ltrim($_32hexa, ',');

        // 填充
        // $n = ($this->image_width * $this->image_height) / 4;
        // $str = str_pad($str, $n, 'f');

        return $str;
    }

}
