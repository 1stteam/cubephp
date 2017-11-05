<?php

/**
 * @author Le Ngoc Doan
 * @copyright 2016
 */


namespace Cube;


class Files{
    protected $extensions = array(
        'php', 'html', 'txt', 'htm', 'js', 'css', 'tpl', 'json', 'sd', 'lang', 'sl', 'xml', 'asp', 'java', 'pas', 'password', 'sl', 'time', 'cube', 'num'
    );
    protected $dir = '';
    protected $ext = null;
    protected $chmod = 0777;
    public $errors = array();
    /**
     * @param array $args : mang tham so
     */ 
    public function __construct($args=null){
        $this->init($args);
    }
    public function init($args=null){
        $this->setDir();
        $a = new Arr($args);
        $this->addExtensions($a->e('extensions'));
        $this->setDir($a->e('dir'));
        $this->setDefaultExtension($a->e('default_extension'));
        $this->setChmodCode($a->e('chmod'));
        return $this;
    }
    
    
    public function get_list($dir=null,$ext=null,$sort = false){
        if(!$dir) $dir = $this->dir;
        $list = array();
        $abc = array();
        $result = array();
        $e = is_string($ext)?strtolower($ext):null;
        if($e){
            $e = explode(',',$e);
            $b = array();
            for($i = 0; $i < count($e); $i++){
                $ei = trim($e[$i]);
                if($ei){
                    $b[] = $ei;
                }
            }
            $e = $b;
        }
        if (is_string($dir) && is_dir($dir)) {
            try{
                if ($dh = opendir($dir)) {
                    while (($file = readdir($dh)) !== false) {
                        $t = 1;
                        if($e){
                            $fs = explode('.',$file);
                            $ex = strtolower($fs[count($fs)-1]);
                            if(in_array($ex,$e)){
                                $t=1;
                            }else{
                                $t = 0;
                            }
                            if($t && $file!='..' && $file!='.'){
                                $path = $this->join_url_dir($dir,$file);
                                $sd = strtolower($file);
                                $abc[] = $sd;
                                $list[$sd] = array(
                                    'type' => 'file',
                                    'filename' => $file,
                                    'path' => $path,
                                    'extension' => $ex
                                );
                            }
                        }else{
                            if($file!='..' && $file!='.'){
                                $path = $this->join_url_dir($dir,$file);
                                $fs = explode('.',$file);
                                $ex = strtolower($fs[count($fs)-1]);
                                $type = is_dir($path)?'folder':'file';
                                $sd = strtolower($file);
                                $abc[] = $sd;
                                $list[$sd] = array(
                                    'type' => $type,
                                    'filename' => $file,
                                    'extension' => $ex,
                                    'path' => $path
                                    
                                );
                            }
                            
                        }
                        
                    }
                    closedir($dh);
                }
            }catch(exception $e){
                $this->errors[__METHOD__] = $e->getMessage();
            }
        }
        if($list && $abc){
            if($sort){
                sort($abc);
            }
            $t = count($abc);
            $type_list = array(
                'folder' => array(),
                'file' => array()
            );
            
            for($i = 0; $i < $t; $i++){
                $item = $list[$abc[$i]];
                $type_list[$item['type']][] = $item;
            }
            foreach($type_list as $list_type){
                foreach($list_type as $it){
                    $result[] = $it;
                }
            }
        }
        return $result;
    }
    public function list_path($path=null){
        if(count(explode(PACKAGE,$path))<2 && $this->dir){
            $path = $this->join_url_dir($this->dir,$path);
        }
        $p = $path;
        if(!$p || $p=='/') return null;
        $f = false;
        if(substr($p,0,1)=='/') $f = true;
        $a = array();
        $b = array();
        $p = trim($p,'/');
        $d = '';
        if($f) $d = '/';
        $e = explode('/',$p);
        $t = count($e);
        for($i=0;$i<$t;$i++){
            $s = ($i>0)?'/':'';
            $d.=$s.$e[$i];
            $a[$i] = $d;
        }
        return $a;
    }
    
    public function make_dir($dir=null){
        if(is_string($dir) && $list = $this->list_path($dir)){
            foreach($list as $p){
                if(!is_dir($p)){
                    mkdir($p,0777);
                }
            }
            return true;
        }return false;
    }

    
    public function save_contents($contents, $filename, $ext=null){
        if(count(explode(PACKAGE,$filename))<2 && $this->dir){
            $filename = $this->join_url_dir($this->dir,$filename);
        }

        $fp = str_replace(PACKAGE, '', $filename);
        $parts = explode('/', ltrim($fp,'/'));
        $file = array_pop($parts);
        $fs = explode('.',$file);
        $ex = array_pop($fs);
        if($ext || $this->ext){
            if(!$ext) $ext = $this->ext;
            $ext=trim($ext,'. '); 
            if(strtolower($ex)!=strtolower($ext)){
                $file.='.'.$ext;
            }
        }
        $dir = PACKAGE;
        try{
            foreach($parts as $part){
                if(!is_dir($dir = cube_join_url($dir,$part))){
                    mkdir($dir);
                }
            }
            if(file_put_contents("$dir/$file", $contents)){
                return true;
            }
            
        }catch(exception $e){
            return false;
        }

        return false;
    }
    
    public function get_contents($filename, $ext=null){
        if(count(explode(PACKAGE,$filename))<2 && $this->dir){
            $filename = $this->join_url_dir($this->dir,$filename);
        }
        $fs = explode('.',$filename);
        $ex = array_pop($fs);
        if($ext || $this->ext){
            if(!$ext) $ext = $this->ext;
            $ext=trim($ext,'. '); 
            if(strtolower($ex)!=strtolower($ext)){
                $filename.='.'.$ext;
            }
        }
        try{
            if(is_file($filename)){
                return file_get_contents($filename);
            }
            return null;
            
        }catch(exception $e){
            $this->errors[__METHOD__] = $e->getMessage();
            return null;
        }
        return null;
    }
    
    
    
    public function upload_files($tmp_name='',$new_file=null){
        if($new_file && $tmp_name){
            if(count(explode(PACKAGE,$new_file))<2 && $this->dir){
                $new_file = $this->join_url_dir($this->dir,$new_file);
            }
            $s = '';
            if(substr($new_file,0,1)=='/') $s = '/';
            
            $g = explode('/',trim($new_file,'/'));
            $file = array_pop($g);
            if($this->make_dir($s.implode('/',$g))){
                try{
                    if(move_uploaded_file($tmp_name,$new_file)) return true;
                }
                catch(exception $e){
                    $this->errors[__METHOD__] = $e->getMessage();
                }
                
            }
        }
        return false;
    }
    public function delete($dirname=null){
        if(is_string($dirname)){
            if(is_file($dirname)) return unlink($dirname);
            elseif(is_dir($dirname)){
                try{
                    if($list = $this->get_list($dirname)){
                        foreach($list as $item){
                            $d = $item['path'];
                            if(is_dir($d)) $this->delete($d);
                            else unlink($d);
                        }
                    }
                    return rmdir($dirname);
                }
                catch(exception $e){
                    $this->errors[__METHOD__] = $e->getMessage();
                }
                
            }else{
                $dirname = $this->join_url_dir($this->dir,$dirname);
                if(is_file($dirname)) return unlink($dirname);
                elseif(is_dir($dirname)){
                    try{
                        if($list = $this->get_list($dirname)){
                            foreach($list as $item){
                                $d = $item['path'];
                                if(is_dir($d)) $this->delete($d);
                                else unlink($d);
                            }
                        }
                        return rmdir($dirname);
                    }
                    catch(exception $e){
                        $this->errors[__METHOD__] = $e->getMessage();
                    }
                    
                }else{
                    $this->errors[__METHOD__] = get_text(517);
                }
            }
        }return false;
    }
    
    public function _chmod($path=null,$code=null){
        $stt = false;
        if(count(explode(PACKAGE,$path))<2 && $this->dir){
            $path = $this->join_url_dir($this->dir,$path);
        }
        if(is_nan($code)) $code = $this->chmod;
        if(is_dir($path) || is_file($path)){
            try{
                $stt = true;
            }catch(exception $e){
                $this->errors[__METHOD__] = $e->getMessage();
            }
            
        }
        return $stt;
    }

    public function join_url_dir($main=null,$join=null){
        $h = is_string($main)?rtrim($main,'/'):'';
        $j = (is_string($join) || is_numeric($join))?$join:'';
        if(is_string($j)) $j = ltrim($j,'/');
        $g = '/';
        $u = $h.$g.$j;
        return $u; 
    }

    /**
     * @param Array $extensions Mang duoi file
     */ 
    public function addExtensions($extensions=null){
        if(!is_null($extensions)){
            $arr = array();
            switch(gettype($extensions)){
                case 'array':
                    $arr = $extensions;
                break;
                case 'string':
                    $ar1 = array();
                    $ext_expl = explode(',',$extensions);
                    foreach($ext_expl as $p){
                        $exs = explode(' ',$p);
                        foreach($exs as $es){
                            $e = trim($es);
                            if($e){
                                $ar1[] = $e;
                            }
                        }
                    
                    }
                    foreach($ar1 as $item){
                        $et = trim($item);
                        if($et){
                            $arr[] = $et;
                        }
                    }
                break;
            }
            foreach($arr as $ext){
                $this->addExtension($ext);
            }
        }
        return $this;
    }
    /**
     * @param String
     */
    public function addExtension($ext=null){
        if(is_string($ext) && $ext && !in_array($ext,$this->extensions)){
            $this->extensions[] = $ext;
        }
    }
    public function setDefaultExtension($ext=null){
        if(is_string($ext) && in_array($ext,$this->extensions)){
            $this->ext = $ext;
        }
    }
    /**
     * @param String $dirname duong dan director
     */ 
    public function setDir($dirname=null){
        if(is_string($dirname) && is_dir($dirname)){
            $this->dir = $dirname;
        }
        return $this;
    }
    /**
     * @param Int $code Ma chmod
     */ 
    public function setChmodCode($code=null){
        if(is_numeric($code)) $this->chmod = $code;
        return $this;
    }
    public function getErrorMessage($code=null){
        if(is_string($code)){
            return isset($this->errors[$code])?$this->errors[$code]:null;
        }
        return null;
    }
    public function catch_error(){
        $a = $this->errors;
        return array_pop($a);
    }

    public static function get_mime()
    {
        return require(PACKAGE.'sys/FileMime.php');
    }

    public function json($file=null,$data=null){
        if(is_null($file)) return $data;
        $f = $this->join_url_dir($this->dir,$file);
        if(is_null($data)){
            $fb = null;
            if(file_exists($file) && !is_dir($file)){
                $fb = $file;
            }
            elseif(file_exists($file.'.json')){
                $fb = $file.'.json';
            }
            elseif(file_exists($f) && !is_dir($f)){
                $fb = $f;
            }
            elseif(file_exists($f.'.json')){
                $fb = $f.'.json';
            }
            $d = array();
            if($fb){
                $a = _do2a(json_decode(file_get_contents($fb)));
                $b = new Arr($a);
                $d = $b->get('data');//;
            }
            return $d;
        }
        $g = explode('.',$file);
        $ex = strtolower($g[count($g)-1]);
        if($ex!='json') $f.='.json';
        if(is_object($data)) $data = _do2a($data);
        $d = array('data'=>$data,'created'=>date("Y-m-d H:i:s"),'time'=>time());
        $d = json_encode($d);
        return $this->save_contents($d,$f);;
    }
    public function getJSON($file=null,$key=null,$convert=false){
        if(is_null($file)) return $data;
        $f = $this->join_url_dir($this->dir,$file);
        $fb = null;
        if(file_exists($file) && !is_dir($file)){
            $fb = $file;
        }
        elseif(file_exists($file.'.json')){
            $fb = $file.'.json';
        }
        elseif(file_exists($f) && !is_dir($f)){
            $fb = $f;
        }
        elseif(file_exists($f.'.json')){
            $fb = $f.'.json';
        }
        $d = null;
        if($fb){
            $a = json_decode(file_get_contents($fb));
            if($convert){
                $b = new Arr(_do2a($a));
                if(!is_null($key))
                $d = $b->get('data.'.$key);
            else 
                $d = $b->get('data');
            }else{
                if(isset($a->data)){
                    if(!is_null($key)){
                        if(is_array($a->data) && isset($a->data[$key])){
                            $d = $a->data[$key];
                        }elseif(is_object($a->data)){
                            eval("if(isset(\$a->data->$key)) \$d = \$a->data->$key;");
                        }
                    }else{
                        $d = $a->data;
                    }
                }
            }
            
        }
        return $d;
        
    }
    public function json_url($url=null){
        return _do2a(json_decode(file_get_contents($url)));
    }
    public function serliz($file=null,$data=null){
        if(is_null($file)) return $data;
        $f = $this->join_url_dir($this->dir,$file);
        if(is_null($data)){
            $fb = null;
            if(file_exists($file) && !is_dir($file)){
                $fb = $file;
            }
            elseif(file_exists($file.'.sl')){
                $fb = $file.'.sl';
            }
            elseif(file_exists($f) && !is_dir($f)){
                $fb = $f;
            }
            elseif(file_exists($f.'.sl')){
                $fb = $f.'.sl';
            }
            if($fb){
                $a = _do2a(unserialize(file_get_contents($fb)));
                $b = new Arr($a);
                return $b->get('data');//;
            }
        }
        $g = explode('.',$file);
        $ex = strtolower($g[count($g)-1]);
        if($ex!='sl') $f.='.sl';
        if(is_object($data)) $data = _do2a($data);
        $d = array('data'=>$data,'created'=>date("Y-m-d H:i:s"),'time'=>time());
        $d = serialize($d);
        return $this->save_contents($d,$f);
    }
    public function text($file=null,$data=null){
        if(is_null($file)) return $data;
        $f = $this->join_url_dir($this->dir,$file);
        if(is_null($data)){
            $fb = null;
            if(file_exists($file) && !is_dir($file)){
                $fb = $file;
            }
            elseif(file_exists($file.'.txt')){
                $fb = $file.'.txt';
            }
            elseif(file_exists($f) && !is_dir($f)){
                $fb = $f;
            }
            elseif(file_exists($f.'.txt')){
                $fb = $f.'.txt';
            }
            $d = array();
            if($fb){
                
                
                $d = $this->get_contents($fb);
            }
            return $d;
        }
        $g = explode('.',$file);
        $ex = strtolower($g[count($g)-1]);
        if($ex!='txt') $f.='.txt';
        return $this->save_contents($data,$f);;
    }
    public function html($file=null,$data=null){
        if(is_null($file)) return $data;
        $f = $this->join_url_dir($this->dir,$file);
        if(is_null($data)){
            $fb = null;
            if(file_exists($file) && !is_dir($file)){
                $fb = $file;
            }
            elseif(file_exists($file.'.html')){
                $fb = $file.'.html';
            }
            elseif(file_exists($f) && !is_dir($f)){
                $fb = $f;
            }
            elseif(file_exists($f.'.html')){
                $fb = $f.'.html';
            }
            $d = array();
            if($fb){
                
                $d = $this->get_contents($fb);
            }
            return $d;
        }
        $g = explode('.',$file);
        $ex = strtolower($g[count($g)-1]);
        if($ex!='html') $f.='.html';
        return $this->save_contents($data,$f);
    }
    
    public function get($file=null,$key=null){
        if(is_null($file)) return null;;
        $f = $this->join_url_dir($this->dir,$file);
        $t = 0;
        $fb = null;
        if(file_exists($file) && !is_dir($file)){
            $fb = $file;
        }
        elseif(file_exists($f) && !is_dir($f)){
            $fb = $f;
        }
        elseif(file_exists($file.'.json')){
            $fb = $file.'.json';
        }
        elseif(file_exists($f.'.json')){
            $fb = $f.'.json';
        }
        elseif(file_exists($file.'.sl')){
            $fb = $file.'.sl';
            $t = 1;
        }
        elseif(file_exists($f.'.sl')){
            $fb = $f.'.sl';
            $t = 1;
        }
        if($fb){
            $c = $this->get_contents($fb);
            if($t==0||$t==1){
                $d = ($t)?unserialize($c):json_decode($c);
                $a = _do2a($d);
                $b = new Arr($a);
                return $b->get($key);//;
            }
            return $c;
        }
        return null;
        
    }
    public function put($file=null,$type='txt',$data = null){
        if(is_string($file) && is_string($type)){
            $s = false;
            switch(strtolower($type)){
                case 'json':
                    $s = $this->json($file,$data);
                break;
                case 'sl':
                    $s = $this->serliz($file,$data);
                break;
                case 'txt':
                    $s = $this->text($file,$data);
                break;
                case 'html':
                    $s = $this->html($file,$data);
                break;
                
                default:
                    $g = explode('.',$file);
                    $ex = strtolower($g[count($g)-1]);
                    if($ex!=$type) $f.='.'.$type;
                    $s = $this->save_contents($data,$f);
                break;
            }
            return $s;
        }
        return false;
    }
}

?>