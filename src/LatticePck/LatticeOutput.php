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
    public function getArray(): array
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


}
