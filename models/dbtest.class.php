<?php 
namespace models;

if(!PFW_INIT){
	echo "break in";
	die;
}

class dbtest extends models {
	
	public function test(){
		$row=$this->db->get_results("select * from t_meproject_shop_list limit 10",ARRAY_A);
		return $row;
		
	}
	
}