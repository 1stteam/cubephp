<?php

/**
 * @author Le Ngoc Doan
 * @copyright 2015
 */


namespace Cube;




/**
 * convert object to array
 * @param String
 */ 
function _ota($d) {
    if (is_object($d)) {
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        return array_map(__FUNCTION__, $d);
    }
    else {
        return $d;
    }
}

function convert_obj_to_arr($d){
    return _ota($d);
}
define("ARR_DEF_VAL","ABVAFSFGSF 87979679685685686u856785t785r868967897686tf jg tyui yityfityfti8 ----------- Doan dep trai");
define('A_ISS','1524545454545445');
class Arr{
    protected $arr;
    protected $data;
    protected $delimiter = '.';
    /**
     * khoi tao mang
     * @param array $array mang dua vao
     */
    public function __construct($array=null,$arg2=null,$arg3=null,$arg4=null){
        switch(strtolower(gettype($array))){
            case 'array':
                $this->set($array,$arg2);
            break;
            case 'object':
                $d = _ota($array);
                $this->set($d);
                if(is_string($arg2)) $this->set_delimiter($arg2);
            break;
            case 'string':
                if(!is_string($arg2)) $arg2 = ' ';
                $a = explode($arg2,$array);
                $this->set($a);
            break;
            case 'integer':
                if(is_int($arg2)){
                    $p = arc_num($arr,$arg2);
                    $this->set($p);
                }
            break;
        }
    }
    
    public function set($array=array(),$delimiter=null){
        $s = false;
        if(is_array($array)){
            $this->arr = $array;
            $this->data = arr_keylower($array);
            $s = true;
            $this->set_delimiter($delimiter);
        }
        return $s;
    }
    /**
     * reset
     */ 
    public function reset(){
        $this->data = array();
        $this->arr = array();
        $this->delimiter = '.';
        return $this;
    }
    /**
     * kiem tra ton tai cua bien
     * @param String $key
     */ 
    
    
    public function is_set($key=null,$delimiter=null){
        if(is_null($key)) return $this->arr?true:false;
        if(is_string($key)){
            $delimiter = $delimiter?$delimiter:$this->delimiter;
            $s = true;
            $t = $this->data;
            $d = explode($delimiter,strtolower($key));
            $p = count($d);
            if(isset($t[$d[0]])){
                $t = $t[$d[0]];
                for($i = 1; $i < $p; $i++){
                    $g = $d[$i];
                    if(is_array($t)&&isset($t[$g])){
                        $t = $t[$g];
                    }else{
                        $t = false;
                        $s = $t;
                        $i+=$p;
                    }
                }
            }
            else $s = false;
            return $t;
        }
        elseif(is_numeric($key)) return isset($this->arr[$key])?true:false;
        return $this->arr?true:false;
    }
    
    /**
     * kiem tra xem gia tri co nam trong mang hay ko
     * @param varieble $value
     * @param String $key;
     */ 
    
    public function in($value=null,$key=ARR_DEF_VAL){
        $s = false;
        if($key!=ARR_DEF_VAL){
            $a = $this->get($key);
            if(is_array($a) && in_array($value)) $s = true;
            return $a; 
        }
        return is_array($value,$this->arr);
    }
    public function getKey($val=null,$parentKey=null){
        $a = $this->get($parentKey);
        if($a){
            if(in_array($val,$a)){
                foreach($a as $index => $value){
                    if($val == $value){
                        return $index;
                    }
                }
            }
        }
        return null;
    }
    public function set_delimiter($delimiter=null){
        if(is_string($delimiter) && $delimiter!=''){
            $this->delimiter = $delimiter;
        }
        return $this;
    }
    
    
    /**
     * lay phan tu trong mang hoac ca mang
     * @param string $key
     */ 
    public function get($key=null,$delimiter=null){
        if(is_null($key)) return $this->arr;
        if(is_string($key)){
            $delimiter = is_string($delimiter)?$delimiter:$this->delimiter;
            $t = $this->data;
            $d = explode($delimiter,strtolower($key));
            $p = count($d);
            if(isset($t[$d[0]])){
                $t = $t[$d[0]];
                for($i = 1; $i < $p; $i++){
                    $g = $d[$i];
                    if(is_array($t)&&isset($t[$g])){
                        $t = $t[$g];
                    }else{
                        $t = null;
                        $i+=$p;
                    }
                }
            }
            else $t = null;
            return $t;
        }
        elseif(is_numeric($key)) return isset($this->arr[$key])?$this->arr[$key]:null;
        return $this->arr?true:false;
    }
    
    /**
     * @param string
     * @param mixed
     */ 
    public function add($key=null,$val=ARR_DEF_VAL){
        return $this->push($key,$val);
    }
    public function put($key=null,$val=ARR_DEF_VAL){
        return $this->push($key,$val);
    }
    public function push($key=null,$val=ARR_DEF_VAL){
        if($val==ARR_DEF_VAL && !is_array($key)){
            $this->arr[] = $key;
            $this->data[] = $key;
            return $this;
        }
        switch(strtolower(gettype($key))){
            case 'string':
                $delimiter = $this->delimiter;
                $ks = explode($delimiter,$key);
                $ky = explode($delimiter,strtolower($key));
                if($ks[0]!='')$kz = "\$this->arr[\"$ks[0]\"]";
                else $kz = "\$this->arr[]";
                if($ky[0]!='')$kx = "\$this->data[\"$ky[0]\"]";
                else $kx = "\$this->data[]";
                
                for($i = 1; $i < count($ks); $i++){
                    $kw = $ks[$i];
                    if($kw!='')$kz .= "[\"".$kw."\"]";
                    else $kz .= "[]";
                    $kk = $ky[$i];
                    if($kk!='')$kx .= "[\"".$kk."\"]";
                    else $kx .= "[]";
                }
                
                eval("$kz = \$val;$kx = \$val;");
            break;
            
            case 'array':
                $____key____ = $key;
                $____e____ = "";
                foreach($____key____ as $_k_ => $_v_){
                    if(is_numeric($_k_)){
                        $this->arr[] = $_v_;
                        $this->data[] = $_v_;
                    }
                    else
                        $____e____ .= "\$this->arr['$_k_'] = \$____key____['$_k_'];\$this->data[strtolower('$_k_')] = \$____key____['$_k_'];";
                }
                if($____e____!="") eval($____e____);
            break;
            
            case 'integer':
                $this->arr[$key] = $val;
                $this->data[$key] = $val;
            break;
            
            case 'null':
                $this->arr[] = $val;
                $this->data[] = $val;
            break;
            
        }
        return $this;
    }
    
    protected function get_var_path($key=null,$delimiter=null){
        if(is_null($key)) return "\$this->var";
        $kx = null;
        $delimiter = $delimiter?$delimiter:$this->delimiter;
        $ky = explode($delimiter,strtolower($key));
        if($ky[0]!='')$kx = "\$this->data[\"$ky[0]\"]";
        else $kx = "\$this->data";
        if(count($ky)<=1) return $kx;
        $f = 0;
        for($i = 1; $i < count($ky); $i++){
            $kk = $ky[$i];
            if($kk!='')$kx .= "[\"".$kk."\"]";
            elseif($f){
                return null;
            }
            else{
                $f = 1;
            }
        }
        return $kx;
    }
    
    protected function get_var_path_raw($key=null,$delimiter=null){
        if(is_null($key)) return "\$this->var";
        $kx = null;
        $delimiter = $delimiter?$delimiter:$this->delimiter;
        $ky = explode($delimiter,$key);
        if($ky[0]!='')$kx = "\$this->var[\"$ky[0]\"]";
        else $kx = "\$this->var";
        if(count($ky)<=1) return $kx;
        $f = 0;
        for($i = 1; $i < count($ky); $i++){
            $kk = $ky[$i];
            if($kk!='')$kx .= "[\"".$kk."\"]";
            elseif($f){
                return null;
            }
            else{
                $f = 1;
            }
        }
        return $kx;
    }
    /**
     * them gia tri vao mang neu key cua gia tri can them ko ton tai trong mang chinh
     * @param Array $args mang chuyen vao
     */ 
    public function parse($args=null){
        if(!is_array($args)) return false;
        $s = 0;
        foreach($args as $k => $v){
            if(is_numeric($k)){
                $this->push($v);
                $s++;
            }
            else{
                $this->push($k,$v);
                $s++;
            
            }
        }
        return $this;
    }
    public function delete($key=null){
        if(is_string($key)){
            $delimiter = $this->delimiter;
            $ks = explode($delimiter,$key);
            $kz = "\$this->arr[\"$ks[0]\"]";
            for($i = 1; $i < count($ks); $i++){
                $kz .= "[\"".$ks[$i]."\"]";
            }
            $kk = explode($delimiter,strtolower($key));
            $kx = "\$this->data[\"$kk[0]\"]";
            for($i = 1; $i < count($kk); $i++){
                $xz .= "[\"".$kk[$i]."\"]";
            }
            eval("unset($kz);unset($kx);");
        }
        elseif(is_numeric($key)){
            unset($this->arr[$key]);
            unset($this->data[$key]);
        }
        return $this;
    }
    
    public function un_set($key=null){
        if(is_string($key)){
            $delimiter = $this->delimiter;
            $ks = explode($delimiter,$key);
            $ky = explode($delimiter,strtolower($key));
            if($ks[0]!='')$kz = "\$this->arr[\"$ks[0]\"]";
            else $kz = "\$this->arr[]";
            for($i = 1; $i < count($ks); $i++){
                $kw = $ks[$i];
                if($kw!='')$kz .= "[\"".$kw."\"]";
                else $kz .= "[]";
            }
            if($ky[0]!='')$kx = "\$this->data[\"$ky[0]\"]";
            else $kx = "\$this->arr[]";
            for($i = 1; $i < count($ky); $i++){
                $kk = $ky[$i];
                if($kk!='')$kx .= "[\"".$kk."\"]";
                else $kz .= "[]";
            }
            eval("unset($kz);unset($kx);");
        }
        elseif(is_numeric($key)){
            unset($this->arr[$key]);
            unset($this->data[$key]);
        }elseif(is_array($key)){
            foreach($key as $__key__){
                if(is_string($__key__)){
                    $delimiter = $this->delimiter;
                    $ks = explode($delimiter,$__key__);
                    $ky = explode($delimiter,strtolower($__key__));
                    if($ks[0]!='')$kz = "\$this->arr[\"$ks[0]\"]";
                    else $kz = "\$this->arr[]";
                    for($i = 1; $i < count($ks); $i++){
                        $kw = $ks[$i];
                        if($kw!='')$kz .= "[\"".$kw."\"]";
                        else $kz .= "[]";
                    }
                    if($ky[0]!='')$kx = "\$this->data[\"$ky[0]\"]";
                    else $kx = "\$this->arr[]";
                    for($i = 1; $i < count($ky); $i++){
                        $kk = $ky[$i];
                        if($kk!='')$kx .= "[\"".$kk."\"]";
                        else $kz .= "[]";
                    }
                    eval("unset($kz);unset($kx);");
                }
                elseif(is_numeric($__key__)){
                    unset($this->arr[$__key__]);
                    unset($this->data[$__key__]);
                }
            }
        }
        return $this;
    }
    
    public function copy($key=null){
        $val = $this->get($key);
        $n = new Arr($val);
        return $n;
    }
    /**
     * @param string
     * @param mixed
     */ 
    
    public function e($key=null,$val=null){
        if(!is_null($val)){
            if($val==A_ISS) return $this->is_set($key);
            return $this->push($key,$val);
        }
        elseif(is_array($key)){
            return $this->push($key);
        }
        return $this->get($key);
    }
    public function _($key=null){
        $a = $this->get($key);
        echo is_array($a)?json_encode($a):$a;
    }
    public function p($key=null){
        $p = $this->get($key);
        $t = is_array($p)?json_decode($p):$p;
        print($t);
    }
    public function pr($key=null){
        $p = $this->get($key);
        print_r($p);
    }
    
    
    public function count($key=null,$delimiter=null){
        $delimiter = is_string($delimiter)?$delimiter:$this->delimiter;
        $p = $this->get($key,$delimiter);
        return count($p);
    }
    /**
     * @param Integer
     * so phan tu can lay
     */ 
    public function rand($num = 1,$key=null){
        if(!is_numeric($num)||$num<1) $num = 1;
        $ag = $this->get($key);
        if(!is_array($ag)) return null;
        $keys = array_rand($ag,$num);
        if($num==1) return $ag[$keys];
        $t = count($t);
        $arr = array();
        for($i=0;$i<$t;$i++){
            $arr[] = $ag[$keys[$i]];
        }
        return $arr;
    }
    
    public function key_rand($num = 1){
        if(!is_numeric($num)||$num<1) $num = 1;
        $ag = $this->get($key);
        if(!is_array($ag)) return null;
        $keys = array_rand($ag,$num);
        return $keys;
    }
    
    
}

$____arr____ = new Arr(array());

function arr_set($array=null,$d='.'){
    global $____arr____;
    $____arr____->set($array,$d);
}
function arr_reset(){
    global $____arr____;
    $____arr____->reset();
}
function arr_get($a=null,$b=null){
    global $____arr____;
    return $____arr____->get($a,$b);
}
function arr_push($a=null,$b=null){
    global $____arr____;
    $____arr____->push($a,b);
    
}
function arr_delete($a=null,$b=null){
    global $____arr____;
    $____arr____->delete($a);
}
function arr_rand($a=null){
    global $____arr____;
    return $____arr____->rand($a);
}
function arr_print($a=null){
    global $____arr____;
    $____arr____->p($a);
}

function _arr($key=null,$value=null){
    switch(strtolower(gettype($key))){
        case 'string':
            if(is_null($value))
                return arr_get($key);
            arr_push($key,$value);
        break;
        case 'integer':
            if(!is_null($value))
                return arr_get($key);
            arr_push($key,$value);
        break;
        case 'array':
            arr_push($key);
        break;
        case 'null':
            if(is_bool($value))
            {
                arr_reset();
                return;
            }
            return arr_get();
        break;
        case 'bool':
            if($key==true)
            {
                arr_reset();
                return;
            }
        break;
        
    }
}
/**
 * @param Integer
 * @param Integer
 * Tao mang so voi key = value
 */ 
function arc_num($from=1,$to=0){
    $a = array();
    if($from>$to)
        for($i=$from;$i>=$to;$i--)
            $a[$i] = $i;
    else
        for($i=$from;$i<=$to;$i++)
            $a[$i] = $i;
    return $a;
}
/**
 * thet lap mang, neu mang push co phan tu nao do ma mang chinh chua co hoac gia tri null hoac rong thi se set phan tu do cho mang chinh
 * @param array $main mang chinh
 * @param Array $push mang tieu chuan
 * @return array mang das dc chinh sua cac phan tu
 */ 
function arr_parse($main=null,$push=null,$keepIndex=false){
    $main = (array) $main;
    if(is_array($push)){
        foreach($push as $k => $v){
            if(is_numeric($k)) {
                if($keepIndex){
                    $main[$k] = $v;
                }
                else{
                    $main[] = $v;
                }
            }
            elseif(!isset($main[$k])||!$main[$k]) $main[$k] = $v;
        }
    }
    return $main;
}
/**
 * thet lap mang, lay tat ca cac phan tu trong mang
 * @param array $main mang chinh
 * @param Array $push mang tieu chuan
 * @return array mang das dc chinh sua cac phan tu
 */ 
function arr_pre_push($main=null,$push=null){
    $main = (array) $main;
    if(is_array($push)){
        foreach($push as $k => $v){
            $main[$k] = $v;
        }
    }
    return $main;
}
define('CALTSO_DEF','%^&&^&^&^&^&^&^&^&');
define('CALTSO_ADI','%^&&^&^&^&^&^&^&^&');
define('CALTSO_VAL','%^&&^&^&^&^&^&^&^& hfd 7uy75 rftrfd');

function arrToSelectOpts($arr=null,$kov=null,$kot=null){
    if(is_object($arr)){
        $arr = convert_obj_to_arr($arr);
    }
    if(is_array($arr)){
        $n=0;
        $b = array();
        $v_last = '125242';
        $t_last = '1254524';
        foreach($arr as $k => $vals){
            if($kov==CALTSO_DEF || $kov == $v_last) {
                $kov = $k;
                $v_last = $k;
            }
            if($kot==CALTSO_DEF || $kot == $t_last) {
                $kot = $k;
                $t_last = $k;
            }
            $el = new Arr($vals);
            $v = $vals;
            if($n==0){
                if(is_array($v)){
                    $i = 0;
                    foreach($v as $kk => $vv){
                        if($i==0){
                            if((is_null($kov) || !isset($v[$kov]))&&$kov!=$k){
                                $kov = $kk;
                            }
                        }
                        elseif($i==1){
                            if((is_null($kot) || !isset($v[$kot]))&&$kot!=$k){
                                $kot = $kk;
                            }
                            break;
                        }
                        $i++;
                    }
                }else{
                    $kov = $k;
                    $kot = CALTSO_VAL;
                }
            }
            $n++;
            if(isset($vals[$kov])) $kkk = $vals[$kov];
            else $kkk = $k;
            
            if(isset($vals[$kot])) $vvv = $vals[$kot];
            elseif($kot == CALTSO_VAL){
                $vvv = $v;
            }
            else $vvv = $k;
            
            $b[$kkk] = $vvv;
        }
        return $b;
    }
    else{
        return array($arr=>$arr);
    }
}

/**
 * @param Array
 */ 


function arr_inv($arr=null,$keep_key=0){
    if(is_array($arr)){
        $a = $b = array();
        $t = count($arr) - 1;
        foreach($arr as $k => $v){
            $a[] = array('key'=>$k,'val'=>$v);
        }
        if($keep_key){
            for($i = $t; $i >= 0; $i--){
                $f = $a[$i];
                $b[$f['key']] = $f['val'];
            }
        }else{
            for($i = $t; $i >= 0; $i--){
                $f = $a[$i];
                $b[] = $f['val'];
            }
        }
        return $b;
    }
    return $arr;
}

define('ANI_INS_DEF','<!--Default-->');

function arr_num_insert($arr, $item = ANI_INS_DEF, $key=null){
    if(is_array($arr) && $item!=ANI_INS_DEF){
        if(is_null($key)) $arr[] = $item;
        elseif(is_string($key)) $arr[$key] = $item;
        elseif(is_int($key)){
            if(!isset($arr[$key])) $arr[$key] = $item;
            else{
                $t = count($arr);
                $tam = null;
                for($i = $key; $i <= $t; $i++){
                    if(isset($arr[$i])){
                        $tam = $arr[$i];
                        $arr[$i] = $item;
                        $item = $tam;
                    }else{
                        $arr[$i] = $item;
                        $i+=$t;
                    }
                }
            }
        }
    }
    return $arr;
}

function str2arr($str=null){
    $a = array();
    if(is_array($str)){
        $a = $str;
    }elseif(is_string($str)){
        if(parse_str($str,$d)){
            $a = $d;
        }
    }
    return $a;
}

function arr_escape($obj=null){
    return str2arr($obj);
}


function arr_unserialize($str){
    $a = array();
    if(is_array($str)){
        $a = $str;
    }elseif(is_string($str)){
        try{
            if($d = unserialize($str)){
                $a = $d;
            }
        }
        catch(exception $e){
            
        }
        
    }
    return $a;
}

function arr_serialize($arr){
    if(is_object($arr)){
        $arr = _ota($arr);
    }
    if(is_array($arr)){
        return serialize($arr);
    }
    return serialize(array($arr));
}





function line2arr($data,$delimiter=null,$linedown='<!--linedown-->',$use_key=true){
    if(!is_string($delimiter)) return null;
$d = explode($linedown,str_replace(array('\r\n','\n','\r','
'),array($linedown,$linedown,$linedown,$linedown),$data));
    if($d){
        $da = array();
        foreach($d as $ln){
            if($b = trim($ln)){
                if(!is_string($delimiter)) $da[] = $b;
                elseif($c = explode($delimiter,$b)){
                    if(count($c)>1){
                        if($use_key)
                            $da[trim($c[0])] = trim($c[1]);
                        else
                            $da[] = trim($c[1]);;
                    }
                }
            }
        }
        return $da;
    }
    return null;
}














/**
 * @param Array
 */ 

function arr_keylower($array=null){if(!is_array($array)) $data = $array;else{$data = array();foreach($array as $key => $val){if(is_string($key)) $key = strtolower($key);if(!is_array($val))$data[$key] = $val;else{$a = array();foreach($val as $key1 => $val1){if(is_string($key1)) $key1 = strtolower($key1);if(!is_array($val1)) $a[$key1] = $val1;else{$b = array();foreach($val1 as $key2 => $val2){if(is_string($key2)) $key2 = strtolower($key2);if(!is_array($val2)) $b[$key2] = $val2;else{$c = array();foreach($val2 as $key3 => $val3){if(is_string($key3)) $key3 = strtolower($key3);if(!is_array($val3)) $c[$key3] = $val3;else{$d = array();foreach($val3 as $key4 => $val4){if(is_string($key4)) $key4 = strtolower($key4);if(!is_array($val4)) $d[$key4] = $val4;else{$e = array();foreach($val4 as $key5 => $val5){if(is_string($key5)) $key5 = strtolower($key5);$e[$key5] = arr_keyslower($val5);}$d[$key4] = $e;}}$c[$key3] = $d;}}$b[$key2] = $c;}}$a[$key1] = $b;}}$data[$key] = $a;}}}return $data;}function arr_keyslower($array=null){if(!is_array($array)) $data = $array;else{$data = array();foreach($array as $key => $val){if(is_string($key)) $key = strtolower($key);if(!is_array($val))$data[$key] = $val;else{$a = array();foreach($val as $key1 => $val1){if(is_string($key1)) $key1 = strtolower($key1);if(!is_array($val1)) $a[$key1] = $key1;else{$b = array();foreach($val1 as $key2 => $val2){if(is_string($key2)) $key2 = strtolower($key2);if(!is_array($val2)) $b[$key2] = $val2;else{$c = array();foreach($val2 as $key3 => $val3){if(is_string($key3)) $key3 = strtolower($key3);if(!is_array($val3)) $c[$key3] = $val3;else{$d = array();foreach($val3 as $key4 => $val4){if(is_string($key4)) $key4 = strtolower($key4);if(!is_array($val4)) $d[$key4] = $val4;else{$e = array();foreach($val4 as $key5 => $val5){if(is_string($key5)) $key5 = strtolower($key5);$e[$key5] = arr_keylower($val5);}$d[$key4] = $e;}}$c[$key3] = $d;}}$b[$key2] = $c;}}$a[$key1] = $b;}}$data[$key] = $a;}}}return $data;}

?>