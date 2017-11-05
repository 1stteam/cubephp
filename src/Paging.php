<?php

/**
 * @author Le Ngoc Doan
 * @copyright 2015
 * @updated 2017-09-22
 */
namespace Cube;

class Paging{
    public $total;
    public $per_page;
    public $current_page;
    public $max_page_item;
    public $_pages_item=array();
    public $_limit=array();
    protected $_mod=0;
    protected $_div=1;
    protected $total_page;
    protected $start = 1;
    protected $fin = 1;
    protected $lang = array(
        'title' => array(
            'first' => 'First Page',
            'preview' => 'Previous Page',
            'page' => 'Page [n]',
            'next' => 'Next Page',
            'last' => 'Lastest Page ([n])'
        ),
        'text' => array(
            'first' => '|<',
            'preview' => '<<',
            'page' => '[n]',
            'next' => '>>',
            'last' => '>|'
        )
    );
    
    public $pre = 0;
    public $aft = 0;
    /**
     * cai dat thong so
     * @param int $total tong so
     * @param int $per_page so item moi trang
     * @param int $current_page trang hiern tai
     * @param int $max_page_item so trang hien thi toi da
     */ 
    public function __construct($total=0,$per_page=10,$current_page=1,$max_page_item=5){
        $this->setup($total,$per_page,$current_page,$max_page_item);
        
    }
    /**
     * cai dat thong so
     * @param int $total tong so
     * @param int $per_page so item moi trang
     * @param int $current_page trang hiern tai
     * @param int $max_page_item so trang hien thi toi da
     */ 
    public function setup($total=0,$per_page=10,$current_page=1,$max_page_item=5){
        $this->reset();
        $total = (int) $total;
        $per_page = (int) $per_page;
        $current_page = (int) $current_page;
        $max_page_item = (int) $max_page_item;
        if(!is_numeric($total)||$total<0) $total = 0;
        if(!is_numeric($per_page)||$per_page<1) $per_page = 10;
        if(!is_numeric($current_page)||$current_page<1) $current_page = 1;
        if(!is_numeric($max_page_item)||$max_page_item<2) $max_page_item = 5;
        
        $this->total = $total;
        $this->per_page = $per_page;
        $this->max_page_item = $max_page_item;
        
        $mod = $total%$per_page;
        $div = ($total-$mod)/$per_page;
        $total_page = ($div<$total/$per_page)?$div+1:$div;
        
        $this->_mod = $mod;
        $this->_div = $div;
        $this->total_page = $total_page;
        
        if($current_page>$total_page) $current_page = $total_page;
        $this->current_page = $current_page;
        
        $g = $max_page_item%2;
        
        $dp = ($max_page_item-$g)/2;
        
        $start = $current_page - ($dp-1+$g);
        if($start<1) $start = 1;
        
        $this->pre = $dp-1+$g;
        $this->aft = $dp;
        
        $fin = $current_page+$dp;
        if($fin>$total_page) $fin = $total_page;
        
        $this->start = $start;
        $this->fin = $fin;
        
        $ls = ($current_page-1)*$per_page;
        if($ls<0) $ls=0;
        $lim = array(
            'array' => array($ls,$per_page),
            'string' => "$ls, $per_page"
        );
        $this->_limit = $lim;
        
        $a = array();
        for($i=$start; $i<=$fin; $i++){
            $a[] = $i;
        }
        
        $this->_pages_item['pages'] = $a;
        $this->_pages_item['ctrl'] = array('first'=>1,'preview'=>$current_page-1,'next'=>$current_page+1,'last'=>$total_page);
        return $this;
    }
    public function reset(){
        $this->total = 0;
        $this->current_page = 1;
        $this->per_page = 10;
        $this->_limit = array();
        $this->_div = 0;
        $this->_mod = 0;
        $this->pre = 0;
        $this->aft = 0;
        $this->start = 0;
        $this->fin = 0;
    }
    
    /**
     * cai dat ngon mgu
     * @param Array $lang chen chu cho button
     */ 
    public function setText($tests=null){
        if(is_array($tests)){
            if(isset($tests['text'])) $this->lang['text'] = $tests['text'];
            if(isset($tests['title'])) $this->lang['title'] = $tests['title'];
        }
        return $this;
    }
    /**
     * lay ds url page
     * @param String URL mau
     */ 
    
    public function getButtons($url='http://localhost/',$queryString='page'){
        $a = $this->start;
        $b = $this->fin;
        $c = $this->current_page;
        $d = $this->total_page;
        $btns = array();
        if($a>2){
            $btns[] = array(
                'title' => $this->eval_text('title|first',array('n'=>1)),
                'text' => $this->eval_text('text|first',array('n'=>1)),
                'link' => reqURL($url,$queryString,1),
                'class' => 'first-page page-item',
                'page' => 1,
                'active' => false
            );
        }
        if($a>1){
            $e = $c - 1;
            $btns[] = array(
                'title' => $this->eval_text('title|preview',array('n'=>$e)),
                'text' => $this->eval_text('text|preview',array('n'=>$e)),
                'link' => reqURL($url,$queryString,$e),
                'class' => 'previous-page page-item',
                'page' => $e,
                'active' => false
            );
        }
        for($i=$a; $i<=$b;$i++){
            $btns[] = array(
                'title' => $this->eval_text('title|page',array('n'=>$i)),
                'text' => $this->eval_text('text|page',array('n'=>$i)),
                'link' => reqURL($url,$queryString,$i),
                'class' => 'page-'.$i.(($c==$i)?' active page-item active':''),
                'page' => $i,
                'active' => (($c==$i)?true:false)
            );
        }
        
        if($b<$d){
            $f = $c + 1;
            $btns[] = array(
                'title' => $this->eval_text('title|next',array('n'=>$f)),
                'text' => $this->eval_text('text|next',array('n'=>$f)),
                'link' => reqURL($url,$queryString,$f),
                'class' => 'next-page page-item',
                'page' => $f,
                'active' => false
            );
        }
        if($b<$d-1){
            $btns[] = array(
                'title' => $this->eval_text('title|last',array('n'=>$d)),
                'text' => $this->eval_text('text|last',array('n'=>$d)),
                'link' => reqURL($url,$queryString,$d),
                'class' => 'last-page page-item',
                'page' => $d,
                'active' => false
            );
        }
        
        return $btns;
    }

    public function getPagination($url="http://localhost/",$queryString='page',$prop=null)
    {
        $buttons = $this->getButtons($url,$queryString);
        if(count($buttons)<2) return null;
        $li = "";
        foreach ($buttons as $btn) {
            $li .="<li class=\"$btn[class]\">"
                . "<a class=\"page-link\" title=\"$btn[title]\" href=\"$btn[link]\">$btn[text]"
                . ($btn['active']?'<span class="sr-only">(current)</span>':'')
                . "</a>"
                . "</li>";
        }
        return cube_get_html('ul',$li,$prop);
    }
    
    /**
     * @param Text
     * @param Nomber
     * @param Nomber
     * @param Nomber
     */
    public function getLimitItem($type='array',$total=null,$per_page=null,$current_page=null){
        $type = strtolower($type);
        if(is_numeric($total)&&is_numeric($per_page)&&is_numeric($current_page)){
            $s = ($current_page-1)*$per_page;
            $f = $per_page;
            if($type=='string') $l = $s.', '.$f;
            else $l = array($s,$f);
        }
        elseif(isset($this->_limit[$type])) $l = $this->_limit[$type];
        else $l = array(0,10);
        return $l;
    }
    
    
    
    
    
    
    /**
     * lay text
     * @param String $key
     * @param String $key
     */
    public function getText($key=null,$char="|"){
        $array = $this->lang;
        if(!is_array($array)) return null;
        if(is_null($key)) return $array;
        $p = explode($char,$key);
        $t = count($p);
        for($i = 0; $i < $t; $i++){
            $c = trim($p[$i]);
            if(is_array($array) && isset($array[$c])){
                $array = $array[$c];
            }else{
                $array = null;
                $i+=$t;
            }
        }
        
        return $array;
    }
    
    /**
     * lay text
     * @param String $key
     * @param Array $data
     */
    public function eval_text($key=null,$data=array()){
        $str = $this->getText($key);
        if(is_string($str) && is_array($data))
            foreach($data as $k => $v)
                $str = str_replace('['.$k.']',$v,$str);
        return $str;
    }
}

?>