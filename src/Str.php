<?php

/**
 * @author Doanln
 * @copyright 2017
 */

namespace Cube;

class Str{
    protected static $arr_unicode = array();
    protected $str = null;
    
    function __construct($str=''){
        $this->str = $str;
        $this->setUnicodeArr();
    }
    public function __toString()
    {
        return $this->str;
    }
    protected function setUnicodeArr(){
        if(self::$arr_unicode) return true;

        $files = new Files(PACKAGE.'data');
        if($arr = $files->json('language/str')){
            self::$arr_unicode = $arr;
            return true;
        }
        return false;
    }
    
    public function sub($start = 0, $length = "MAX")
    {
        $this->str = $this->subtext($this->str,$start,$length);
        return $this->str;
    }



    
    public function clearUnicode($string=null){
        $string = $string ? $string : $this->str;
        $this->setUnicodeArr();
        $str = self::$arr_unicode;
        
        $vi = $str['vi'];
        $en = $str['en'];
        return str_replace($vi, $en, $string);
    }

    public function vi2it($string=null){
        $this->setUnicodeArr();
        $string = $string ? $string : $this->str;
        $str = self::$arr_unicode;
        
        $vi = $str['vi'];
        $en = $str['cit'];
        return str_replace($vi, $en, $string);
    }

    public function it2vi($string=null){
        $this->setUnicodeArr();
        $string = $string ? $string : $this->str;
        $str = self::$arr_unicode;
        
        $vi = $str['vi'];
        $en = $str['cit'];
        return str_replace($en, $vi, $string);
    }
    public function vi2js($string=null){
        $this->setUnicodeArr();
        $string = $string ? $string : $this->str;
        $str = self::$arr_unicode;
        
        $vi = $str['vi'];
        $en = $str['jsc'];
        return str_replace($vi, $en, $string);
    }
    public function js2vi($string=null){
        $this->setUnicodeArr();
        $string = $string ? $string : $this->str;
        $str = self::$arr_unicode;
        
        $vi = $str['vi'];
        $en = $str['jsc'];
        
        return str_replace($en, $vi, $string);
    }
    public function fixDBfont($str=''){
        $this->setUnicodeArr();
        $string = $string ? $string : $this->str;
        $s = self::$arr_unicode;
        
        $db = $s['db'];
        $vi = $s['vi'];
        return str_replace($db,$vi,$str);
    }
    public function cleanit($text)
    {
    	return htmlentities(strip_tags(stripslashes($text)), ENT_COMPAT, "UTF-8");
    }
    public function clear($str){
        $f = '/[^A-z0-9\s_\-=<>\[\]\{\}\.\,\"\'\:\;\(\)\~\!\@\#\$\%\^\&\*\r\n\t\`\/]/';
        $r = '';
        $str = preg_replace($f, $r, $str);
        return $str;
    }
    public function str_clean($str){
        $f = '/[^A-z0-9\s_\-\.]/';
        $r = '';
        $str = preg_replace($f, $r, $str);
        return $str;
    }
    public function str_ucln($str){
        $f = '/[^A-z0-9_\-\.]/';
        $r = '';
        $str = preg_replace($f, $r, $str);
        return $str;
    }
    public function getNamespace($str = null){
        $str !is_null($str) ? $str : $this->str;

        $str = $this->clearUnicode($str);

        $f = '/([^A-z0-9_\-]|^\-|\-$)/';
        $r = '-';

        $str = preg_replace($f, $r, $str);
        $str = preg_replace('/\-+/', $r, $str);
        $str = trim($str, '-');
        return $str;
    }
    public function getNamespace2($str=null){
        $str !is_null($str) ? $str : $this->str;

        $str = $this->clearUnicode($str);

        $f = '/([^A-z0-9_\-]|^\-|\-$)/';
        $r = '_';

        $str = preg_replace($f, $r, $str);
        $str = preg_replace('/\_+/', $r, $str);
        $str = trim($str, '_');
        return $str;
    }
    public function getAccessName($str=null){
        $str !is_null($str) ? $str : $this->str;

        $str = $this->clearUnicode($str);

        $f = '/([^A-z0-9_\-]|^\-|\-$)/';
        $r = '_';

        $str = preg_replace($f, $r, $str);
        $str = preg_replace('/\_+/', $r, $str);
        $str = trim($str, '_');
        return $str;
    }
    public function sclr($str=null,$char=null){
        $st = $this->vi2js($str);
        $st = preg_replace('/[^A-z0-9_&\-\s\\\t]/','',$st);
        $st = js2vi($st);
        
        $st = ($char!=null)?rtrim($st,$char):rtrim($st);
        return $st;
        
    }
    public function stk($value=null){
        $value = $this->htmlvi($value);
        $vl = preg_replace('/(\-\s|\s\-\s|\s\-)/', '\s', $value);
        $p = $value
            .", ".$this->vi2en($vl);
        $p = str_replace(",,", ",", $p);
        $p = preg_replace('/\,\,/', '\,', $p);
        return $p;
    }
    public function wspl($str=null, $start = 0, $length = 1000000){
    	
        
        $pr = "";
        
    	$str = preg_replace('/[\r\n\t]/', '', $str);
        
    	$cnt = strip_tags(str_replace("><", "> <", str_replace('  ', ' ', $str)));
    	$cnt = $this->htmlvi($cnt);
        if(strlen($str)<=$length) return $cnt;
        $cnt = substr($cnt, $start, $length);
    	$cnt = explode(" ", $cnt);
    	$p = count($cnt) -1;
    	for($i = 0; $i < $p; $i++){
    		$pr .= $cnt[$i] . " ";
    	}
    	return $pr."...";
    }
    
    
    public function subword($str=null, $start = 0, $length = 50000,$moretext='',$keepline=false){
    	
        
        $pr = "";
        
    	
    	$cnt = strip_tags(str_replace("><", "> <", str_replace('  ', ' ', $str)));
    	$cnt = preg_replace('/\s{2,}/', ' ', $cnt);
        if(!$keepline)$cnt = preg_replace('/[\r\n\t]/', '',$cnt);
        elseif(is_string($keepline)){
            $cnt = preg_replace('/[\r\n\t]/', ' / ', $cnt);
        }
        $cnt = $this->htmlvi($cnt);
        $p = explode(' ',$cnt);
        $c = count($p);
        $k = $start+$length;
        $s = $start;
        if($length>=$c) return $cnt;
        if($k >= $c){
            $s=0;
            $k=$c-1;
        }
        for($i=$s;$i<$k;$i++){
            $pr.=(($i>$s)?' ':'').$p[$i];
        }
        
        
        
        return $pr.$moretext;
    }
    
    public function clrt($str){
        $f = array('\\');
        $r = array('');
        $str = str_replace($f, $r, $str);
        return $str;
    }
    
    public function htmlvi($str){
        $f = array('\\','"',"'",'<','>');
        $r = Array('','&quot;','&#039;','&lt;','&gt;');
        $str = str_replace($f, $r, $str);
        return $str;
    }
    public function htmlvi2($str){
        return htmlentities($str);
    }
    public function hsencode($str){
        $f = array('\\','"',"'",'<','>');
        $r = Array('','&quot;','&#039;','&lt;','&gt;');
        $str = str_replace($f, $r, $str);
        return $str;
    }
    public function hsdecode($str){
        $f = array('','"',"'",'<','>');
        $r = Array('\\','&quot;','&#039;','&lt;','&gt;');
        $str = str_replace($r, $f, $str);
        return $str;
    }
    
    /**
     * Ham kiem tra ngay thang co hop le hay khong
     * @param day : Ngay
     * @param month : Thang
     * @param year : Nam
     * tra ve true neu dung du kien ngay thang va false neu sai
     */
    public function is_date($day, $month, $year){
        $stt = true;
        switch($day){
            case 31:
                if($month==2||$month==4||$month==6||$month==9||$month==11) $stt = false;
            break;
            case 30:
                if($month == 2) $stt = false;
            break;
            case 29:
                if(($year % 4 != 0 || ($year%4==0&&$year%100==0))||$year % 4 == 0 && $year%100!=0){
                    $stt = false;
                }
            break;
            
        }
    	return $stt;
    }
    
    
    public function is_gender($gender){
    	$stt = ($gender == "male" || $gender == "female")? true:false;
    	return $stt;
    }
    
    
    public function is_email($email){
    	$tm = '/^[_A-z0-9\-]+(\.[_A-z0-9\-]+)*@[A-z0-9\-]+(\.[A-z0-9\-]+)*(\.[A-z]{2,4})$/';
    	$t = false;
    	if(preg_match($tm, $email)) $t = true;
    	return $t;
    }
    
    public function subtext($str=null, $start = 0, $length = "MAX"){
    	$cnt = $this->htmlvi(strip_tags(str_replace("><", "> <", preg_replace('/[\r\n\t]/', ' ', $str))));
        if($length == 'MAX') $length = strlen($cnt); 
        if(strlen($cnt)<=$length) return $cnt;
        $pr = "";
        
        $cnt = substr($cnt, $start, $length);
    	$cnt = explode(" ", $cnt);
    	$p = count($cnt) -1;
    	for($i = 0; $i < $p; $i++){
    		$pr .= $cnt[$i] . " ";
    	}
        $pr = rtrim($pr);
    	return $pr;
    }
    
    public function html2text($html){
        $str = str_replace('\\','',$html);
        $str = strip_tags($str);
        return $str;
    }
    
    
    public function text2html($str){
        return preg_replace('/\n/','<br />', trim($str));
    }
    
    public function getLink($text){
        if(preg_match('/^([Hh]{1}[tT]{2}[Pp]{1}[Ss]{0,1}|ftp)\:\/\//', $text)) $str = "<a href=\"$text\">$text</a>";
        else $str = $text;
        return $str;
    }
     
    /**
     * @param String
     * @param String
     * @param Int
     * @param String
     */
    public function eval_str($text=null,$data=null,$char_type=0,$char_start='$'){
        $type = array(
            0 => array('start' => '{', 'end' => '}'),
            1 => array('start' => '[', 'end' => ']'),
            2 => array('start' => '(', 'end' => ')'),
            3 => array('start' => '/*', 'end' => '*/'),
            5 => array('start' => '<', 'end' => '>')
        );
        $chars = array('$','@','%','','*','cube:');
        if(!is_string($text) && !is_array($data)) return $text;
        $start = '{';
        $end = '}';
        $char = '$';
        if(isset($type[$char_type])){
            $ty = $type[$char_type];
            $start = $ty['start'];
            $end = $ty['end'];
        }elseif(is_string($char_type)){
            $start = $char_type;
            if(strlen($char_type)>1){
                $end = '';
                $n = strlen($char_type)-1;
                for($i=$n; $i >= 0; $i--){
                    $end.=substr($char_type,$i,1);
                }
            }else{
                $end = $start;
            }
        }
        if(in_array($char_start,$chars)){
            $char = $char_start;
        }elseif($char_start&&isset($chars[$char_start])){
            $char = $chars[$char_start];
        }elseif($char_start){
            $char = $char_start;
        }
        $find = array();
        $replace = array();
        $find2 = array();
        $replace2 = array();
        
        foreach($data as $name => $val){
            if(is_array($val)) continue;
            $find[] = $start.$char.$name.$end;
            $replace[] = $val;
            
            
            $find2[] = '['.$name.']';
            $replace2[] = $val;
            
            
            $find2[] = '{$'.$name.'}';
            $replace2[] = $val;
            $find2[] = '{\$'.$name.'}';
            $replace2[] = $val;
            
            $find2[] = '{'.$name.'}';
            $replace2[] = $val;
        }
        
        
        $txt = str_replace($find,$replace,$text);
        
        //$txt = str_replace($find2,$replace2,$txt);
        
        return $txt;
    }
    

    public static function short($string='',$length=null)
    {
        $Str = new static($string);
        return $Str->sub(0,$length?$length:48);
    }
        
}

?>