<?php

/**
 * @author Le Ngoc Doan
 * @copyright 2017
 * @describe dung de kiem tra thong tin form truyen vao 
 */

namespace Cube;

class FormData
{
    protected $tableName = '';
    public $id=null;
    protected $errorFile = '';
    protected $fields = array();
    private $tableMask = null;
    protected $msgArr = array();
    protected $data = array();
    protected $errors = array();


    protected $form = array();
    protected $validate = array();
    protected $applies = array();
    protected $applyData = array();

    public function __construct($form)
    {
        $this->setFormData($form);
    }
    public function setTable($tableName=null,$id=null)
    {
        $this->tableName = $tableName;
        $this->id = $id;
    }
    
    // tao bang mat na de thao tac voi mot bang

	// public final function createMask($tableName = null){
	// 	if(!$this->tableMask){
	// 		if($tableName) $tbn = $tableName;
	// 		else $tbn = $this->tableName;
	// 		if(!$tbn) die('ten bang chua dc set');
	// 		$this->tableMask = new Models\TableMask($tbn);
	// 	}
		
	// }

	// public final function mask(){
	// 	$this->createMask();
	// 	return $this->tableMask;
	// }

	// public static final function getMask()
	// {
	// 	$model = new static();
	// 	return $model->mask();
 //    }
    
    public function setFormData(Array $form)
    {
        $this->form = $form;
    }

    public function setApplyArgs(Array $args)
    {
    	$this->applies = $args;
    }

    public function setValidateArgs(Array $args)
    {
    	$this->applies = $args;
    }

    public function setErrorMessageFile($filename)
    {
        $this->errorFile = $filename;
        $this->msgArr = App::data()->getJSON($filename,null,true);
    }

    public function setFieldsText($fields,$key=null)
    {
        if(is_string($fields)){
            $fs = App::data()->getJSON($fields,$key);
            if($fs){
                $arr = array();
                foreach($fs as $name => $f){
                	$nm = isset($f->name)?$f->name:$name;
                    $arr[$nm] = $f->text;
                }
                $this->fields = $arr;
            }
        }elseif(is_array($fields)){
            $this->fields = $fields;
        }
    }

    public function getFieldText($field = null)
    {
        if(is_array($this->fields) && isset($this->fields[$field])) return $this->fields[$field];

        return ucfirst($field);
    }

    public function countError()
    {
        return count($this->errors);
    }
    
    public function getMessageText($field,$errNum)
    {
        $text = $this->getFieldText($field);
        if(is_array($this->msgArr) && count($this->msgArr)>0) $arr = $this->msgArr;
        else $arr = $this->errMsgAll();
        
        if(isset($arr[$errNum])){
            $msg = str_replace('{text}', $text, $arr[$errNum]);
        }else $msg = null;
        
        return $msg;
    }

    public function getMessage($field,$errNum=null)
    {
        if(isset($this->errors[$field])){
            if(!is_null($errNum)){
                $n = $this->errors[$field];
            }else{
                $n = $errNum;
            }
            return $this->getMessageText($field,$n);
        }elseif(!is_null($errNum)){
            return $this->getMessageText($field,$errNum);
        }
        return null;
    }
    public function getAllMessage()
    {
        $arr = array();
        if(count($this->errors)>0){
            foreach($this->errors as $field => $error){
                $arr[$field] = $this->getMessageText($field,$error);
            }
        }
        return $arr;
    }

    public function errMsgAll()
	{
		return array("", "{text} không được bõ trống", "{text} quá ngắn", "{text} không hợp lệ", "{text} đã được sử dụng trước đó!", "{text} không khớp");
	}



    public function getData()
    {
        return $this->data;
    }

    public function applyVal($val = null, $func = null, $args = array())
    {
    	$v = $val;
    	if($func){
    		array_unshift($args, $val);
    		if(is_callable($func)){
    			$v = call_user_func_array($func, $args);
    		}elseif(function_exists($func)){
    			$v = call_user_func_array($func, $args);
    		}elseif(count($obs = explode('->', $func)) == 2){
    			$class = trim($obs[0]);
    			$method = $obs[1];
    			$rc = new ReflectionClass($class);
    			$s = $rc->newInstanceArgs(array($val));
    			if(method_exists($s, $method)){
    				$v = call_user_func_array(array($s, $method), $args);
    			}
    		}
    	}
    	return $v;
    }

    
    public function applyValByArr($val = null, $funcs = null)
    {
    	$v = $val;
    	if(is_array($funcs)){
    		foreach($funcs as $f => $args){
    			if(is_numeric($f)){
    				$v = $this->applyVal($v,$args);
    			}else{
    				if(!is_array($args)){
    					$v = $this->applyVal($v,$f, array($args));
    				}else{
    					$v = $this->applyVal($v,$f, $args);
    				}
    			}
    			
    		}
    	}elseif(is_string($funcs)){
    		$v = $this->applyVal($v,$funcs);
    	}
    	return $v;
    }

    protected function doApply($form, $applyArgs)
    {
    	if(is_array($form) && is_array($applyArgs)){
    		$this->applyData = array();
    		foreach($form as $field => $val){
    			if(isset($applyArgs[$field])) $v = $this->applyValByArr($val,$applyArgs[$field]);
    			else $v = $val;
    			$this->applyData[$field] = $v;
    			$form[$field] = $v;
    		}
    	}
    	return $form;
    }
    public function apply()
    {
    	$args = func_get_args();
    	$t = count($args);
    	$f = null;
    	$a = null;

    	if($t == 0){
    		if($this->form && $this->applies){
    			$f = $this->form;
    			$a = $this->applies;
    		}
    	}elseif($t == 1){
    		if($this->form){
    			$f = $this->form;
    			$a = $args[0];
    		}elseif($this->applies){
    			$f = $args[0];
    			$a = $this->applies;
    		}
    	}else{
    		$f = $args[0];
    		$a = $args[1];
    	}
    	if($f) return $this->doApply($f,$a);
    	return ($t==0?null:$args[0]);
    }



    // end apply



	public function validateField($field, $val='' ,$func='len >= 1', $type='insert')
	{
		$err = 0;
		switch(strtolower($func)){
			case 'namespace':
				$err = $this->validateNamespace($field, $val,$type);
				break;

			case 'accessname':
				$err = $this->validateAccessName($field, $val,$type);
				break;

			case 'unique':
				$err = $this->validateUnique($field, $val,$type);
				break;
			case 'email':
				$err = $this->validateEmail($val);
				break;

			case 'unique_email':
				if($e = $this->validateEmail($val)){
					$err = $e;
				}elseif($ee = $this->validateUnique($field,$val,$type)){
					$err = $ee;
				}
				break;

			case 'name':
				$err = $this->validateName($val);
				break;

			case 'exists':
				$err = $this->validateExists($field,$val);
				break;

			default: 
                if(is_callable($func)){

                    $a = call_user_func_array($func,array($val));
                    if(!$a) $err = 3;
                }
                elseif(function_exists($func)){
                    $a = call_user_func($func,$val);
                    if(!$a) $err = 3;
                }
                else{
					$p = '/(.*)?\s*(\!=|<=|>=)\s*(.*)?/si';
					$p2 = '/(.*)?\s*(<|>|=)\s*(.*)?/si';
					$o = "=";
					$f = "";
					$v = null;
					if(preg_match_all($p,trim($func),$m)){
						$f = $m[1][0];
						$o = $m[2][0];
						$v = trim($m[3][0]);
					}
					elseif(preg_match_all($p2,trim($func),$m)){
						$f = $m[1][0];
						$o = $m[2][0];
						$v = trim($m[3][0]);
					}
					if($fn = strtolower(trim($f))){
						if($fn == 'len'){
							$err = $this->validateLength($val,$v,$o);
						}elseif($fn == 'val'){
                            $err = $this->compareVal($val,$v, $o);
						}
					}
				}
				break;
		}
		return $err;
	}

	protected function validateByFuncArr($field, $val= null, $funcs = array(),$type='insert')
	{
		foreach($funcs as $k => $f){
			if(is_numeric($k)){
				if($e = $this->validateField($field, $val, $f,$type)){
					return $e;
				}
			}else{
				if(function_exists($k)){
					$a = call_user_func_array($k,array($val));
					if($f!=$a) return 5;
				}else{
					return 3;
				}
			}
		}
		return 0;
	}

	public function validate($data, $validate=null,$type='')
	{
        if(!is_array($validate) && count($this->form) > 0){
            if(!$type && is_string($validate)) $type = $validate;
            $validate = $data;
            $data = $this->form;
        }
        $this->validate = $validate;
        $errInd = array();
        $accept = array();
        if(is_array($data) && is_array($validate)){
        	
        	foreach ($data as $key => $value) {
        		if(!isset($va[$key]) && !in_array($key, $validate)){
        			$accept[$key] = $value;
        		}
        	}
			foreach($validate as $k => $f){
				if(is_numeric($k)){
					if(isset($data[$f])){
                        if(strlen($data[$f])<1) $errInd[$f] = 2;
                        else{
                            $accept[$f] = $data[$f];
                        }
					}else{
						$errInd[$f] = 1;
					}
				}else{
					if(isset($data[$k])){
						$val = $data[$k];
						if(is_array($f)){
							if($ee = $this->validateByFuncArr($k,$val,$f,$type)){
								$errInd[$k] = $ee;
							}else{
                                $accept[$k] = $val;
                            }
						}else{
							if($e = $this->validateField($k, $val, $f, $type)){
								$errInd[$k] = $e;
							}else{
                                $accept[$k] = $val;
                            }
						}
					}else{
						$errInd[$k] = 1;
					}
				}
			}
			foreach($data as $key => $val){
				if(!isset($accept[$key]) && !isset($errInd[$key])){
					$accept[$key] = $val;
				}
			}
        }

        $this->errors = $errInd;
        $this->data = $accept;
        return (count($errInd)==0?$accept:false);
	}
	

	// func apply & validate
	/**
	 * ap dung cac thuoc tinh cho form. uu tien validate
	 * cac ten ham, class hay phuong thuc dung deap dung cho thuoc tinh phai chua tien to @
	 * 
	 * @param array $data
	 * @param array $applyValidate
	 * @param string $type
	 * @return array
	 */

	public function applyAfterValidate($data=null, $applyValidate=null,$type='')
	{
		if(!is_array($applyValidate) && count($this->form) > 0){
            if(!$type && is_string($applyValidate)) $type = $applyValidate;
            $applyValidate = $data;
            $data = $this->form;
        }
        //$this->validate = $validate;
        $errInd = array();
        $accept = array();
        $applies = array();

        if(is_array($data) && is_array($applyValidate)){
        	
        	foreach ($data as $key => $value) {
        		if(!isset($applyValidate[$key]) && !in_array($key, $applyValidate)){
        			$accept[$key] = $value;
        		}
        	}


        	foreach ($applyValidate as $k => $f) {
        		if(is_numeric($k)){
					if(isset($data[$f])){
                        if(strlen($data[$f])<1) $errInd[$f] = 2;
                        else{
                            $accept[$f] = $data[$f];
                        }
					}else{
						$errInd[$f] = 1;
					}
				}
				else{
					if(isset($data[$k])){
						$val = $data[$k];

						if(is_array($f)){
							$apls = array();
							$fs = $f;
							foreach($f as $fnc => $arg){
								
								if(is_numeric($fnc)){
									if(substr($arg, 0,1) == '@'){
										$apls[] = substr($arg, 1);
										unset($f[$fnc]);
									}
								}elseif(substr($fnc, 0, 1)=='@'){
									$apls[substr($fnc, 1)] = $arg;
									unset($f[$fnc]);
								}
							}
							
							if($apls){
								if(!isset($applies[$k])) $applies[$k] = $apls;
								else $applies[$k] = array_merge($applies[$k],$apls);
							}
							if(!$f){
								$accept[$k] = $val;
								unset($applyValidate[$k]);
							}
							elseif($ee = $this->validateByFuncArr($k,$val,$f,$type)){
								$errInd[$k] = $ee;
							}else{
                                $accept[$k] = $val;
                            }
						}
						elseif(substr($f, 0, 1)=='@'){
							if(!isset($applies[$k])) $applies[$k] = array();
							$applies[$k][] = substr($f, 1);
							$accept[$k] = $val;
							unset($applyValidate[$f]);
						}
						else{
							if(strlen($data[$k])<1) $errInd[$k] = 2;
							elseif($e = $this->validateField($k, $val, $f, $type)){
								$errInd[$k] = $e;
							}else{
                                $accept[$k] = $val;
                            }
						}
					}else{
						$errInd[$k] = 1;
					}
				}
        	}
        }

        $this->errors = $errInd;
        $this->data = ($applies?$this->doApply($accept,$applies):$accept);
        
        return (count($errInd)==0?$this->data:false);
	}

	public function validateAfterApply($data=null, $applyValidate=null,$type='')
	{
		if(!is_array($applyValidate) && count($this->form) > 0){
            if(!$type && is_string($applyValidate)) $type = $applyValidate;
            $applyValidate = $data;
            $data = $this->form;
        }
        $applies = array();
        $validate = array();
        if(is_array($data) && is_array($applyValidate)){
        	
        	$apva = $applyValidate;
        	foreach ($applyValidate as $k => $f) {
        		if(!is_numeric($k)){
					if(isset($data[$k])){
						if(is_array($f)){
							$apls = array();
							$fs = $f;
							foreach($f as $fnc => $arg){
								if(is_numeric($fnc)){
									if(substr($arg, 0,1) == '@'){
										$apls[] = substr($arg, 1);
										unset($f[$fnc]);
									}
								}elseif(substr($fnc, 0, 1)=='@'){
									$apls[substr($fnc, 1)] = $arg;
									unset($f[$fnc]);
								}
							}
							
							if($apls){
								if(!isset($applies[$k])) $applies[$k] = $apls;
								else $applies[$k] = array_merge($applies[$k],$apls);
							}
							if(!$f){
								unset($apva[$k]);
							}
							else{
                                $validate[$k] = $f;
                            }
						}
						elseif(substr($f, 0, 1)=='@'){
							if(!isset($applies[$k])) $applies[$k] = array();
							$applies[$k][] = substr($f, 1);
							unset($apva[$f]);
						}
						else{
							$validate[$k] = $f;
						}
					}
				}
				else{
					$validate[$k] = $f;
				}
        	}
        	if(count($applies) > 0){
				$data = $this->apply($data,$applies,$type);
			}
			$accept = $this->validate($data,$validate,$type);
        }
        return $accept;
	}




	/**
	 * kiwm tra khong gian ten xem co hop le hay khong
	 * @param string $column ten cot
	 * @param string $value Gia tri can kiem tra
	 * @param string $id (chi them vao neu can kiem tra trong truong hop update)
	 * 
	 * @return boolean
	 */
  //    public function validateExists($column=null, $value=null){
		// if(!$column || is_null($value)) return 1;
		// $mask = $this->mask();
		
		// $stt = 3;
  //       if($u = $mask->getOne($mask->idField,array($column=>$value), 'ASSOC')){
  //           $stt = 0;
  //       }return $stt;
  //   }

	/**
	 * kiwm tra khong gian ten xem co hop le hay khong
	 * @param string $column ten cot
	 * @param string $value Gia tri can kiem tra
	 * @param string $id (chi them vao neu can kiem tra trong truong hop update)
	 * 
	 * @return boolean
	 */
     public function validateNamespace($column=null, $value=null,$type='insert'){
		if(!$column || is_null($value)) return 1;
		// if(strtolower($type)=='update')
		// 	$id = $this->id;
		// else
		// 	$id = null;
		
		// $mask = $this->mask();
		
		$stt = 4;
        if(!preg_match('/^([A-Za-z_])+([A-Za-z0-9_\-])*$/',$value)) $stt = 3;
        // elseif($u = $mask->getOne($mask->idField,array($column=>$value), 'ASSOC')){
        //     if($id && $u[$mask->idField]==$id) $stt = 0;
        // }
        // 
        else $stt = 0;
        return $stt;
    }

	/**
	 * kiwm tra khong gian ten xem co hop le hay khong
	 * @param string $column ten cot
	 * @param string $value Gia tri can kiem tra
	 * @param string $id (chi them vao neu can kiem tra trong truong hop update)
	 * 
	 * @return boolean
	 */
    public function validateAccessName($column=null, $value=null,$type='insert'){
		if(!$column || is_null($value)) return 1;
		// if(strtolower($type)=='update')
		// 	$id = $this->id;
		// else
		// 	$id = null;
		
		// $mask = $this->mask();
		
		// $stt = 4;
        if(!preg_match('/^([A-Za-z])+([A-Za-z0-9_\.])*$/',$value)) $stt = 3;
        // elseif($u = $mask->getOne($mask->idField,array($column=>$value), 'ASSOC')){
        //     if($id && $u[$mask->idField]==$id) $stt = 0;
        // }
        else $stt = 0;
        return $stt;
    }

	/**
	 * kiem tra xem co bi trung hay ko
	 * @param string $column ten cot
	 * @param string $value Gia tri can kiem tra
	 * @param string $id (chi them vao neu can kiem tra trong truong hop update)
	 * 
	 * @return boolean
	 */
	public function validateUnique($column=null, $value=null,$type='insert'){
		// if(!$column || is_null($value)) return 1;
		// $mask = $this->mask();
		// if(strtolower($type)=='update')
		// 	$id = $this->id;
		// else
		// 	$id = null;
		// $stt = 4;
  //       if($u = $mask->getOne($mask->idField,array($column=>$value), 'ASSOC')){
  //           if($id && $u[$mask->idField]==$id) $stt = 0;
  //       }else $stt = 0;
  //       return $stt;
	}
	
	public function validateEmail($email=null){
		if(!$email) $stt = 1;
        elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)) $stt = 3;
        else $stt = 0;
        return $stt;
	}
	public function validateLength($value='',$length=1,$operator='>=')
	{
		$stt=2;
		$str = "if(strlen(\$value)==0) \$stt=1; elseif(strlen(\$value) $operator \$length) \$stt=0; else \$stt=2;";
		eval($str);
		return $stt;
	}
	public function compareVal($value='',$length=1,$operator='>=')
	{
		$stt=2;
		$str = "if(\$value $operator \$length) \$stt=0; else {if(\$operator=='==')\$stt=5; else \$stt=3; }";
		eval($str);
		return $stt;
	}
	public function validateName($name = '')
	{
        $stt = 1;
		$Str = new Str();
		if($name){
			if(!preg_match('/[^A-z\s]/', $Str->clearUnicode($name))){
				$stt = 0;
			}
			else{
				$stt = 3;
			}
		}else{
			$stt = 1;
        }
        return $stt;
	}
	

}
