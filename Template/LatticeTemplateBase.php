<?php

namespace Lattice\Template;

use Lattice\LatticePck\LatticeFont;
use Lattice\LatticePck\LatticeImg;
use Lattice\LatticePck\LatticeOutput;

class LatticeTemplateBase
{

    /**
     * @var int 输出模式 0.点阵数组 1.html 2.点阵图 3.图片
     */
    public $dump = 0;
    public $imgPath = null;
    /**
     * @var string
     */
    public $imgName = null;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var string[]
     */
    protected $images;
    public $encoding = 'UTF-8';
    /**
     * @var int|mixed
     */
    public $status;

    public function __construct()
    {
        // $this->imgName = time() . mt_rand(0, 100);
        $this->imgName = 1 . mt_rand(0, 9999);
        $this->path = App()->getRootPath();
        // $this->path = $_SERVER['HTTP_HOST'];
        $this->imgPath = $this->path . 'public/static/images_temp/';
        // $this->imgPath = $this->path . 'public/static/images_temp/'. date('Ymd', time())."/";
        if (!file_exists($this->imgPath)) {
            // 检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($this->imgPath, 0700, true);
        }
        // 图片零件
        $path = $this->path . 'public/static/images/status_img/';
        // $path = $this->path . '/static/images/status_img/';
        $this->images = [
            'smjj' => $path . 'smjj.png',
            '换' => $path . 'huang.png',
            '电' => $path . 'dian.png',
            '普' => $path . 'pu.png',
            '预约' => $path . 'yy.png',
            '半日达' => $path . 'br.png',
            '次日达' => $path . 'cr.png',
            '准时达' => $path . 'zs.png',
            '海' => $path . 'h.png',
            '陆' => $path . 'lu.png',
            '空' => $path . 'k.png',
            '补货' => $path . 'bh.png',
            't' => $path . 't.png',
            'wg' => $path . 'wg.png',
            'jt' => $path . 'jt.png',
            'b' => $path . 'b.png',
        ];
    }

    /**
     * 打印
     * @param $lattice
     * @param string $command
     * @return array
     */
    public function dumpImg($lattice, string $command = '02')
    {
        switch ($this->dump) {
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
                echo "<img src='/static/images_temp/{$this->imgName}.png'>";
                break;
            default:
                // 分包
                return $this->subcontract($lattice->image, $command);
        }
    }

    /**
     * 分包+命令+CRC
     * @param array|string $image 点阵数组
     * @param string $command 命令
     * @param int $size 分包大小 默认1024字节
     * @return array $array
     */
    function subcontract($image, string $command, int $size = 1024): array
    {
        $size *= 2; // C语言0xff算一个字符
        if (is_string($image))
        {
            $impload = $image;
        }else{
            $impload = $this->toHexa($image);
        }

        // 数据总长
        $strlen = strlen($impload) / 2;
        // 16进制数据总长
        $len = str_pad(dechex($strlen), 4, 0, STR_PAD_LEFT);
        $j = 1;
        $arr = [];
        for ($i = 0; ($i / 2) < $strlen; $i += $size) {
            // 截取 1kb
            $str = mb_substr($impload, $i, $size);
            // 报序号
            $n = str_pad($j, 2, 0, STR_PAD_LEFT);
            $j++;
            $crc16 = dechex(self::crc16($str));
            $crc16 = str_pad($crc16, 4, 0, STR_PAD_LEFT);
            $arr[] = 'aa' . $command . $len . $n . $crc16 . $str;
            // $arr[] = $str;
        }

        return [
            'count' => $len,
            'data' => $arr
        ];
    }

    /**
     * 分包+命令+CRC
     * @param array|string $image 点阵数组
     * @param string $command 命令
     * @param int $size 分包大小 默认1024字节
     * @return array $array
     */
    function subcontract5kb($image, string $command, int $size = 1024): array
    {
        $size *= 2; // C语言0xff算一个字符
        if (is_string($image))
        {
            $impload = $image;
        }else{
            $impload = $this->toHexa($image);
        }

        // 数据总长
        $strlen = strlen($impload) / 2;
        // 16进制数据总长
        $len = str_pad(dechex($strlen), 4, 0, STR_PAD_LEFT);
        $crc16 = dechex(self::crc16($impload));
        $crc16 = str_pad($crc16, 4, 0, STR_PAD_LEFT);
        $string = 'aa' . $command . '1280' . '01' . $crc16 . $impload;

        return [
            'count' => $len,
            'data' => [$string]
        ];
    }

    /**
     * 将二进制数据转换16进制数串的函数
     * @param array $olbImage 点阵数组
     * @return string $_32hexa
     */
    public function toHexa(array $olbImage): string
    {
        // 颜色反转
        $arr = [1, 0];
        foreach ($olbImage as &$item) {
            $strLen = mb_strlen($item, $this->encoding);
            for ($i = 0; $i < $strLen; $i++) {
                $item[$i] = $arr[$item[$i]];
            }
        }
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

    /**
     * CRC16
     * @param string $pchMsg 校验的字符串
     * @return string $_32hexa
     */
    public static function crc16($pchMsg)
    {
        $dataLen = strlen($pchMsg) / 2;
        $wCRCTalbeAbs = [
            0x0000, 0xCC01, 0xD801, 0x1400, 0xF001,
            0x3C00, 0x2800, 0xE401, 0xA001, 0x6C00,
            0x7800, 0xB401, 0x5000, 0x9C01, 0x8801,
            0x4400
        ];

        $wCRC = 0xFFFF;
        for ($i = 0; $i < $dataLen; $i++)
        {
            $chChar = hexdec(mb_substr($pchMsg, $i * 2, 2));
            $wCRC = $wCRCTalbeAbs[($chChar ^ $wCRC) & 15] ^ ($wCRC >> 4);
            $wCRC = $wCRCTalbeAbs[(($chChar >> 4) ^ $wCRC) & 15] ^ ($wCRC >> 4);
        }

        return $wCRC;
    }

}
