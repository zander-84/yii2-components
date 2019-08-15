<?php


namespace zander84\helpers\helpers\captcha;
use Yii;


class Captcha
{

    public $expire=5;
    public $headerKey='Captchakey';
    public $prefix = 'captcha.';
    public $count = 5;




    public function getPrefix()
    {
        if (isset(Yii::$app->params['redis_key']['captcha_prefix'])){
            return Yii::$app->params['redis_key']['captcha_prefix'];
        }else{
            return $this->prefix;
        }
    }
    //__________________________________________________________________________________________________________________
    public  function img ()
    {
        ob_start();
        // Set the content-type
        header('Content-type: image/png');
        header("Expires: 0");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // Create the image
        $im = imagecreatetruecolor(125, 50);

        // Create some colors
        $white = imagecolorallocate($im, 255, 255, 255);
        $grey = imagecolorallocate($im, 128, 128, 128);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, 125, 50, $white);

        // The text to draw
        $text =  $this->getCodeNum();

        $font = __DIR__.'/plane_crash.ttf';

        // Add some shadow to the text
        imagettftext($im, 20, 0, 4, 38, $grey, $font, $text);

        // Add the text
        imagettftext($im, 20, 6, 10, 44, $black, $font, $text);

        // Using imagepng() results in clearer text compared with imagejpeg()
        imagepng($im);
        imagedestroy($im);

        $data = ob_get_clean();
        return 'data:image/png;base64,'.base64_encode($data);

        //exit;
    }

    public function generateCode()
    {
        $nums = [1,2,5,6,8,9];
        $code = '';
        for ($i=0;$i<$this->count;$i++){
            $code .= $nums[rand(0,5)];
        }
        return $code;
    }

    public  function getCodeNum()
    {
        $cache = Yii::$app->cache;
        $cache_key = md5(uniqid(rand(1,100000)));

        $prefix = $this->getPrefix();
        $header_key = $this->headerKey;
        if($cache->exists($prefix.$cache_key)){
            sleep(1);
            return $this->getCodeNum();
        }else{
            $numbers = $this->generateCode();
            $cache->set($prefix.$cache_key, $numbers, $this->expire*60);
        }

        Yii::$app->response->getHeaders()->set($header_key,$cache_key);
        return $numbers;
    }



    public  function check ($code)
    {
        $prefix = $this->getPrefix();
        $tmp_key = Yii::$app->request->getHeaders()->get($this->headerKey);
        if($tmp_key) {
            $cache_key = $prefix . $tmp_key;
            $cache = Yii::$app->cache;
            if ($cache->exists($cache_key)) {
                $cache_data = $cache->get($cache_key);
                $cache->delete($cache_key);
                return $cache_data == $code;
            }
        }

        return false;
    }

}
