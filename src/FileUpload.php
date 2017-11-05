<?php

/**
 * @author Le Ngoc Doan
 * @copyright 2017
 */
namespace Cube;

define('UNIQUE_FILENAME', '<!%Unique%!>');

class SimpleFileUpload{
    protected $filename = null;
    protected $original_name = null;
    protected $type = null;
    protected $temp = null;
    protected $size = 0;
    protected $error = null;
    protected $has = false;
    protected $ext = null;
    protected $uploaded_dir = null;
    protected $uploaded_filename = null;
    /**
     * tao doi tuong file upload
     * @param Array
     */ 
     
    public function __construct($file) {
        if(is_string($file)){
            if(isset($_FILES[$file])){
                $f = $_FILES[$file];
                if(is_array($f['name'])){
                    $file = array();
                    foreach($f as $key => $vals){
                        $file[$key] = $vals[0];
                    }
                }else{
                    $file = $f;
                }
            }
        }
        if(is_array($file)){
            if(isset($file['name']) && isset($file['type']) && isset($file['size']) && isset($file['tmp_name'])){
                $this->filename = $file['name'];
                $this->original_name = $file['name'];
                $this->type = $file['type'];
                $this->size = $file['size']/1024;
                $this->temp = $file['tmp_name'];
                if($this->size > 0) $this->has = true;
                $es = explode('.',$this->filename);
                $this->ext = array_pop($es);
            }
            if(isset($file['size'])) $this->error = $file['error'];
        }
    }
    
    public function has(){
        return $this->has;
    }
    /**
     * ham set file name
     * @param String $filename Ten file
     */ 
    
    public function setFilename($filename=null){
        if($filename==UNIQUE_FILENAME){
            $this->filename = uniqid().'.'.$this->getExtension();
        }
        elseif(is_string($filename) && strlen($filename)>0){
            $a = explode('.', $filename);
            $fn = null;
            if(count($a)>0){
                $ext = array_pop($a);
                if($ext && $this->isExtension($ext)){
                    $fn = implode('.', $a).'.'.$ext;
                }else{
                    $fn = $filename.'.'.$this->getExtension();
                }
            }else{
                $fn = $filename.'.'.$this->getExtension();
            }
            $this->filename = $fn;
        }
        return $this;
    }
    
    public function getFilename(){
        return $this->filename;
    }
    public function getOriginalFilename(){
        return $this->original_name;
    }
    
    
    
    public function getExtension(){
        return $this->ext;
    }
    
    public function getMime(){
        return $this->type;
    }
    
    public function isImage(){
        return (substr(strtolower($this->type),0,5)=='image')? true : false;
    }
    
    public function toImageObject(){
        if(!$this->isImage()) return null;
        $a = new Image($this->temp);
        return $a;
    }
    
    
    public function getContents()
    {
        return file_get_contents($this->temp);
    }

    public function tmp()
    {
        return $this->temp;
    }
    /**
     * di chuyen file
     * @param String $dirname duong dan
     * @param String $filename Ten file
     */ 
    
    
    public function move($dirname = null, $filename=null){
        $fp = null;
        $this->uploaded_dir = null;
        $this->uploaded_filename = null;
        $dia = false; //dirname in array
        if(is_array($dirname))
        {
            $d = $dirname;
            $f = array('dirname','filename');
            $n = 0;
            foreach($d as $i => $v){
                if(is_numeric($i) && isset($f[$n])){
                    $a = $f[$n];
                    $$a = $v; 
                
                }elseif(in_array($i,$f)){
                    $$i = $v;
                }
                $n++;
            }
            if(is_string($dirname)) $dia = true;
        }
        elseif($dirname==UNIQUE_FILENAME)
        {
            if(!$filename){
                $filename = uniqid();
                $dirname = null;
            }
            else{
                $dirname = uniqid();
                $dia = true;
            }
        }
        elseif($dirname==UNIQUE_FILENAME.'/')
        {
            $dirname = uniqid().'/';
            $dia = true;
        }
        
        if($dirname){
            if(count(explode(BASEDIR,$dirname))<2){
                $dirname = join_url(UPLOADDIR,$dirname);
            }
            if(is_dir($dirname) || substr($dirname, strlen($dirname)-1)=='/'){
                if(!$filename) $filename = $this->filename;
                $fp = cube_join_url($dirname,$filename);
            }else{
                if($filename){
                    $fp = cube_join_url($dirname,$filename);
                }
                elseif(!$dia){
                    $parts = explode('/', ltrim($dirname,'/'));
                    $file = array_pop($parts);
                    $fs = explode('.',$file);
                    if(count($fs)>1){
                        $ex = array_pop($fs);
                        if($this->isExtension($ex)){
                            $fp = $dirname;
                        }
                    }
                }
                if(!$fp) $fp = cube_join_url($dirname,$this->filename);
            }
        }
        elseif($filename){
            if(is_dir($filename)){
                if(!$filename) $fp = cube_join_url($filename,$this->filename);
                
            }else{
                if(count(explode(BASEDIR,$filename))<2){
                    $filename = cube_join_url(UPLOADDIR,$filename);
                }
                $parts = explode('/', ltrim($filename,'/'));
                $file = array_pop($parts);
                $fs = explode('.',$file);
                if(count($fs)>1){
                    $ex = array_pop($fs);
                    if($this->isExtension($ex)){
                        $fp = $filename;
                    }else{
                        $fp = $filename.'.'.$this->ext;
                    }
                }
                else{
                    $fp = $filename.'.'.$this->ext;
                }
            
            }
        }else{
            $fp = cube_join_url(UPLOADDIR,$this->filename);
        }
        $fp = str_replace(BASEDIR, '',$fp);
        $parts = explode('/', ltrim($fp,'/'));
        $file = array_pop($parts);
        $dir = BASEDIR;
        try{
            foreach($parts as $part){
                if(!is_dir($dir = cube_join_url($dir,$part))){
                    mkdir($dir);
                }
            }
            if(move_uploaded_file($this->temp,"$dir/$file")){
                $this->uploaded_dir = $dir;
                $this->uploaded_filename = $file;
                return true;
            }
            
        }catch(exception $e){
            return false;
        }
        return false;
        
    }
    
    public function matchFilename($search=null)
    {
        if(is_string($search)){
            $s = str_replace(array('.','-'), array('\\.','\\-'), $search);
            if(preg_match('/'.$s.'/si', $this->filename)){
                return true;
            }
        }
        return false;
    }
    
    public function getUploadedFilename(){
        return $this->uploaded_filename;
    }
    public function getUploadedDir(){
        return $this->uploaded_dir;
    }
    
    
    public function isAccept(){
        return $this->isExtension($this->ext);
    }
    
    public static function getFilenameInfo($filename)
    {
        $a = array();
        $fn = null;
        $ex = null;
        if(is_string($filename)){
            $fps = explode('.', $filename);
            if(count($fps)>1){
                $ext = array_pop($fps);
                $exts = self::getAcceptMime();
                if(isset($exts[$ext])){
                    $fn = implode('.', $fps);
                    $ex = $ext;
                }else{
                    $fn = $filename;
                }
            }
            else{
                $fn = $filename;
            }
        }
        return array($fn,$ex);
    }
    
    protected function getExtByMime($mine=null){
        if(is_null($mine)) $mine = $this->size;
        if(is_null($mine)) return null;
        $a = self::mimeToExts();
        if(isset($a[$mine])) return $a[$mine];
        return null;
    }
    
    protected function isExtension($ext){
        $a = self::getAcceptMime();
        return isset($a[$ext]);
    }
    
    protected static function mimeToExts(){
        return array_flip(self::getAcceptMime());
    }
    protected static function getAcceptMime(){
        return files::get_mime();
    }

}
/**
 * @param String $name Ten file
 */
function CubeSimpleFileUpload($name=null){
    if(is_string($name) && isset($_FILES[$name]) && !$_FILES[$name]['error']){
        $fu = new SimpleFileUpload($_FILES[$name]);
        return $fu;
    }elseif(is_array($name)){
        $fu = new SimpleFileUpload($name);
        if($fu->has())
            return $fu;
    }
    return null;
}


class MultipleFileUpload{
    protected $files = array();
    protected $uploaded_files = array();
    protected $upload_fail = array();
    public function __construct($files)
    {
        if(is_string($files)){
            if(isset($_FILES[$files])){
              $files = $_FILES[$files];
            }
        }
        if(is_array($files)){
            if(isset($files['name']) && isset($files['tmp_name']) && isset($files['size'])){
                $flist = array();
                foreach($files as $key => $values){
                    foreach($values as $n => $val){
                        if(!isset($flist[$n])) $flist[$n] = array();
                        $flist[$n][$key] = $val;
                    }
                }
                if(count($flist) > 0){
                    foreach($flist as $file){
                        if($f = CubeSimpleFileUpload($file)){
                            $this->files[] = $f;
                        }
                    }
                }
            }
        }
    }

    public function setFilename($filename=null,$fcount = '-{n}',$incl_first = false){
        $t = count($this->files);
        if($t>0){
            $fnl = array();
            if($filename==UNIQUE_FILENAME){
                for($i = 0; $i < $t; $i++){
                    $this->files[$i]->setFilename($filename);
                }
                return $this;
            }
            if(is_array($filename)){
                $c = count($filename);
                if($c < $t){
                    $last = $filename[$c-1];
                    for($i = 0; $i < $c; $i++){
                        $fnl[] = $filename[$i];
                    }
                    $n = 2;
                    $fi = SimpleFileUpload::getFilenameInfo($last);
                    for($i = $c; $i < $t; $i++){
                        $fnl[] = $fi[0].str_replace('{n}', $n, $fcount).($fi[1]?'.'.$fi[1]:'');
                    }
                }else{
                    for($i = 0; $i <$t; $i++){
                        $fnl[] = $filename[$i];
                    }
                }
            }elseif(is_string($filename)){
                $fi = SimpleFileUpload::getFilenameInfo($filename);
                $n = 1;
                $fntpl = $fi[0].$fcount.($fi[1]?'.'.$fi[1]:'');
                for($i = 0; $i < $t; $i++){
                    $fnl[] = str_replace('{n}', $n, $fntpl);
                    $n++;
                }
                if(!$incl_first) $fnl[0] = $filename;
            }
            if(count($fnl)>0){
                for($i = 0; $i < $t; $i++){
                    $this->files[$i]->setFilename($fnl[$i]);
                }
            }
        }
        return $this;
    }

    public function move($dirname=null, $filename=null){
        if(!$this->files) return false;
        $fp = null;
        $this->uploaded_files = array();
        $this->upload_fail = array();
        $stt = false;
        if($filename) $this->setFilename($filename);
        foreach($this->files as $file){
            if($file->move($dirname)){
                $stt = true;
                $this->uploaded_files[] = $file->getUploadedFilename();
            }else{
                $this->upload_fail[] = $file->getFilename();
            }
        }
        return $stt;
    }

    public function successAll()
    {
        $t = count($this->files);
        $c = count($this->uploaded_files);
        return ($t>0 && $t==$c)? true : false;
    }

    public function getUploadedFilename($tostr=false){
        $f = $this->uploaded_files;
        if($tostr) $f = implode(',', $f);
        return $f;
    }

    public function getUploadErrorFilename($tostr=false){
        $f = $this->upload_fail;
        if($tostr) $f = implode(',', $f);
        return $f;
    }

    public function count(){
        return count($this->files);
    }

    public function has()
    {
        return $this->count() > 0 ? true : false;
    }
    public function fimd($search=null)
    {
        if(is_string($search)){
            if($this->has()){
                
                foreach($this->files as $f){
                    if($f->matchFilename($search)) return $f;
                }
            }
        }
        return null;
    }
}

function CubeMultipleFileUpload($name){
    //
    $files = new MultipleFileUpload($name);
    if($files->count()>0) return $files;

    return null;
}

class CubeFileUpload{
    protected $files;
    protected $multiple = false;
    protected $is_set = false;
    protected $_uploaded = false;
    public function __construct($name)
    {
        if(isset($_FILES[$name])){
            
            $f = $_FILES[$name];
            if(is_array($f['name'])){
                if($files = CubeMultipleFileUpload($f)){
                    $this->is_set = true;
                    $this->multiple = true;
                    $this->files = $files;
                }
            }else{
                if($file = CubeSimpleFileUpload($f)){
                    $this->is_set = true;
                    $this->files = $file;
                }
            }
        }
    }

    public function isMultiple()
    {
        return $this->multiple;
    }

    public function count()
    {
        if($this->is_set){
            if($this->isMultiple()) return $this->files->count();
            return 1;
        }
        return 0;
    }

    public function has()
    {
        return ($this->count()>0?true:false);
    }

    public function setFilename($filename=null,$fcount = '-{n}',$incl_first = false)
    {
        $this->files->setFilename($filename,$fcount,$incl_first);
        return $this;
    }

    public function move($dirname=null,$filename=null)
    {
        if($this->files->move($dirname,$filename)){
            $this->_uploaded = true;
            return true;
        }
        return false;
    }

    public function successAll()
    {
        if($this->multiple){
            return $this->files->successAll();
        }
        return $this->_uploaded;
    }

    public function isUploadwed()
    {
        return $this->_uploaded;
    }

    public function getUploadedFilename($tostr=false)
    {
        return $this->files->getUploadedFilename($tostr);
    }
    
    public function getUploadErrorFilename($value='')
    {
        if($this->multiple){
            return $this->files->getUploadedFilename();
        }elseif(!$this->_uploaded) return $this->files->getFilename();
        return null;
    }

    public function single($index=0)
    {
        if($this->multiple){
            if(isset($this->files[$index])){
                return $this->files[$index];
            }
            throw new Exception("out of file index", 1);
        }
        else{
            return $this->files;
        }
    }
    public function find($s=null)
    {
        if($this->multiple){
            foreach ($this->files as $file) {
                if($file->matchFilename($s)) return $file;
            }
            return null;
            
        }
        else{
            throw new Exception("this method doesn't support single file object");
        }
    }

    public function findAll($s=null)
    {
        if($this->multiple){
            $files = array();
            if(is_array($s)){
                foreach($s as $ss){
                    foreach ($this->files as $file) {
                        if($file->matchFilename($s)){
                            $files[] = $file;
                        }
                    }
                }
            }else{
                foreach ($this->files as $file) {
                    if($file->matchFilename($s)){
                        $files[] = $file;
                    }
                }
            }
            return $files;
        }
        else{
            throw new Exception("this method doesn't support single file object", 1);
        }
    }
}


function FileUpload($name=null){
    $f = new CubeFileUpload($name);
    if($f->has()) return $f;
    return null;
}






?>