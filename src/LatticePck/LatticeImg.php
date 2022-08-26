<?php

namespace Lattice\LatticePck;

use Lattice\Utils\CString;
use CodeItNow\BarcodeBundle\Utils\BarcodeGenerator;
use CodeItNow\BarcodeBundle\Utils\QrCode;
use Exception;

/**
 * 点阵绘画类
 * Class Lattice
 */
class LatticeImg
{
    protected $image = [];

    /**
     * 创建矩形
     * @param Lattice $lattice Lattice对象
     * @param int $width 宽度
     * @param int $height 高度
     * @param array $xy XY坐标 [0,  0]
     * @param int $fillColor 填充颜色 0.白 1.黑
     * @param int $borderSize 边框大小
     * @param int $borderColor 边框颜色
     * @return void
     */
    public static function Rectangle(Lattice $lattice, int $width, int $height, array $xy, int $fillColor = 0, int $borderSize = 0, int $borderColor = 1): void
    {
        $image = array_fill($fillColor, $height, str_repeat($fillColor, $width));
        $lattice->positions($xy, $image);
        $lattice->_insert($image, $xy[0], $xy[1], $width);
    }

    /**
     * 创建线 （只能是直线或竖线）
     * @param Lattice $lattice Lattice对象
     * @param array $xy XY坐标与偏移指令 [0,  0, 'center']
     * @param int $long 线长度
     * @param string $hv 横竖线 h.横线 v.竖线
     * @param int $thickness 像素厚度。默认为1
     * @param int $color 线条颜色 1.黑 0.白 默认为1
     * @return void
     */
    public static function Line(Lattice $lattice, array $xy, int $long, $hv = 'h', int $thickness = 1, int $color = 1)
    {
        if ($hv === 'h')
        {
            $line = [str_repeat($color, $long)];
            $lattice->_insert($line, $xy[0], $xy[1], $long, $color ^ 1);
        }
    }

    /**
     * Barcode生成条纹码
     * @param Lattice $lattice 点阵类
     * @param string $text 字符串
     * @param int $thickness 高度厚度
     * @param array $xy xy坐标
     * @param int $fillColor 条纹码颜色
     * @param string $filePath 文件路径
     * @param string $fileName 文件名
     * @param int $thick 是否需要加粗
     * @return void
     */
    public static function BarCode(Lattice $lattice,string $text, int $thickness, array $xy, int $fillColor, string $filePath, string $fileName = '', $thick = 1)
    {
        // 一维码
        $filePath = self::generateBarCode($text, false, 12, $filePath, $fileName,'', $thickness);
        list($barCode) = self::imgToLattice($filePath);
        // dd($barCode);
        // 是否需要加粗
        if ($thick !== 1)
        {
            $string = $barCode[0];
            for ($i = 1;$i < $thick;$i++)
                $string = CString::MergeBetween($string, $string);
            $barCode = array_fill(0, count($barCode), $string);
        }

        // 坐标偏移
        $lattice->positions($xy, $barCode);
        // 插入
        $lattice->_insert($barCode, $xy[0], $xy[1], strlen($barCode[0]));
    }

    /**
     * Barcode生成二维码
     * @param Lattice $lattice 点阵类
     * @param string $text 字符串
     * @param int $size 尺寸
     * @param array $xy xy坐标
     * @param string $filePath 生成二维码的文件路径
     * @param string $fileName 生成二维码的文件名 默认时间戳
     * @return void
     * @throws Exception
     */
    public static function QrCode(Lattice $lattice,string $text, int $size, array $xy, string $filePath, string $fileName = '')
    {
        // 一维码
        $filePath = self::generateQrCode($text, $size, 0, $filePath, $fileName);
        list($barCode, $size) = self::imgToLattice($filePath);
        // 坐标偏移
        $lattice->positions($xy, $barCode);
        // 插入
        $lattice->_insert($barCode, $xy[0], $xy[1], $size[0]);
    }


    /**
     * QRCode生成二维码
     * @param string $str 字符串
     * @param int $size 尺寸
     * @param int $padding 内边距
     * @param string $filePath 图片储存路径
     * @param string $fileName 图片名称 默认时间戳存储
     * @param string $imageType 图片类型 'png' 'gif' 'jpeg' 'wbmp'
     * @param string $errorCorrectionLevel 图片质量等级 LOW，MEDIUM，QUARTILE，HIGH
     * @return string 图片路径
     * @throws Exception
     */
    public static function generateQrCode(
        string $str,
        int $size = 300,
        int $padding = 10,
        string $filePath = '',
        string $fileName = '',
        string $imageType = QrCode::IMAGE_TYPE_PNG,
        string $errorCorrectionLevel = 'high'
    ): string
    {
        $qrCode = new QrCode();
        $qrCode
            ->setText($str)
            ->setSize($size)
            ->setPadding($padding)
            ->setErrorCorrection($errorCorrectionLevel)
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            // ->setLabel('Scan Qr Code')
            ->setLabelFontSize(16)
            ->setImageType($imageType)
        ;
        $baseImage = "data:".$qrCode->getContentType().';base64,'.$qrCode->generate()."'";
        if (!$fileName)
        {
            $fileName = mt_rand(0, 10).time();
        }

        $filePath = self::base64_image_content($baseImage, $filePath, $fileName);

        return $filePath;
    }

    /**
     * Barcode生成条纹码
     * @param string $text 数据
     * @param bool $isLabel 是否显示标签
     * @param int $fontSize 字体大小
     * @param string $filePath 图片路径
     * @param string $filename 图片名称
     * @param string $fontPath 字体路径 留空则使用默认
     * @param int $thickness 厚度高度
     * @return string
     */
    public static function generateBarCode(string $text = '空数据', bool $isLabel = false, int $fontSize = 12, string $filePath = '', string $filename = '', string $fontPath = '', int $thickness = 18): string
    {
        $barcode = new BarcodeGenerator();
        $barcode->setText($text);
        $barcode->setType(BarcodeGenerator::Code128);
        $barcode->setScale(1);
        $barcode->setThickness($thickness);
        if($isLabel)
        {
            $barcode->setFontSize($fontSize);
            $barcode->setLabel($text);
        }else{
            $barcode->setFontSize(0);
            $barcode->setLabel('');
        }

        if ($fontPath)
            $barcode->setFont($fontPath);

        $baseImage = 'data:image/png;base64,'.$barcode->generate()."'";
        if (!$filename)
        {
            $filename = mt_rand(0, 1000).time();
        }

        $filePath = self::base64_image_content($baseImage, $filePath, $filename);

        return $filePath;
    }

    /**
     * [将Base64图片转换为本地图片并保存]
     * @E-mial 2665468087@qq.com
     * @param string $base64_image_content 要保存的Base64
     * @param string $file_path 文件路径
     * @param string $file_name 文件名 默认时间戳
     * @return bool|string
     */
    public static function  base64_image_content(string $base64_image_content,string $file_path,string $file_name = ''){
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
            $type = $result[2];


            $new_file = $file_path;
            if($new_file && !file_exists($new_file)){
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0777, true);
            }
            // 文件名 默认时间戳
            if (!$file_name)
            {
                $file_name = time();
            }
            $new_file = $new_file.$file_name.".{$type}";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
                return $file_path . $file_name . ".{$type}";
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 图片转点阵（黑白）
     * @param string $imgPath
     * @param int $type 0.灰变黑 1.灰变白 2.灰
     * @return array
     */
    public static function imgToLattice(string $imgPath, int $type = 1): array
    {
        $size = getimagesize($imgPath);// 得到图片的信息
        switch ($size['mime'])
        {
            case 'image/jpeg':
                $im = imagecreatefromjpeg($imgPath);// 創建一張圖片
            break;
            case 'image/png':
                $im = imagecreatefrompng($imgPath);// 創建一張圖片
            break;
            case 'image/gif':
                $im = imagecreatefromgif($imgPath);// 創建一張圖片
            break;

        }

        // 储存二进制数组
        $lattice = [];
        $white = [
            'red' => 255,
            'green' => 255,
            'blue' => 255,
            'alpha' => 0,
        ];
        $black = [
            'red' => 0,
            'green' => 0,
            'blue' => 0,
            'alpha' => 0,
        ];
        for ($i = 0; $i < $size[1]; ++ $i) {
            $lattice[$i] = '';
            for ($j = 0; $j < $size[0]; ++$j) {
                $rgb = imagecolorat($im, $j, $i);          //取得某像素的颜色索引值
                $rgbArr = imagecolorsforindex($im, $rgb);

                switch ($type)
                {
                    case 0:
                        if ($white === $rgbArr){
                            $lattice[$i] .= 0;
                        }else{
                            $lattice[$i] .= 1;
                        }
                        break;
                    case 1:
                        if ($black === $rgbArr){
                            $lattice[$i] .= 1;
                        }else{
                            $lattice[$i] .= 0;
                        }
                        break;
                    case 2:
                        if ($black === $rgbArr){
                            $lattice[$i] .= 1;
                        }elseif($white === $rgbArr){
                            $lattice[$i] .= 0;
                        }else{
                            $lattice[$i] .= 3;
                        }
                }


            }
        }

        return [$lattice, $size];
    }
}


