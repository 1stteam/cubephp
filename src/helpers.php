<?php
namespace Cube;

function join_url($main,$add=null){
	if($add){
		$main = ltrim($main,'/').'/'.rtrim($add,'/');
	}
	return $main;
}

?>