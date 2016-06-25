<?php
// 验证码类
class Captcha
{
    private $chararr = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';
    private $code;
    private $codelen = 4;
    private $width = 120;
    private $height = 40;
    private $img;
    private $font;
    private $fontsize = 20;

    public function __construct()
    {
        $this->font = __DIR__ . '/font.ttf';
    }

    // 生成随机码
    private function createCode()
    {
        $_len = strlen($this->chararr) - 1;
        for ($i = 0; $i < $this->codelen; $i++) {
            $this->code .= $this->chararr[mt_rand(0, $_len)];
        }
    }

    // 生成背景
    private function createBg()
    {
        $this->img = imagecreate($this->width, $this->height);
        imagecolorallocatealpha($this->img, 255, 255, 255, 100);
    }

    // 生成验证码
    private function createFont()
    {
        $_x = $this->width / $this->codelen;
        for ($i = 0; $i < $this->codelen; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imagettftext($this->img, $this->fontsize, mt_rand(-50, 50), $_x * $i + mt_rand(5, 10), $this->height / 1.5, $color, $this->font, $this->code[$i]);
        }
    }

    // 生成干扰元素
    private function createLine()
    {
        for ($i = 0; $i < 50; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
            imagestring($this->img, mt_rand(1, 5), mt_rand(0, $this->width), mt_rand(0, $this->height), 'JZ', $color);
        }
    }

    // 输出
    private function outPut()
    {
        header('Content-type: image/png');
        imagepng($this->img);
        imagedestroy($this->img);
    }

    // 对外生成
    public function getImg()
    {
        $this->createBg();
        $this->createCode();
        $this->createLine();
        $this->createFont();
        $this->outPut();
    }

    // 获取验证码
    public function getCode()
    {
        return strtolower($this->code);
    }
}
