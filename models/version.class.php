<?php 
namespace models;

if(!PFW_INIT){
	echo "break in";
	die;
}

class version extends models {
	protected $SN = false;
	function __construct($SN){
		parent::__construct();
		$this->SN= $SN;
		return;
	}
	/**
	 * 检查最新的版本
	 * @param string $hd_type 设备型号
	 * @param string $version 版本串
	 * @return mixed
	 */
	public function check_newest_version($hd_type,$version){
		$sql = "select  vid,status,is_need,md5sum,version,file_path,file_size,vinfo,hd_type,is_force,is_formal ";
		$sql.=" from t_update_v where status=1 and FIND_IN_SET('{$hd_type}',hd_type) ";
		$sql.=" order by v1 desc,v2 desc,v3 desc limit 1";
		$res = $this->db->get_row($sql,ARRAY_A);
		if(!$res){
			return false;//no data
		}
		if($res['version']==$version){
			return false;////current version is newest
		}	
		if($res['is_need']==1){ 
			return $res;//必须升级到此版本
		}	
		//检查是否有必须升级的版本
		$arr_version = explode('.',$version);
		$sql  = "select  vid,status,is_need,md5sum,version,file_path,file_size,vinfo,hd_type,is_force,is_formal  from t_update_v ";
		$sql .= " where status=1 and is_need=1 and FIND_IN_SET('{$hd_type}',hd_type) and ( v1>'{$arr_version[0]}'";
		$sql .= " or (v1='{$arr_version[0]}' and v2>'{$arr_version[1]}')";
		$sql .= " or (v1='{$arr_version[0]}' and v2='{$arr_version[1]}' and v3>'{$arr_version[2]}') )";
		$sql .= "  order by v1 desc , v2 desc, v3 desc limit 1";
		$res_need = $this->db->get_row($sql,ARRAY_A);
		if(!$res_need){
			return $res;
		}else { 
			return $res_need;
		}
	}
	/*
	 * 检查升级的版本 v3,返回当前版本的下一个版本
	 */
	public function check_newest_version_v3($hd_type,$version){
		$arr_version = explode('.',$version);
		$key = 'domy.version.newest.next.'.$hd_type.'_'.$version;
		if($this->cache->exists($key)){
			return $this->cache->get($key);
		}
		$sql  = "select  vid,status,is_need,md5sum,version,file_path,file_size,vinfo,hd_type,is_force,is_formal  from t_update_v ";
		$sql .= " where status=1 and FIND_IN_SET('{$hd_type}',hd_type) and ( v1>'{$arr_version[0]}'";
		$sql .= " or (v1='{$arr_version[0]}' and v2>'{$arr_version[1]}')";
		$sql .= " or (v1='{$arr_version[0]}' and v2='{$arr_version[1]}' and v3>'{$arr_version[2]}') )";
		$sql .= "  order by v1 asc , v2 asc, v3 asc limit 1";
		$res = $this->db->get_row($sql,ARRAY_A);
		if(!$res){
			return array();
		}
		if($res['is_formal']==1){
			$this->cache->setex($key,7200,$res);//正式版本加缓存
		}
		return $res;
	}
	/**
	 * 得到版本的详情
	 * @param string $hd_type 设备型号
	 * @param string $version 版本串
	 * @return array
	 */
	public function get_version_info($hd_type,$version){
		$sql = "select vid,status,is_need,md5sum,version,file_path,file_size,vinfo,hd_type,is_force,is_formal from ";
		$sql.=" t_update_v where FIND_IN_SET('{$hd_type}',hd_type) and version='{$version}' limit 1";
		$res = $this->db->get_row($sql,ARRAY_A);
		if(!$res){
			return array();
		}
		return $res;
	}
	/**
	 * 得到版本的详情
	 * @param int $vid 版本ID
	 * @return array
	 */
	public function get_version_info_vid($vid){
		$vid = intval($vid);
		if($vid<1){
			return array();
		}
		$sql = "select vid,status,md5sum,version,file_path,file_size,vinfo,hd_type,is_force,is_formal from ";
		$sql.=" t_update_v where vid='{$vid}'";
		$res = $this->db->get_row($sql,ARRAY_A);
		if(!$res){
			return array();
		}
		return $res;
	}
	/**
	 * 增加    更新版本的会话
	 * @param string $token 会话标记
	 * @param string $version 版本号串
	 * @param int $size 文件大小（字节）
	 * @param string $path 文件路径
	 * @return boolean 
 	 */
	public function insert_version_session($token,$version,$file_size,$file_path){
	   $expire= time()+20*60;
	   $sql = "insert into t_update_vs(token,SN,file_path,version,file_size,expire) ";
	   $sql.= " values('{$token}','{$this->SN}','{$file_path}','{$version}','{$file_size}','{$expire}')";
	   $res = $this->db->query($sql);
	   if(!$res){
	   	return false;
	   }
	   return true;
	}
	/**
	 * 新增版本更新的token SN
	 */
	public function insert_update_token($token,$version,$file_size,$file_path){
		  $key='swdl_'.$this->SN;
		   $arr_data=array(
		   		'token'=>$token,
		   		'SN'=>$this->SN,
		   		'file_path'=>$file_path,
		   		'version'=>$version,
		   		'file_size'=>$file_size
		   );
		  $res = $this->cache->setex($key,60*20,$arr_data);
		  if($res){
		  	return true;
		  }else {
		  	return false;
		  }
	
	}
	/**
	 * 商用版本更新insert token
	 */
	public function insert_update_token_pw($token,$version,$file_size,$file_path){
	   $key='swdl_'.$token;
	  	$arr_data=array(
		   		'token'=>$token,
		   		'SN'=>$this->SN,
		   		'file_path'=>$file_path,
		   		'version'=>$version,
		   		'file_size'=>$file_size
		   );
	 	$res = $this->cache->setex($key,60*10,$arr_data);
		  if($res){
		  	return true;
		  }else {
		  	return false;
		  }
	}
	/**
	 * 删除版本更新的TOKEN
	 */
	public function delete_update_token(){
		$key='swdl_'.$this->SN;
		$res = $this->cache->del($key);
		if(!$res){
			return false;
		}
		return true;
	}
	/**
	 * 得到版本更新 token
	 * @param string $token 会话的标记
	 */
	public function select_update_token(){
		$key='swdl_'.$this->SN;
		$res = $this->cache->get($key);
		if($res){
			return $res;
		}else {
			return array();
		}
	}
	/**
	 * 得到版本更新token数 
	 */
	public function get_swdl_count(){
		$key = 'swdl_*';
		$res = $this->cache->keys($key);
		return count($res);
	}
	
}










