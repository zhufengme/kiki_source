<?php
namespace controllers;

if(!PFW_INIT){
	echo "break in";
	die;
}

class testsw extends rest {

	public function test(){
		$array = array(
			"array_bool" => true,
			"array_number" => 222,
			'array_string' => "efg"
		);

		$result = array(
			'bool' => true,
			'number' => 111,
			'string' => "abc",
			'array' => $array
		);
		
		$t1 = $result;
		$t2 = self::json_mapper($result);
		$this->api_response($t1);
	}



	public function json_mapper($value, $recursive = true) {
		if (!empty($value) && is_string($value) && $decoded = json_decode($value, true)) {
			return $decoded;
		} elseif (is_array($value) && $recursive) {
			return array_map('self::json_mapper', $value);
		} else {
			return $value;
		}
	}

	public function json_mapper_norecurse($value) {
		return self::json_mapper($value, false);
	}

	public function json_to_array($array, $recursive = true) {
		if (!is_array($array)) {
			$array = array($array);
		}

		return array_map($recursive ? 'self::json_mapper' : 'self::json_mapper_norecurse', $array);
	}


}