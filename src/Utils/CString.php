<?php
namespace Lattice\Utils;

class CString {
    /**
     * 以类似ABABAB模式的方式合并两个字符串结果。
     * @param string $str1 String A
     * @param string $str2 String B
     * @return string Merged string
     */
    public static function MergeBetween(string $str1, string $str2): string
    {
        // 拆分两个字符串
        $str1 = str_split($str1, 1);
        $str2 = str_split($str2, 1);
        // 如果字符串1大于字符串2，则交换变量
        if (count($str1) >= count($str2))
        list($str1, $str2) = array($str2, $str1);
        // 将较短的字符串附加到较长的字符串
        for($x=0; $x < count($str1); $x++)
        $str2[$x] .= $str1[$x];
        return implode('', $str2);
    }
}
