<?php

/**
 * @author Le Ngoc Doan
 * @copyright 2016
 */
namespace Cube;
class Image{
    protected $source;
    protected $Data;
    protected $Original;
    protected $type = null;
    protected $mime = null;
    protected $width = 0;
    protected $height = 0;
    protected static $font_path;
    protected static $font_folder;
    protected static $font = 'arial.ttf';
    protected $_is_image = false;
    
    public function __construct($image=null){
        $this->new_image($image);
    }
    
    public function new_image($image=null){
        if(self::is_image_file($image) || get_resource_type($image)=='gd'){
            if(self::is_image_file($image)){
                $i = self::getsity($image);
                $im  = image::create($image);
                $this->Data = $im;
                $this->Original = $im;
                $this->height = $i['h'];
                $this->width = $i['w'];
                $this->type = $i['type'];
                $this->mime = $i['mime'];
                $this->_is_image = true;
            }else{
                $this->Data = $image;
                $this->Original = $image;
                $this->height = imagesy($this->Data);
                $this->width = imagesx($this->Data);
                $this->_is_image = true;
                
            }
        }else{
            $this->height = 768;
            $this->width = 1366;
            $im = self::create(null,$this->height,$this->width,array(255,255,255));
            $this->Data = $im;
            $this->Original = $im;
            $this->type = 'png';
            $this->mime = 'image/png';
        }
    }
    
    public function get(){
        return $this->Data;
    }
    public function getOriginal(){
        return $this->Original;
    }
    public function get_type(){
        return $this->type;
    }
    public function get_mime(){
        return $this->mime;
    }
    public function get_height(){
        return $this->height;
    }
    public function get_width(){
        return $this->width;
    }
    public function check(){
        return $this->_is_image;
    }
    
    public function copy(){
        $b = new static($this->Data);
        return $b;
    }
    /**
     * tao hinh anh tren trinh duyet
     * @param string $mime kieu file anh
     */ 
    public function output($mime=null){
        $m = $mime?$mime:($this->mime?$this->mime:'image/png');
        $d = $this->Data;
        header('Content-Type: '.$m);
        self::display($d,null,$m);
    }
    /**
     * tao hinh anh tren trinh duyet
     * @param string $mime kieu file anh
     */ 
    public function show($mime=null){
        $this->output($mime);
    }
    /**
     * tao file tu du lieu co san
     * @param string $filename ten file hoac duong dan noi bo
     * @param string $mime kieu file anh
     * @return Boolean
     */ 
    public function save($filename,$mime=null){
        if(!is_string($filename)) exit('filename you gived is not a string');
        $m = $mime?$mime:($this->mime?$this->mime:'image/png');
        $ext = $this->get_ext($m);
        if(!preg_match('/\.'.$ext.'$/si',$filename)){
            $filename.='.'.$ext;
        }
        if(self::display($this->Data,$filename,$m)) return true;
        return false;
    }
    
    /**
     * cat hinh anh
     * @param Int
     * @param Int
     * @param Int
     * @param Int
     */ 
    
    public function crop($width=null,$height=null,$x=null,$y=null,$transparent_bg = true){
        $w = $width;
        $h = $height;
        if(is_array($width)){
            $i = $width;
            if(isset($i['width'])) $w = $i['width'];
            else $w = null;
            if(isset($i['height'])) $h = $i['height'];
            if(isset($i['x'])) $x = $i['x'];
            if(isset($i['y'])) $y = $i['y'];
        }
        $img = $this->Data;
        $x = self::crop_x($img,$x,$w);
        $y = self::crop_y($img,$y,$h);
        
        $nWidth = $w;
        $nHeight = $h;
        $newImg = imagecreatetruecolor($nWidth, $nHeight);
        if($transparent_bg){
            imagealphablending($newImg, false);
            imagesavealpha($newImg,true);
            $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
            imagefilledrectangle($newImg, 0, 0, $nWidth, $nHeight, $transparent);
        
        }
        $targ_w = $w;
        $targ_h = $h;
    	$jpeg_quality = 90;
    
    	
        $ix = imagesx($img);
        $iy = imagesy($img);
        
        
        imagecopyresampled($newImg, $img, 0, 0, $x ,$y, $nWidth, $nHeight, $w, $h);
    	//imagecopyresampled($dst_r,$img_r,0,0,$x,$y,$targ_w,$targ_h,$w,$h);
        $this->Data = $newImg;
        $this->refresh();
        return $this;
    }
    
    public function resize($width=null,$height=null){
        $this->Data = self::iresize($this->Data,$width,$height);
        $this->refresh();
        return $this;
    }
    
    /**
     * thu phong hinh anh
     * @param string $type chieu thu phong height | width | d
     * @param Int $size do lon hinh anh
     * @param string $p don vi thu phong % | px
     * @return Object image
     */  
    public function zoom($type='width',$size=100,$p='px'){
        $img = $this->Data;
        $width = imagesx($img);
        $height = imagesy($img);
        $tt = strtolower($type);
        $n = $size;
        if($p=="%"){
            $zk = $n/100;
            $new_height = $height*$zk;
            $new_width = $width*$zk;
        }
        else{
            $k = $width/$height;
            if($tt == "h"||$tt=='height'){
                $new_height = $n;
                $new_width = $new_height*$k;
            }
            elseif($tt=='d' || $tt == 'diagonal'){
                $d1 = sqrt(($height*$height)+($width*$width));
                $d2 = $n;
                $k2 = $d2/$d1;
                $new_height = $k2*$height;
                $new_width = $k2*$width;
            }
            else{
                $new_width = $n;
                $new_height = $new_width/$k;
            }
        }
        $newImg = imagecreatetruecolor($new_width, $new_height);
        imagealphablending($newImg, false);
        imagesavealpha($newImg,true);
        $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        imagefilledrectangle($newImg, 0, 0, $new_width, $new_height, $transparent);
        
        imagecopyresampled($newImg, $img, 0, 0, 0 , 0, $new_width, $new_height, $width, $height);
        $this->Data = $newImg;
        $this->refresh();
        return $this;
    	
    }
    
    public function rotate($angle=0){
        if(is_numeric($angle)){
            $img = $this->Data;
            
            $img = imagerotate($img, $angle, -1); 
            imagealphablending($img, true); 
            imagesavealpha($img, true); 
            $this->Data = $img;
            $this->refresh();
        }
        return $this;
    }
    
    /**
     * them mot hinh anh khac vao hinh anh dang xu ly
     * @param resource $image doi tuong can chen
     * @param Int $x vi tri ngang
     * @param Int $y vi tri doc
     * @param Int $margin can le
     */ 
    public function addImage($image,$x='center',$y='middle',$margin=0,$item_width=null,$item_height=null){
        $_x = strtolower($x);
        $_y = strtolower($y);
        
        $dst_im = self::images($this->Data);
        $bg_w = imagesx($dst_im);
        $bg_h = imagesy($dst_im);
        
        $source_im = self::images($image);
        $ins_w = imagesx($source_im);
        $ins_h = imagesy($source_im);
        
        
        if(is_numeric($x)){
            $ix = $x;
        }
        elseif(is_string($_x)){
            if($_x=="left")
                $ix = $margin;
            elseif($_x=="right")
                $ix = ($bg_w-$ins_w)-$margin;
            else
                $ix = ($bg_w-$ins_w)/2;
        }
        else{
            $ix = $margin;
        }
        if(is_numeric($y)){
            $iy = $y;
        }
        elseif(is_string($_y)){
            if($_y=="top"){
                $iy =$margin;
            }
            elseif($_y=="bottom"){
                $iy = $bg_h - $ins_h - $margin;
            }
            else{
                $iy = ($bg_h-$ins_h)/2;
            }
        }
        else{
            $iy = $margin;
        }
        $itw = is_numeric($item_width)?$item_width:$ins_w;
        $ith = is_numeric($item_height)?$item_height:$ins_h;
        imagecopy($dst_im, $source_im, $ix, $iy, 0, 0, $itw, $ith);
        $this->Data = $dst_im;
        return $this;
    }
    public function addImageCustom($image,$x='center',$y='middle',$margin=0,$item_width=null,$item_height=null){
        $_x = strtolower($x);
        $_y = strtolower($y);
        
        $dst_im = self::images($this->Data);
        $bg_w = imagesx($dst_im);
        $bg_h = imagesy($dst_im);
        
        $source_im = self::images($image);
        $ins_w = imagesx($source_im);
        $ins_h = imagesy($source_im);
        
        
        if(is_numeric($x)){
            $ix = $x;
        }
        elseif(is_string($_x)){
            if($_x=="left")
                $ix = $margin;
            elseif($_x=="right")
                $ix = ($bg_w-$ins_w)-$margin;
            else
                $ix = ($bg_w-$ins_w)/2;
        }
        else{
            $ix = $margin;
        }
        if(is_numeric($y)){
            $iy = $y;
        }
        elseif(is_string($_y)){
            if($_y=="top"){
                $iy =$margin;
            }
            elseif($_y=="bottom"){
                $iy = $bg_h - $ins_h - $margin;
            }
            else{
                $iy = ($bg_h-$ins_h)/2;
            }
        }
        else{
            $iy = $margin;
        }
        $itw = is_numeric($item_width)?$item_width:$ins_w;
        $ith = is_numeric($item_height)?$item_height:$ins_h;
        imagecopyresampled($dst_im, $source_im, $ix, $iy, 0, 0,$bg_w,$bg_h,$ins_w,$ins_h);
        $this->Data = $dst_im;
        return $this;
    }
    
    /**
     * them mot doan van ban hinh anh dang xu ly
     * @param string $text
     * @param int $size
     * @param int or string $x
     * @param int or string $y
     * @param int $angle do nghieng cu chu
     * @param string $font duong dan font
     * @param int $margin can le
     * @param int or array $color mau chu
     * @param int or array $stroke_color mau vien chu
     * @param int $stroke_width
     */
    
    public function addText($text,$size=14,$x='center',$y='midle',$angle=0,$font=null,$max_width=null,$margin=0,$color='#000',$stroke_color='#FFF',$stroke_width=0){
        $this->Data = self::insertText($this->Data,$text,$size,$x,$y,$angle,$font,$max_width,$margin,$color,$stroke_color,$stroke_width);
        return $this;
    }
    /**
     * lam mo anh
     * @param int $d
     */ 
    public function blur($d=10){
        $this->Data = self::blurring($this->Data,$d);
        return $this;
    }
    
    /**
     * chinh kich thuoc
     * @param int 
     * @param int
     */ 
    public function RaC($width=null,$height=null){
        $image = $this->Data;
        $w = imagesx($image);
        $h = imagesy($image);
        $g = $w/$h;
        
        if(is_string($width) && strtolower($width)=='auto'&&is_string($height) && strtolower($height)=='auto'){
            return $this;
        }
        
        if(is_string($width) && strtolower($width)=='auto'){
            $width = $height*$g;
        }
        elseif(!$width || !is_numeric($width)){
            $width = $this->width;
        }
        
        if(is_string($height) && strtolower($height)=='auto'){
            $height = $width/$g;
        }
        elseif(!$height || !is_numeric($height)){
            $height = $this->height;
        }
        
        $k = $width/$height;
        if($g<$k){
            $s = $width;
            $z = "width";
        }
        else{
            $s = $height;
            $z = "height";
        }
        
        $this->zoom($z,$s);
        $this->crop($width,$height);
        $this->refresh();
        return $this;
    }
    
    public function restore(){
        $this->Data = $this->Original;
        $this->refresh();
    }
    
    public function refresh(){
        $this->width = imagesx($this->Data);
        $this->height = imagesy($this->Data);
    }
    
    
    
    
    public function get_text_height($text='',$size=14,$font='arial',$max_width=null,$angle=0,$margin = null, $x = null){
        $font = self::get_font_url($font);
        $mg = is_numeric($margin)?$margin:0;
        if(!is_numeric($max_width)||$max_width<0){
            if(is_numeric($x)){
                $max_width = $this->get_width() - $x - $mg;
            }
            else{
                $max_width = $this->get_width();
            }
            
        }
        return getiTextHeight($text,$size,$max_width,$font,$angle);
    }
    public function count_line($text='',$size=14,$font='arial',$max_width=null,$angle=0,$margin = null, $x = null){
        $font = self::get_font_url($font);
        $mg = is_numeric($margin)?$margin:0;
        if(!is_numeric($max_width)||$max_width<0){
            if(is_numeric($x)){
                $max_width = $this->get_width() - $x - $mg;
            }
            else{
                $max_width = $this->get_width();
            }
            
        }
        return getiTextLine($text,$size,$max_width,$font,$angle);
    }
    
    public function get_ext($mime=null){
        $type = $mime?$mime:($this->mime?$this->mime:'image/png');
        $stt = 'png';
        if(($type == "png" || $type == "image/png")){
            $stt = 'png';
        }elseif(($type == "jpg" || $type == "jpeg" || $type == "image/jpeg")){
            $stt = 'jpg';
        }elseif(($type == "gif" || $type == "image/gif")){
            $stt = 'gif';
        }
        return $stt;
    }
    
    
    // static
    
    //font
    
    /**
     * @param String $font
     */ 
    
    public static function set_font_path($font='arial.ttf'){
        if($font){
            self::$font_path = $font;
        }
    }
    
    public static function set_font($font='arial.ttf'){
        if($font){
            self::$font = $font;
        }
    }
    
    
    public static function set_font_folder($dir){
        if(is_string($dir)){
            $d = rtrim($dir,'/');
            self::$font_folder = $d;
        }
    }
    
    
    
    public static function get_font_url($font='arial.ttf'){
        $s = null;
        if(is_string($font)){
            $f1 = $font;
            $f2 = $font.'.ttf';
            $fd = explode('://',$f1);
            if(count($f1)>1){
                $s = $f1;
            }else{
                if(file_exists($f1)){
                    $s = $f1;
                }elseif(file_exists($f2)){
                    $s = $f2;
                }else{
                    $p1 = rtrim(self::$font_folder).'/'.ltrim($f1);
                    $p2 = $p1.'.ttf';
                    if(file_exists($p1)){
                        $s = $p1;
                    }elseif(file_exists($p2)){
                        $s = $p2;
                    }
                }
            }
        }
        elseif(self::$font_path && file_exists(self::$font_path)){
            $s = self::$font_path;
        }elseif(self::$font_folder && self::$font){
            $p1 = rtrim(self::$font_folder).'/'.ltrim(self::$font);
            $p2 = $p1.'.ttf';
            if(file_exists($p1)){
                $s = $p1;
            }elseif(file_exists($p2)){
                $s = $p2;
            }
        }
        return $s;
    }
    
    /**
     * @param string
     */ 
    
    
    public static function is_image_file($url){
        if(!is_string($url))
            return false;
        $stt = (preg_match('/(^http|\.jpg|\.gif|\.png|tmp|\.jpeg)/si', $url) || is_file($url))?true:false;
        return $stt;
    }
    
    
    
    
    
    public static function getsity($image_url){
        $pex = '';
        $mime = '';
        if(self::is_image_file($image_url)){
            $source = getimagesize($image_url);
            $mime = $source['mime'];
            $w = $source[0];
            $h = $source[1];
            $typ = explode('/',$mime);
            if($typ[0]=='image'&&isset($typ[1])){
                $t = $typ[1];
                switch($t){
                    case 'png':
                        $pex = $t;
                    break;
                    case 'jpeg':
                        $pex = 'jpg';
                    break;
                    case 'gif':
                        $pex = $t;
                    break;
                    default:
                        $pex = $t;
                    break;
                    
                }
            }
        }elseif($image_url){
            $w = imagesx($image_url);
            $h = imagesy($image_url);
        }
        else{
            $w = 0;
            $h = 0;
        }
        $img_inf = array
        (
            'type' => $pex,
            'mime' => $mime,
            'w' => $w,
            'h' => $h
        );
        return ($w)?$img_inf:null;
    }
    
    /**
     * @param resource or string
     * @param string
     * @param string
     */ 
    public static function images($image=null){
        if(self::is_image_file($image)){
            $source_im = self::create($image);
            
        }elseif(get_resource_type($image)=='gd'){
            $source_im = $image;
            
        }
        else{
            $source_im = image::create(null,480,360,array(255,255,255));
        }
        return $source_im;
    }
    public static function display($image_src, $img_filename = null, $img_type = null){
        $imi = $image_src;
        if(is_array($imi)){
            $type = ($imi['type'])?$imi['type']:$img_type;
            $image = $imi['image'];
            $filename = ($imi['filename'])?$imi['filename']:(($img_filename)?$img_filename:null);
        }
        elseif(self::is_image_file($imi)){
            $image = self::create($imi);
            $ii = self::getsity($imi);
            $filename = ($img_filename)?$img_filename:null;
            $type = ($img_type)?$img_type:$ii['type'];
        }
        else{
            $image = $imi;
            $filename = ($img_filename)?$img_filename:null;
            $type = $img_type;
        }
        //$quality = ($imi['quality'])?$imi['quality']:100;
        if(($type == "png" || $type == "image/png") && imagepng($image, $filename)){
            $stt = true;
        }elseif(($type == "jpg" || $type == "jpeg" || $type == "image/jpeg") && imagejpeg($image, $filename)){
            $stt = true;
        }elseif(($type == "gif" || $type == "image/gif") && imagegif($image, $filename)){
            $stt = true;
        }
        elseif(isset($ii) && copy($imi,$img_filename)){
            $stt=true;
        }
        else{
            $stt = false;
        }
        return $stt;
    }
    
    /**
     * @param resource or string
     * @param int
     * @param int
     * @param array or string
     */ 
    
    public static function create($image_url = null, $image_w = 100, $image_h = 100, $color = null){
        if(is_string($image_url) && self::is_image_file($image_url)){
            $img = self::getsity($image_url);
            $type = $img['type'];
            if($type == "png"){
                $image = imagecreatefrompng($image_url);
            }elseif($type == "jpg"){
                $image = imagecreatefromjpeg($image_url);
            }elseif($type == "gif"){
                $image = imagecreatefromgif($image_url);
            }
            else{
                $image = file_get_contents($image_url);
            }
        }
        else{
            if(is_array($color) && isset($color[0]) && isset($color[1]) && isset($color[2])){
                $image = imagecreatetruecolor($image_w,$image_h);
                imagecolorallocate($image, $color[0], $color[1], $color[2]);
            }
            elseif(is_string($color)){
                $color = explode(',',$color);
                if(is_array($color) && isset($color[0]) && isset($color[1]) && isset($color[2])){
                    $image = imagecreatetruecolor($image_w,$image_h);
                    imagecolorallocate($image, $color[0], $color[1], $color[2]);
                }else{
                    $image = imagecreatetruecolor($image_w,$image_h);
                }
                
            }else
                $image = imagecreatetruecolor($image_w,$image_h);
            //$txtColor = imagecolorallocate($image, 245, 250, 254);
            
        }
        return $image;
    }
    
    
    
    public static function iresize($image, $w=100, $h=100){
        if($image){
            $bg = self::create(null, $w, $h);
            if(self::is_image_file($image)){
                $img = self::create($image);
            }
            else{
                $img = $image;
            }
            imagecopy($bg, $img, 0, 0, $w, $h);
            $afterresize = $bg;
        }
        else{
            $afterresize = $image;
        }
        return $afterresize;
    }
    /**
     * phong to thu nho hinh anh
     * @param resource $image : GD or URL
     * @param string $t height | eidth | d
     * @param number $n
     * @param string $p : px | %
     * @return resource
     */
    public static function izoom($image, $t = "w", $n = 100, $p = "px"){
        $img = new image($image);
        $img->zoom($t,$n,$p);
        return $img->get();
    }
    public static function icrop($image, $w = "w", $h = "h", $x = "center", $y = "center"){
        $ipic = new image($image);
        $ipic->crop($w,$h,$x,$y);
        $img = $ipic->get();
        return $img;
    }
    
    public static function destroy(&$image){
        imagedestroy($image);
    }
    public static function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);
        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }
        $rgb = array($r, $g, $b);
        //return implode(",", $rgb); // returns the rgb values separated by commas
        return $rgb; // returns an array with the rgb values
    }

    public static function getTextWidth($text,$font='fonts/arial.ttf',$size=10,$angle=0){
        $dims = imagettfbbox($size, $angle, $font, $text);
        $width = $dims[4] - $dims[6]; 
        return ($dims[4] - $dims[6]);
    }
    public static function getTextHeight($text,$font='fonts/arial.ttf',$size=10,$angle=0){
        $dims = imagettfbbox($size, $angle, $font, $text); 
        $height = $dims[3] - $dims[5];
        return $height;
    }
    public static function getTrueColorByHex($image,$hex='#000'){
        $img = self::images($image);
        if(preg_match('/^#/',$hex)){
            $tc = self::hex2rgb($hex);
            $trueColor = imagecolorallocate($img,$tc[0],$tc[1],$tc[2]);
        }
        else
            $trueColor = imagecolorallocate($img,0,0,0);
        return $trueColor;
    }
    
    
    public static function insertImage($image, $item, $x = "center", $y = "center", $margin = 0,$item_width=null,$item_height=null){
        $x = strtolower($x);
        $y = strtolower($y);
        
        $dst_im = self::images($image);
        $bg_w = imagesx($dst_im);
        $bg_h = imagesy($dst_im);
        
        $source_im = self::images($item);
        $ins_w = imagesx($source_im);
        $ins_h = imagesy($source_im);
        
        
        if(is_numeric($x)){
            $ix = $x;
        }
        elseif(is_string($x)){
            if($x=="left")
                $ix = $margin;
            elseif($x=="right")
                $ix = ($bg_w-$ins_w)-$margin;
            else
                $ix = ($bg_w-$ins_w)/2;
        }
        else{
            $ix = $margin;
        }
        if(is_numeric($y)){
            $iy = $y;
        }
        elseif(is_string($y)){
            if($y=="top"){
                $iy =$margin;
            }
            elseif($y=="bottom"){
                $iy = $bg_h - $ins_h - $margin;
            }
            else{
                $iy = ($bg_h-$ins_h)/2;
            }
        }
        else{
            $iy = $margin;
        }
        $itw = is_numeric($item_width)?$item_width:$ins_w;
        $ith = is_numeric($item_height)?$item_height:$ins_h;
        imagecopy($dst_im, $source_im, $ix, $iy, 0, 0, $itw, $ith);
        return $dst_im;
    }
    
    
    /**
     * @param Source  $image hinh anh dua vao
     * @param String  $text van ban can chen
     * @param Integer $size kich thuc chua
     * @param Integer $x vi tri (ngang) Chap nhan left/right/center / (c|m|center|mid|middle)=number
     * @param Integer $y vi tri (doc) Chap nhan top/center/bottom / (c|m|center|mid|middle)=number
     * @param Integer $angle do xoay chu
     * @param String  $font dung dan font
     * @param Integer $max_width Kich thuoc toi da cua van ban (do rong)
     * @param Integer $margin can le van ban
     * @param HexCode $text_color hex hoac array(r,g,b) hoac 'r,g,b'
     * @param HexCode $stroke_color hex hoac array(r,g,b) hoac 'r,g,b'
     * @param Integer $stroke_width kich thuoc duong vien
     * @return GD object tra ve image source
     */ 
    
    public static function insertText($image,$text,$size=10,$x='center',$y='center',$angle=0,$font='arial.ttf',$max_width=null,$margin=0,$text_color='#000',$stroke_color='#FFF',$stroke_width=0){
        /* xu ly hinh anh dua vao */
        if(self::is_image_file($image)) $img = self::create($image);
        elseif(strtolower(get_resource_type($image))=='gd') $img = $image;
        else $img = self::create(null,850,400,array(25,162,100));
        
        /** xu ly thong so chuoi dua vao */
        
        $txt = $text;
        if(is_array($text)){
            $i = $text;
            $txt = isset($i['text'])?$i['text']:'';
            $size = isset($i['size'])?$i['size']:$size;
            $margin = isset($i['margin'])?$i['margin']:$margin;
            $max_width = isset($i['max_width'])?$i['max_width']:$max_width;
            $font = isset($i['font'])?$i['font']:$font;
            $x = isset($i['x'])?$i['x']:$x;
            $y = isset($i['y'])?$i['y']:$y;
            $text_color = isset($i['color'])?$i['color']:$text_color;
            $stroke_color = isset($i['stroke_color'])?$i['stroke_color']:$stroke_color;
            $stroke_width = isset($i['stroke_width'])?$i['stroke_width']:$stroke_width;
            $angle = isset($i['angle'])?$i['angle']:$angle;
        }
        $text = $txt;
        $font = self::get_font_url($font);
        
        $image_width = imagesx($img);
        $image_height = imagesy($img);
        // xu ly mau sac
        
        if(is_array($text_color)&&isset($text_color[0])&&isset($text_color[1])&&isset($text_color[2])){
            $tc = $text_color;
            $text_color = imagecolorallocate($img,$tc[0],$tc[1],$tc[2]);
        }elseif(preg_match('/^#/',$text_color)){
            $tc = self::hex2rgb($text_color);
            $text_color = imagecolorallocate($img,$tc[0],$tc[1],$tc[2]);
        }elseif(preg_match('/[0-9],[0-9],[0-9]/',$text_color)){
            $tc = explode(',',$text_color);
            $text_color = imagecolorallocate($img,$tc[0],$tc[1],$tc[2]);
        }
        else{
            $text_color = imagecolorallocate($img,0,0,0);
        }
        if(is_array($stroke_color)&&isset($stroke_color[0])&&isset($stroke_color[1])&&isset($stroke_color[2])){
            $bc = $stroke_color;
            $boder_color = imagecolorallocate($img,$bc[0],$bc[1],$bc[2]);
        }elseif(preg_match('/^#/',$stroke_color)){
            $bc = self::hex2rgb($stroke_color);
            $boder_color = imagecolorallocate($img,$bc[0],$bc[1],$bc[2]);
        }elseif(preg_match('/[0-9],[0-9],[0-9]/',$stroke_color)){
            $bc = explode(',',$stroke_color);
            $boder_color = imagecolorallocate($img,$bc[0],$bc[1],$bc[2]);
        }else{
            $boder_color = imagecolorallocate($img,0,0,0);
        }
        
        # kinh thuc text
        if(is_numeric($max_width)){
            $max = $max_width;
        }elseif(is_numeric($margin)&&$margin>0){
            $max = $image_width - 2*$margin;
        }
        else{
            $max = $image_width;
        }
        $texts_height = getiTextHeight($text,$size,$max,$font,$angle);
        $texts = textQoute($text,$max,$font,$size,0);
        $t = count($texts);
        $th = getiTextMaxHeight($text,$size,$max,$font,$angle);
        
        $image = image::images($img);
        if(countTextLine($text,$size,$max,$font,$angle)>1){
            $tr=$th/1.1;
        }else{
            $tr=$th;
        }
        #vi tri text (y)
        if(is_numeric($y)) $sy = $y + $tr;
        else{
            $ly = strtolower($y);
            $_gy = explode('=',$ly);
            if(count($_gy)==2){
                if($_gy[0]=='m'||$_gy[0]=='c'||$_gy[0]=='mid'||$_gy[0]=='middle'){
                    $sy = getText2Number($_gy[1]) - $texts_height/2 + $tr;
                }else{
                    $sy = ($image_height - $texts_height)/2 + $tr*0.8;
                }
            }elseif($ly=='top')
                $sy = $margin + $tr;
            elseif($ly=='bottom')
                $sy = $image_height - $margin - $texts_height + $tr;
            else
                $sy = ($image_height - $texts_height)/2 + $tr*0.8;
        }
        for($i=0;$i<$t;$i++){
            $txt = $texts[$i];
            $text_width = getTextWidth($txt,$font,$size,$angle);
            # vi tri text (x)
            if(is_numeric($x)) $sx = $x;
            else{
                $lx = strtolower($x);
                $_gx = explode('=',$lx);
                if(count($_gx)==2){
                    if($_gx[0]=='c'||$_gx[0]=='center'||$_gx[0]=='mid'||$_gx[0]=='middle'||$_gx[0]=='m'){
                        $sx = getText2Number($_gx[1]) - $text_width/2;
                    }
                    else{
                        $sx = ($image_width-$text_width)/2;
                    }
                }elseif($lx=='left') $sx = $margin;
                elseif($lx=='right') $sx = $image_width - ($text_width+$margin);
                else $sx = ($image_width-$text_width)/2;
            }
            #insert text
            $image = self::insTaS($image,$size,$angle,$sx,$sy,$text_color,$boder_color,$font,$txt,$stroke_width);
            $sy+=$th;
        }
        return $image;
    }
    
    public static function getFirstImage($content){
        $content = (!$content)?"":$content;
        $first_img = '';
        $content = str_replace("\\", "", $content);
        $regex = "/<img .*?(?=src)src=\"([^\"]+)\"/si";
        preg_match_all($regex, $content, $matches);
        $first_img = $matches[1][0];
        if(empty($first_img)){ //Defines a default image
            $first_img = "";
        }
        return $first_img;
    }
    
    public static function getRAC($image,$width=320,$height=180){
        $img = new image($image);
        $img->RaC($width,$height);        
        return $img->get();
        
    }

    public static function insTaS($image, $size, $angle, $x, $y, $textcolor, $strokecolor, $fontfile, $text, $px) {
        for($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++)
            for($c2 = ($y-abs($px)); $c2 <= ($y+abs($px)); $c2++)
                $bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);
        imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
     
         return $image;
    }
    /**
     * @param image $image : image's source or Url Image if use url only use JPG, PNG or GIF
     * @param Integer
     */
    
    public static function blurring($image, $int=10){
        if(self::is_image_file($image)) $img = self::create($image);
        elseif($image && !is_array($image)) $img = $image;
        else $img = self::create(null,200,200,array(25,162,100));
        
        //$image = image::images($image);
        $image_width = imagesx($img);
        $image_height = imagesy($img);
        
        for ($i = 0; $i < $int; $i++) {
            imagefilter($img, IMG_FILTER_GAUSSIAN_BLUR);
        }
        return $image;
    }
    
    protected static function crop_x($img,$x=null,$width=null){
        $r = 0;
        $w = imagesx($img);
        $h = imagesy($img);
        if(!is_numeric($width)) $width = $w;
        if(is_numeric($x)){
            $r = $x;
        }
        elseif(is_string($x)){
            $s = strtolower($x);
            if($s=='left'||$s=='l'||$s=='trai' || $s == 't'){
                $e = 0;
            }elseif($s=='right'||$s=='r'||$s=='phai' || $s == 'p'){
                $r = $w-$width;
            }else{
                $r = ($w-$width)/2;
            }
        }else{
            $r = ($w-$width)/2;
        }
        return $r;
    }
    
    protected static function crop_y($img,$y=null,$height=null){
        $r = 0;
        $w = imagesx($img);
        $h = imagesy($img);
        if(!is_numeric($height)) $height = $h;
        if(is_numeric($y)){
            $r = $y;
        }
        elseif(is_string($y)){
            $s = strtolower($y);
            if($s=='top'||$s=='t'||$s=='tren'){
                $e = 0;
            }elseif($s=='bottom'||$s=='b'||$s=='duoi' || $s == 'd'){
                $r = $h-$height;
            }else{
                $r = ($h-$height)/2;
            }
        }else{
            $r = ($h-$height)/2;
        }
        return $r;
    }
    
    
}

function getTextWidth($text,$font='fonts/arial.ttf',$size=10,$angle=0){
    $dims = imagettfbbox($size, $angle, $font, $text);
    $width = $dims[4] - $dims[6]; 
    return $width;
}
function getTextHeight($text,$font='fonts/arial.ttf',$size=10,$angle=0){
    $dims = imagettfbbox($size, $angle, $font, $text); 
    $height = $dims[3] - $dims[5];
    return $height;
}

function textQoute($text,$maxWidth=100,$font="fonts/arial.ttf",$size=10,$angle=0){
    $maxTextWidth = $maxWidth;
    $texts = array('');
    if(is_array($text))
        $texts = $text;
    elseif(is_string($text))
        $texts = explode("\n", $text);
    elseif(is_numeric($text))
        $texts = array($text);
    $itext = array();
    $st = count($texts);
    $n = 0;
    for($i=0;$i<$st;$i++){
        $txt = $texts[$i];
        $xt = explode(" ", $txt);
        $curtext = "";
        $crt = "";
        $c = count($xt);
        for($j=0;$j<$c;$j++){
            $crt .= (($crt=="")?"":" ").$xt[$j];
            if(getTextWidth($crt,$font,$size)<=$maxTextWidth){
                $curtext = $crt;
            }else{
                $itext[$n] = $curtext;
                $crt = $xt[$j];
                $curtext = $xt[$j];
                $n++;
            }
        }
        if($curtext!=""){
            $itext[$n] = $curtext;
            $n++;
        }
    }
    return $itext;
}

function getQuoteHeight($text='',$font=null,$size=null,$max=500){
    $texts = textQoute($text,$max,$font,$size,0);
    $t = count($texts);
    $th = getTextHeight((isset($texts[0])?$texts[0]:""),$font,$size);
    for($i=1;$i<count($texts);$i++){
        $tm = getTextHeight($texts[$i],$font,$size);
        if($tm>$th) $th=$tm;
    }
    $tr = $th;
    $th*=1.1;
    $texts_height = $th*$t;
    return $texts_height;
}

function getiTextHeight($text='',$size=null,$max=500,$font=null,$angle=0){
    $texts = textQoute($text,$max,$font,$size,$angle);
    $t = count($texts);
    $th = 0;
    $last = 0;
    for($i=0;$i<$t;$i++){
        $tm = getTextHeight($texts[$i],$font,$size,$angle);
        if($tm>$th) $th=$tm;
        if($i==$t-1) $last = $tm;
    }
    $tr = $th;
    $th*=1.1;
    $texts_height = $th*$t - ($last*1.1 - $last);
    
    return $texts_height;
}

function getiTextMaxHeight($text='',$size=null,$max=500,$font=null,$angle=0){
    $texts = textQoute($text,$max,$font,$size,$angle);
    $t = count($texts);
    $th = 0;
    $last = 0;
    for($i=0;$i<$t;$i++){
        $tm = getTextHeight($texts[$i],$font,$size,$angle);
        if($tm>$th) $th=$tm;
        if($i==$t-1) $last = $tm;
    }
    $th*=1.1;
    return $th;
}

function getiTextLine($text='',$size=null,$max=500,$font=null,$angle=0){
    $texts = textQoute($text,$max,$font,$size,$angle);
    $t = count($texts);
    return $t;
}
function countTextLine($text='',$size=null,$max=500,$font=null,$angle=0){
    $texts = textQoute($text,$max,$font,$size,$angle);
    $t = count($texts);
    return $t;
}

function getElx($array,$key='A',$char='|'){
    if(!is_array($array)) return null;
    if(!is_string($key) && !is_numeric($key)) return $array;
    $p = explode($char,$key);
    $t = count($p);
    for($i = 0; $i < $t; $i++){
        $c = rtrim($p[$i]);
        if(is_array($array) && isset($array[$c])){
            $array = $array[$c];
        }else{
            $array = null;
            $i+=$t;
        }
    }
    
    return $array;
    
}

function getText2Number($str){
    $lbtg = array(
                 0,1,2,3,4,5,6,7,8,9, 
                 'A'=> 1,'B'=> 2,'C'=> 3,'D'=> 4,'E'=> 5,'F'=> 6,'G'=> 7,'H'=> 8,'I'=> 9,'J'=>10,
                 'K'=>11,'L'=>12,'M'=>13,'N'=>14,'O'=>15,'P'=>16,'Q'=>17,'R'=>18,'S'=>19,'T'=>20,
                 'U'=>21,'V'=>22,'W'=>23,'X'=>24,'Y'=>25,'Z'=>26,'.'=>'.',
                 
                          
    );
    $txt = strtoupper($str);
    $s = '';
    $l = strlen($txt);
    
    for($i=0;$i<$l;$i++){
        $c = substr($txt,$i,1);
        $gt = getElx($lbtg,$c);
        
        if(is_numeric($gt)||is_string($gt)){
            $s .= $gt;
        }
        
    }
    return ((int) $s);
}


?>