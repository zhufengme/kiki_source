<?php
namespace models;

if (!PFW_INIT) {
    echo "break in";
    die;
}

class service extends models {

    function __construct() {
        parent::__construct();
        return;
    }

    /**
     * 创建一条短信
     *
     * @param string $recipient 收信人手机号
     * @param string $content 短信内容
     * @param int $schedule_time 计划发出时间，默认0立即发送
     *
     * @return int sms_id短信唯一编号
     */

    public function create_sms($recipient, $content, $schedule_time = 0) {
        if (\helper::is_mobile($recipient) && !\helper::is_international_mobile($recipient)) {
            $recipient = "+86" . $recipient;
        }

        $content = mb_substr($content, 0, 70);

        $this->db->query("insert into t_sms_queue (recipient,content,create_time,schedule_time) values ('$recipient','$content',{$this->timestamp},$schedule_time)");
        $sms_id = $this->db->insert_id;

        if ($schedule_time == 0) {
            $sms_request = array(
                'recipient' => $recipient,
                'content' => $content,
                'sms_id' => $sms_id,
            );
            $this->cache->queue_l_push("DOMY_SMS_QUEUE", $sms_request);
        }

        return $sms_id;

    }


    /**
     * 获取GTAN测试用数据
     *
     * @param string $SN 设备序列号
     *
     * @return array 一个数组，其中包含需返回的数据
     */
    public function get_gtan_testing_data($SN) {
        $row = $this->db->get_row("select * from t_gtan_testing_data where SN='$SN'");
        if (!$row) {
            return false;
        }
        $result = array(
            'METHOD' => $row->method,
            'PPPOE_USERNAME' => $row->pppoe_username,
            'PPPOE_PASSWORD' => $row->pppoe_password,
        );
        return $result;
    }

    /**
     * 检查缓存系统状态
     *
     * @return bool 状态，true-正常,false-异常
     */
    public function check_cache_status() {
        $this->cache->set("_test", 1);
        $t = $this->cache->get("_test");
        if (!$t) {
            return false;
        }
        return true;
    }

    /**
     * 检查文件系统权限
     *
     * @return bool 状态，true-正常,false-异常
     */
    public function check_file_access() {
        $testfile = PFW_CACHE_PATH . "/.test." . rand(1, 9999);
        $t = file_put_contents($testfile, "1");
        if (!$t) {
            return false;
        }
        unlink($testfile);
        return true;
    }

    /**
     * 检查数据库连接状态
     *
     * @return bool 状态，true-正常,false-异常
     */
    public function check_db_status() {
        $link = mysql_connect(PFW_DB_HOST, PFW_DB_USERNAME, PFW_DB_PASSWORD, true);
        if (!$link) {
            return false;
        }
        mysql_close($link);
        return true;
    }

    /**
     * 根据错误码返回错误信息
     *
     * @param string $error_code 错误码
     * @param string $addin_msg 附加信息
     */
    public function get_error_info_by_code($error_code, $addin_msg = false) {
        $result = false;
        $cache_key = "error_code_" . $error_code;
        $result_json = $this->cache->get($cache_key);
        if ($result_json) {
            $result = json_decode($result_json, true);
        } else {
            $result = $this->db->get_row("select error_code,error_msg,http_code from t_api_error_codes where error_code='$error_code'", ARRAY_A);
            if (!$result) {
                $result = array(
                    'error_code' => '999999',
                    'error_msg' => 'error code not found : ' . $error_code,
                    'http_code' => '404',
                );
                return $result;
            } else {
                $this->cache->setex($cache_key, 28800, json_encode($result));
            }
        }

        if ($addin_msg) {
            $result['error_msg'] = $result['error_msg'] . " : " . htmlspecialchars($addin_msg);
        }
        return $result;
    }

    /**
     * 检查是否有新版本
     *
     * @param string $version 版本号串
     *
     */
    public function check_new_version($version, $hd_type) {
        $arr = explode('.', trim($version));
        //$sql ="select * from t_update_v where v1>".intval($arr[0])." or v2>".intval($arr[1])." or v3>".intval($arr[2]);
        $sql = "select v1,v2,v3 from t_update_v where v1>=" . intval($arr[0]);
        $sql .= "  and FIND_IN_SET('" . $hd_type . "',hd_type)";//兼容多个版本
        //$sql.=" and hd_type='{$hd_type}' ";
        //加已经发布的正式版本条件
        $sql .= " and status=1";
        $res = $this->db->get_results($sql, ARRAY_A);
        rsort($res);
        if (!$res) {
            return false;
        }
        $new_v = false;
        if ($res[0]['v1'] > $arr[0]) {
            $new_v = true;
        } else if ($res[0]['v2'] > $arr[1]) {
            $new_v = true;
        } else if ($res[0]['v3'] > $arr[2]) {
            $new_v = true;
        } else {
            $new_v = false;
        }
        if (!$new_v) {
            return false;
        }
        return $res[0];
    }

    /**
     * 获取版本的信息
     *
     * @param string $v1 固件版本号
     * @param string $v2 覆盖版本号
     * @param string $v3 增量版本号
     * @return array
     */
    public function get_version_session($v1, $v2, $v3) {
        $sql = "select * from t_update_v where v1=" . intval($v1) . " and v2=" . intval($v2) . " and v3=" . intval($v3);
        return $this->db->get_row($sql, ARRAY_A);
    }

    /**
     * 增加    更新版本的会话
     *
     * @param string $token 会话标记
     * @param string $version 版本号串
     * @param int $size 文件大小（字节）
     * @param string $path 文件路径
     * @param string $SN 设备编号
     *
     * @return boolean
     *
     */
    public function add_version_session($token, $version, $size, $path, $SN) {
        $expire = time() + 20 * 60;
        $sql = "insert into t_update_vs(`token`,`version`,`file_size`,`file_path`,`SN`,`expire`) values('{$token}','{$version}',{$size},'{$path}','{$SN}',{$expire})";
        $res = $this->db->query($sql);
        if (!$res) {
            return false;
        }
        return true;
    }

    /**
     * 检查 更新版本的 token
     *
     * @param string $token 会话的标记
     * @param string $version 跟新的版本号
     */
    public function check_update_token($token, $version) {
        $res = $this->db->get_row("select * from t_update_vs where `token`='{$token}' and `version`='{$version}' ", ARRAY_A);
        if (!$res) {
            return false;
        }
        return $res;
    }

    /**
     * 根据设备SN编号 获取设备的信息
     * @param string $SN 设备编号
     */
    public function get_device_row($SN) {
        $res = $this->db->get_row("select * from t_devices_list where SN='{$SN}' ", ARRAY_A);
        if (!$res) {
            return false;
        }
        return $res;
    }
}
	