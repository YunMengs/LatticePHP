<?php
namespace Lattice\LatticePck;

/**
 * 点阵字体类
 * Class LatticeFont
 * @package Lattice\LatticePck
 */
class LatticeFont
{
    public $font_v = 12; //中文大小
    public $font_e = 6; //E文字符宽度
    public $spacing = 1; //间距
    public $font_bold = 1; //是否加粗
    public $font_width = 16; // 单字宽度
    public $font_height = 16; // 单字高度度
    public $byteCount = 32;//一个点阵占的字节数
    public $fontFileName = 'st12_16';//字库名字st16_16
    public $fontLimitQuantity = 0;// 字体越界标识 最大极限的序号
    public $spaceNumber = 32; // 空格字符编号
    public $spaceWidth = 7; // 空格统一宽

    public $defaultFontPath = 'extend/Lattice/Resources/font/';

    /**
     * 当字体的文字宽度不等时，设置是否自动获取宽度 1.自动获取 0.固定的等宽
     * @param int $font
     */
    public $autoWidth = 0;

    public function __construct()
    {
        $this->adaption();
    }

    /**
     * 设置字体大小
     * @param int|string $font
     */
    public function setFont($font = 12)
    {
        switch ($font)
        {
            case 12:
                $this->font_v = 12;
                $this->font_e = 6;
                $this->autoWidth = 0;
                $this->fontLimitQuantity = 65535;
                $this->font_width = $this->font_height = 16;
                $this->fontFileName = $this->defaultFontPath . 'st12_16';
            break;
            case 16:
                $this->font_v = 16;
                $this->font_e = 8;
                $this->autoWidth = 0;
                $this->fontLimitQuantity = 65535;
                $this->font_width = $this->font_height = 16;
                $this->fontFileName = $this->defaultFontPath . 'st16_16';
            break;
            case 24:
                $this->font_v = 24;
                $this->font_e = 12;
                $this->autoWidth = 1;
                $this->fontLimitQuantity = 128;
                $this->spaceNumber = 32;
                $this->spaceWidth = 7;
                $this->font_width = $this->font_height = 24;
                $this->fontFileName = $this->defaultFontPath . 'ar24_24';
            break;
            case 40:
                $this->font_v = 40;
                $this->font_e = 40;
                $this->autoWidth = 1;
                $this->fontLimitQuantity = 128;
                $this->spaceNumber = 32;
                $this->spaceWidth = 7;
                $this->font_width = $this->font_height = 40;
                $this->fontFileName = $this->defaultFontPath . 'c40_40';
            break;
            case 48:
                $this->font_v = 48;
                $this->font_e = 48;
                $this->autoWidth = 1;
                $this->fontLimitQuantity = 128;
                $this->spaceNumber = 32;
                $this->spaceWidth = 7;
                $this->font_width = $this->font_height = 48;
                $this->fontFileName = $this->defaultFontPath . 'c48_48';
            break;
        }
        $this->adaption();
    }

    /**
     * 自适应
     */
    public function adaption()
    {
        $this->byteCount = $this->font_height * $this->font_width / 8;
    }

    /**
     * 获取一个字符的位置
     * @param string $string 字符
     * @return int|mixed
     */
    public function UnicodeCodePoints($string)
    {
        $ord = 0;
        if (extension_loaded('mbstring') === true)
        {
            mb_language('Neutral');
            mb_internal_encoding('UTF-8');
            mb_detect_order(array('UTF-8', 'ISO-8859-15', 'ISO-8859-1', 'ASCII'));
            $result = unpack('N', mb_convert_encoding($string, 'UCS-4BE', 'UTF-8'));
            if (is_array($result) === true)
            {
                $ord = $result[1];
            }
        }else{
            $ord = ord($string);
        }
        // 字符越界
        if ($ord > 65535)
        {
            $ord = 0;
        }
        return $ord;
    }

    /**
     * 获取字模并转换成01
     * @param string $str 字符串
     * @return array
     */
    public function getDot(string $str):array
    {
        $dotArray = [];
        $fp = fopen($this->fontFileName, "rb");
        $strLen = mb_strlen($str, 'utf-8');
        for ($z = 0; $z < $strLen; $z++)
        {
            $dot = '';
            $dot_string = '';
            $word = mb_substr($str, $z, 1, 'utf-8');
            $location = $this->UnicodeCodePoints($word);
            if ($this->font_width === 24)
            {
                if (
                    $location <= $this->spaceNumber
                    ||
                    $location > $this->fontLimitQuantity
                )
                {
                    $location = $this->spaceNumber;
                }
            }

            $oldLocation = $location;
            $location *= $this->byteCount; /* 计算汉字字模在文件中的位置 */
            fseek($fp, $location, SEEK_SET);//定位到汉字或字母指针开始的地方
            $dot.= fread($fp, $this->byteCount);//读取32字节的长度，一个字节8位，一行依次放两个字节组成16*16的点阵
            for ($c = 0; $c < $this->byteCount; $c++)
            {
                $dot_string .= sprintf("%08b", ord($dot[$c]));
            }

            // 数组分割
            // 判断是否是半角字符（切割）
            if ($this->UnicodeCodePoints($word) < 700)
                $width = $this->font_e;
            else
                $width = $this->font_v;


            // 将每个字切割成数组合并
            for ($h = 0; $h < $this->font_v; $h++)
            {

                if ($this->autoWidth === 1)
                {
                    $width = self::getCharWidth($dot_string, $this->font_v, $this->font_width);
                }
                //空格单独设置间距
                if ($oldLocation === $this->spaceNumber)
                    $width = $this->spaceWidth;

                // 垃圾ThinkPHP框架
                if (!isset($dotArray[$h]))
                    $dotArray[$h] = '';
                $dot = mb_substr($dot_string, $h * $this->font_width, $width, 'utf-8');
                // 加粗
                if ($this->font_bold)
                {
                    // 按位或 错开
                    // 00100
                    // 01000
                    $dot = ("0".$dot) | $dot;
                }

                $dotArray[$h] .= $dot;

                // 字间距
                if ($this->spacing)
                    $dotArray[$h] .= str_repeat('0', $this->spacing);
            }

        }

        fclose($fp);
        return $dotArray;
    }

    /**
     * 字符串换行分割
     * @param string $str 原始字符串
     * @param int $length 插入的间隔长度, 英文长度
     * @param int $hans_length 一个汉字等于多少个英文的宽度
     * @param string $append 需要插入的字符串
     * @return array
     */
    public static function strWrap(string $str,int $length = 12,int $hans_length = 2,string $append = "<br>"): array
    {
        // $line 记录当前行的长度 // $len utf-8字符串的长度
        $nstr = "";
        $arr = [];
        for ($line = 0, $len = mb_strlen($str, "utf-8"), $i = 0; $i < $len; $i++) {
            $v = mb_substr($str, $i, 1, "utf-8"); // 获取当前的汉字或字母
            $vlen = strlen($v) > 1 ? $hans_length : 1; // 根据二进制长度 判断出当前是中文还是英文
            if (($line + $vlen) > $length) { // 检测如果加上当前字符是否会超出行的最大字数
                // $nstr .= $append; // 超出就加上换行符
                $arr[] = $nstr;
                $nstr = '';
                $line = 0; // 因为加了换行符 就是新的一行 所以当前行长度设置为0
            }
            $nstr .= $v; // 加上当前字符
            $line += $vlen; // 加上当前字符的长度
        }
        // 获取不足一行的字符串
        if ($nstr)
            $arr[] = $nstr;

        return $arr;
    }

    /**
     * 自动获取字模最大宽度
     * @param string $dot_string 0101字符串
     * @param int $fontV 中文大小
     * @param int $font_width 单字宽度
     * @return false|int
     */
    public function getCharWidth(string $dot_string,int $fontV, int $font_width)
    {
        // 将每个字切割成数组合并
        $dotArr = [];
        for ($h = 0; $h < $fontV; $h++) {
            // 垃圾ThinkPHP框架
            if (!isset($dotArray[$h]))
                $dotArray[$h] = '';
            $dotArr[$h] = mb_substr($dot_string, $h * $font_width, $font_width, 'utf-8');
        }
        $str = '0';
        for ($j = 0; $j < $fontV; $j++) {
            $str = $str | $dotArr[$j];
        }
        $l = 0; // 长度
        if ((strrpos($str, '1') | 0) !== 0) {
            $l = strrpos($str, '1') + 2;
        }

        return $l;
    }
}

