<?php

/**
 * @author Doanln
 * @copyright 2017
 */

namespace Cube;
//chua lam gi
class HTTPRequest{
    public function __construct() {
        foreach($_REQUEST as $name => $val){
            try{
                eval("\$this->$name = \$val;");
            }catch(exception $e){
                
            }
        }
        
    }
    public function input($name=null){
        if(!is_null($name)){
            $a = null;
            if(property_exists($this,$name)){
                eval("\$a = \$this->$name;");
            }
            return $a;
        }
        return _ota($this);
    }
    public function getMethod(){
        return $_SERVER['REQUEST_METHOD'];
    }
    public function isMethod($method='GET'){
        return $_SERVER['REQUEST_METHOD'] == strtoupper($method) ? true : false;
    }
    
    public function isGet(){
        return $this->isMethod();
    }
    public function isPost(){
        return $this->isMethod('POST');
    }
    public function isPut(){
        return $this->isMethod('PUT');
    }
    public function isDelete(){
        return $this->isMethod('DELETE');
    }
    public function isHead(){
        return $this->isMethod('HEAD');
    }
    public function isOoptions(){
        return $this->isMethod('OPTIONS');
    }
    
    
    public function get($name=null){
        if(is_null($name)) return $_GET;
        if(preg_match('/\./',$name)){
            $a = new Arr($_GET);
            return $a->get($name);
        }
        return isset($_GET[$name])?$_GET[$name]:null;
    }
    public function post($name=null){
        if(is_null($name)) return $_POST;
        if(preg_match('/\./',$name)){
            $a = new Arr($_POST);
            return $a->get($name);
        }
        return isset($_POST[$name])?$_POST[$name]:null;
    }
    public function request($name=null){
        if(is_null($name)) return $_REQUEST;
        if(preg_match('/\./',$name)){
            $a = new Arr($_REQUEST);
            return $a->get($name);
        }
        return isset($_REQUEST[$name])?$_REQUEST[$name]:null;
    }
    public function query($name=null)
    {
        $query = isset($_SERVER['QUERY_STRING'])?$_SERVER['QUERY_STRING']:null;
        if($query){
            if(is_null($name)) return $query;
            parse_str($query,$arr);
            return isset($arr[$name])?$arr[$name]:null;
        }
        return null;
    }
    public function files($name=null){
        return FileUpload($name);
    }

    public function file($name=null){
        return CubeSimpleFileUpload($name);
    }
}
/**
* request
*/
class Request extends HTTPRequest
{
    
    public function __construct()
    {
        parent::__construct();
    }
}

function request()
{
    $a = new Request();
    return $a;
}
?>