<?php
class ezcache{
	
	private $redis = false;
	private $log = false;
	
	function __construct($redis_host,$redis_port){
		if(!in_array("redis",get_loaded_extensions())){
			throw new Exception("phpredis not installed.");
			return;
		};
		$this->get_redis_connect($redis_host, $redis_port);
	}
	
	function __destruct(){
		@$result=$this->redis->close();
		if($result){
			$this->write_log("Redis connect closed");
		}
	}
	
	/**
	 * 从队列右侧弹出
	 * 
	 * @param string $key 队列名
	 * 
	 */
	public function queue_r_pop($key){
		$serialize_data = $this->redis->rPop($key);
		$result=unserialize($serialize_data);
		if(!$result && $serialize_data){
			$result=$serialize_data;
		}
		if(is_array($result)){
			$result_str = implode(',',$result);
		}else {
			$result_str = $result;
		}
		$this->write_log("get queue {$key} right is " . $result_str);
		return $result;
	}

	/**
	 * 从队列左侧弹出
	 *
	 * @param string $key 队列名
	 *
	 */
	public function queue_l_pop($key){
		$serialize_data = $this->redis->lPop($key);
		$result=unserialize($serialize_data);
		if(!$result && $serialize_data){
			$result=$serialize_data;
		}
		$this->write_log("get queue {$key} left is " . $result);
		return $result;
	}
	
	/**
	 * 从队列右侧压入
	 * 
	 * @param string $key 队列名
	 * @param string $value 值
	 */
	public function queue_r_push($key,$value){
		$value=serialize($value);
		$result=$this->redis->rPush($key,$value);
		$this->write_log("push queue {$key} to {$value} in right");
		return $result;
	}
	

	/**
	 * 从队列左侧压入
	 *
	 * @param string $key 队列名
	 * @param string $value 值
	 */
	public function queue_l_push($key,$value){
		$value=serialize($value);
		$result=$this->redis->lPush($key,$value);
		$this->write_log("push queue {$key} to {$value} in left");
		return $result;
	}
	
	/**
	 * 获取队列长度
	 * 
	 * @param string $key 队列名
	 */
	public function queue_size($key){
		$value = $this->redis->lSize($key);
		$this->write_log("queue $key size is : $value");
		return $value;
	}
	
	
	public function enable_log($obj_log){
		$this->log = $obj_log;
	}
	
	/**
	 * 判断哈希键值是否存在
	 * @param string $key
	 * @param string $item
	 */
	public function hash_exists($key,$item){
		return $this->redis->hExists($key,$item);
	}
	
	/**
	 * 判断键值是否存在
	 * 
	 * @param string $key
	 */
	public function exists($key){
		return $this->redis->exists($key);
	}
	
	/**
	 * 取回哈希键值中所有项目
	 * 
	 * @param string $key
	 *
	 */
	private function hash_get_all($key){
		$result=$this->redis->hGetAll($key);
		if(!$result){
			return false;
		}
		foreach ($result as $item => $value){
			$result[$item]=unserialize($value);
		}
		$this->write_log("get hash cache all in {$key} is " . serialize($result));
		return $result;
	}
	
	/**
	 * 取回哈希键值中所有项目总数
	 *
	 * @param string $key
	 *
	 */
	public function hash_size($key){
		$result=$this->redis->hLen($key);
		if(!$result){
			$this->write_log("get hash size in {$key} is zero.");
			return false;
		}
		$this->write_log("get hash size in {$key} is " . serialize($result));
		return $result;
	}
	
	/**
	 * 获取一个哈希键值
	 * 
	 * @param string $key
	 * @param string $item 可选，如不设置则返回全部hash项目
	 * @return mixed
	 */
	public function hash_get($key,$item=false){
		if($item){
			$serialize_data = $this->redis->hGet($key,$item);
			$result=unserialize($serialize_data);
			if(!$result && $serialize_data){
				$result=$serialize_data;
			}
			$this->write_log("get hash cache {$item} in {$key} is " . $result);
			return $result;
		}else{
			return $this->hash_get_all($key);
		}
		
	}
	
	/**
	 * 设置一个哈希缓存键值
	 * 
	 * @param string $key
	 * @param string $item
	 * @param mix $value
	 * @param int $expirein 可选，生存期（秒）
	 */
	public function hash_set($key,$item,$value,$expirein=false){
		$value = serialize($value);
		$result=$this->redis->hSet($key,$item,$value);
		$this->write_log("set hash cache {$item} in {$key} to {$value} is " . (string) $result);
		if($expirein){
			$result=$this->redis->setTimeout($key,$expirein);
			$this->write_log("set hash cache {$key} expirein $expirein sec. is " . (string) $result);
		}
		return ;
	}
	
	/**
	 * int 自增自减
	 */
	public function hash_incr_by($key,$item,$value){
		$value = intval($value);
		$result = $this->redis->hIncrBy($key,$item,$value);
		$this->write_log("set hash cache {$item} in {$key} to increment {$value}");
		return ;
	}
		
	/**
	 * 设置一个缓存键值
	 * 
	 * @param string $key
	 * @param mix $value
	 */
	public function set($key,$value){
		$value=serialize($value);
		$result=$this->redis->set($key,$value);
		$this->write_log("set cache {$key} to {$value} is " . (string) $result);
		return $result;
	}
	
	/**
	 * 设置一个带过期时间的缓存键值
	 * 
	 * @param string $key
	 * @param int $expirein 有效期时长（秒）
	 * @param string $value
	 * @return bool
	 */
	public function setex($key,$expirein,$value){
		$value=serialize($value);
		$result=$this->redis->setex($key,$expirein,$value);
		$this->write_log("set cache {$key} to {$value} in expire {$expirein} sec is " . (string) $result);
		return $result;
	}
	
	/**
	 * 删除一个缓存键值
	 * 
	 * @param unknown $key
	 */
	public function del($key){
		$result=$this->redis->delete($key);
		$this->write_log("delete cache {$key} is " . (string) $result);
		return $result;
	}
	
	/**
	 * 删除一个哈希键值
	 * 
	 * @param string $key
	 * @param string $item
	 * 
	 */
	public function hash_del($key,$item){
		$result=$this->redis->hDel($key,$item);
		$this->write_log("delete hash cache {$item} in {$key} is " . (string) $result);
		return $result;
	}
	
	/**
	 * 取出一个缓存键值
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function get($key){
		$serialize_data=$this->redis->get($key);
		$result=unserialize($serialize_data);
		$this->write_log("get cache {$key} is " . $result);
		return $result;
	}

    /**
     * 返回匹配键值的数组
     *
     * @author Sun Wei <sunwei@ezlink.us>
     * @since 2014-11-13
     */
	public function keys($key_string = '*') {
		$result = $this->redis->keys($key_string);
		return $result;
	}

    /**
     * API 命中
     *
     * @author Sun Wei <sunwei@ezlink.us>
     * @since 2014-11-13
     */
    public function api_hit($class_string='', $function_string='') {
        $key = 'requestnum.'.$class_string.'.'.$function_string;
        if ($this->exists($key)) {
            $num = $this->get($key);
        } else {
            $num = 0;
        }
        $this->set($key, $num+1);
    }

    /**
     * cache 命中
     *
     * @author Sun Wei <sunwei@ezlink.us>
     * @since 2014-11-13
     */
    public function cache_hit($class_string='', $function_string='') {
        $key = 'getcachenum.'.$class_string.'.'.$function_string;
        if ($this->exists($key)) {
            $num = $this->get($key);
        } else {
            $num = 0;
        }
        $this->set($key, $num+1);
    }
	
	private function write_log($str,$level="info"){		
		if(is_object($this->log)){
			$str=addslashes($str);
			/*
			$cmd_str="\$this->log->{$level}(\"{$str}\");";
			eval($cmd_str);
			*/
			$this->log->$level($str);
		}
	}
	
	private function get_redis_connect($redis_host,$redis_port){
		$this->redis=new Redis();
		$this->redis->pconnect($redis_host,$redis_port);
		if(!$this->redis){
			echo "Redis Fail";
			die;
		}
	}
}