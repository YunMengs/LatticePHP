<?php

namespace Lattice\LatticePck;

use think\App;

/**
 * 点阵绘画类 依赖 composer require codeitnowin/barcode
 * Class Lattice
 */
class Lattice
{
    /**
     * 绘画字体类
     * @var array
     */
    public static $fontDraw = null;

    /**
     * 完整的图片数组
     * @var array
     */
    public $image;

    /**
     * @var int 图片宽度
     */
    protected $width = 0;
    /**
     * @var int 图片高度
     */
    protected $height = 0;

    /**
     * 单例模式 获取画字类
     * @return LatticeFont
     */
    public static function getFontDraw()
    {
        if (self::$fontDraw === null)
        {
            self::$fontDraw = new LatticeFont();
            // 字体根路径
            // self::$fontDraw->defaultFontPath = App()->getRootPath() . self::$fontDraw->defaultFontPath;
            // self::$fontDraw->defaultFontPath = "./font/";
        }

        return self::$fontDraw;
    }

    /**
     * 创建空白图片
     * @param int $width
     * @param int $height
     * @param int $color 填充颜色 0.白 1.黑
     */
    public function createBlankImage(int $width, int $height, int $color = 0)
    {
        $this->width = $width;
        $this->height = $height;
        $this->image = array_fill(0, $height, str_repeat($color, $width));
    }

    /**
     * 获取图片总宽度
     * @return int
     */
    public function getWidth()
    {
        $width = 0;
        if ($this->image && isset($this->image[0]))
        {
            $width = strlen($this->image[0]);
        }
        return $width;
    }
    /**
     * 获取图片总高度
     * @return int
     */
    public function getHight()
    {
        $height = 0;
        if ($this->image && is_array($this->image))
        {
            $height = count($this->image);
        }
        return $height;
    }
    
    public function setFont()
    {
        $fontDraw = Lattice::getFontDraw();
        $fontDraw->setFont($size);
    }

    /**
     * 写入文字
     * @param string $text 文字
     * @param array $xy xy坐标与偏移指令
     * @param int $color 字体颜色 1.黑色 0.白色
     * @param int $spacing 字间距
     * @param int $font_bold 1 加粗 0 不加粗
     * @param int $heightSpacing 1 去除上下空白 0 不去除
     * @param int $getType 1 获取点阵数组
     * @return array
     */
    public function text(string $text,array $xy, int $color = 1, int $spacing = 0, int $font_bold = 0,int $heightSpacing = 0, int $getType = 0)
    {
        $fontDraw = Lattice::getFontDraw();
        $fontDraw->spacing = $spacing;
        $fontDraw->font_bold = $font_bold;
        $textArr = $fontDraw->getDot($text);
        if (isset($textArr[0]))
        {
            // 去除上下空白
            if ($heightSpacing === 1)
            {
                foreach ($textArr as $k=>&$v)
                {
                    if (substr_count($v,'0') === strlen($v))
                    {
                        unset($textArr[$k]);
                    }
                }
                $textArr = array_merge($textArr);
            }

            // 颜色反转
            if ($color === 0)
            {
                $this->colorReflection($textArr);
            }
            // 居中效果
            $this->positions($xy, $textArr);

            switch ($getType)
            {
                case 1:
                    return $textArr;
                break;
                default:
                    $this->_insert($textArr, $xy[0], $xy[1], strlen($textArr[0]));
                break;
            }


        }
    }



    /**
     * 创建图形
     * @param string $drawingObjectName 图形名称
     * @param array $p 可变参数
     */
    public function createDrawingObject(string $drawingObjectName, ...$p)
    {
        switch ($drawingObjectName)
        {
            case 'Rectangle':
                LatticeImg::Rectangle(
                    $this,
                    $p[0],
                    $p[1],
                    (array_key_exists(2,$p) ? $p[2] : array(0,0)),
                    (array_key_exists(3,$p) ? $p[3] : 0),
                    (array_key_exists(4,$p) ? $p[4] : 0)
                );
            break;
        }
    }

    /**
     * 插入图片方法
     * @param string $filePath 图片路径
     * @param array $xy xy坐标与偏移指令
     * @param int $color 颜色反射
     * @return void
     */
    function _insertImg(string $filePath, array $xy, int $color = 1)
    {
        list($lattice, $size) = LatticeImg::imgToLattice($filePath);
        // 颜色反转
        if ($color === 0)
            $this->colorReflection($lattice);
        $this->positions($xy, $lattice);
        $this->_insert($lattice, $xy[0], $xy[1], $size[0]);

    }

    /**
     * 坐标偏移居中效果
     *
     * @param array $xy xy坐标与偏移指令
     * @param array $textArr 点阵数组
     * @return void
     */
    function positions(array &$xy, array &$textArr)
    {
        // 居中效果
        if (isset($xy[2]))
        {
            // 数组索引重置序列
            array_splice($textArr, 0, 0);
            switch ($xy[2])
            {
                case 'top-center':
                    $xy[0] = (($this->getWidth() / 2) - ((strlen($textArr[0])) / 2)) + $xy[0];
                break;
                case 'top-right':
                    $xy[0] = ($this->getWidth() - strlen($textArr[0])) + $xy[0];
                break;
                case 'top-left':
                    break;
                case 'bottom-center':
                    $xy[0] = ($this->getWidth() / 2) - (strlen($textArr[0]) / 2) + $xy[0];
                    $xy[1] = $this->getHight() - count($textArr) + $xy[1];
                    break;
                case 'bottom-left':
                    $xy[1] = $this->getHight() - count($textArr) + $xy[1];
                    break;
                case 'bottom-right':
                   $xy[0] = ($this->getWidth() - strlen($textArr[0])) + $xy[0];
                   $xy[1] = $this->getHight() - self::getFontDraw()->font_v + $xy[1];
                break;
                case 'center-right':
                   $xy[0] = ($this->getWidth() - strlen($textArr[0])) + $xy[0];
                   $xy[1] = ($this->getHight() / 2) - (count($textArr) / 2) + $xy[1];
                break;
                case 'center':
                    $xy[0] = ($this->getWidth() / 2) - (strlen($textArr[0]) / 2) + $xy[0];
                    $xy[1] = ($this->getHight() / 2) - (count($textArr) / 2) + $xy[1];
                    break;
                case 'center-left':
                   $xy[1] = ($this->getHight() / 2) - (count($textArr) / 2) + $xy[1];
                break;
            }
        }

    }

    /**
     * 颜色反转
     * @param array $textArr
     * @return void
     */
    public function colorReflection(array &$textArr)
    {
        foreach ($textArr as $key=>&$item)
        {
            $strLen = strlen($item);
            $newString = '';
            for ($i = 0;$i < $strLen;$i++)
            {
                // 按位非
                $newString .= ($item[$i] ^ 1);
            }
            $textArr[$key] = $newString;
        }
    }

    /**
     * 插入方法
     * @param array $arr 插入的点阵数组
     * @param int $x x轴
     * @param int $y y轴
     * @param int $width 宽度
     * @param int $fillColor 宽度不足填充0
     * @return void
     */
    function _insert(array &$arr, int $x, int $y, int $width, int $fillColor = 0): void
    {
        $i = 0;
        foreach ($arr as &$item)
        {
            // $y+$i Y轴每行递增 -1是因为数组从0开始
            if (!isset($this->image[$y + $i]))
            {
                break;
            }
            $this->image[$y + $i] = substr_replace($this->image[$y + $i], $item, $x, $width);
            // 防止不足
            $this->image[$y + $i] = str_pad($this->image[$y + $i], $width, $fillColor);
            $i++;
        }
    }

    /**
     * 数组越界截取
     */
    public function interception()
    {
        // Y轴截取
        $count = count($this->image);
        switch ($count <=> $this->height)
        {
            // 数组越界
            case 1:
                $ele = $this->height - $count; // 溢出了多少
                array_splice($this->image, $this->height - 1, $ele);
            break;
        }

        // X轴截取
        foreach ($this->image as &$item)
        {
            $item = mb_substr($item, 0, $this->width);
        }
    }
}


