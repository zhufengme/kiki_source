<?php 
/**
 * 感知类
 *
 * @author Sun Wei <sunwei@ezlink.us>
 * @since 2014-11-11
 */
namespace models;

if(!PFW_INIT){
	echo "break in";
	die;
}

class perception extends models {

	protected $SN = false;
	
	function __construct($SN){
		parent::__construct();
		$this->SN = $SN;
	}

    /**
     * 用户感知接口
     *
     * @author Sun Wei <sunwei@ezlink.us>
     * @since 2014-11-11
     */
	public function ping_perception($detail_array) {
        $this->cache->api_hit('perception', __FUNCTION__);

        if (!empty($detail_array) && is_array($detail_array)) {
            $time = time();

            $TABLE_NAME = "t_devices_perception_".$this->SN;
            $sql = "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_NAME='".$TABLE_NAME."'";
            $rs = $this->db->get_var($sql);

            if ($rs != 1) {
                $sql = "CREATE TABLE IF NOT EXISTS `".$TABLE_NAME."` (
                    `dpid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                    `SN` char(16) COLLATE utf8_unicode_ci NOT NULL COMMENT '路由器序列号',
                    `FACTORY_NAME` varchar(80) COLLATE utf8_unicode_ci NOT NULL COMMENT '厂商OUI名称',
                    `MAC` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '联网设备MAC地址',
                    `NAT_IP` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '本地内网IP地址',
                    `WIRED` tinyint(2) NOT NULL DEFAULT '0' COMMENT '有线设备标记',
                    `DSIGNAL` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT '信号强度',
                    `SPEED` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT '连接速率',
                    `UPLOAD_BD` int(11) NOT NULL COMMENT '瞬时上行流量',
                    `DOWNLOAD_BD` int(11) NOT NULL COMMENT '瞬时下行流量',
                    `TIME` int(11) NOT NULL COMMENT '时间戳',
                    PRIMARY KEY (`dpid`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='设备感知表' AUTO_INCREMENT=1";
                $this->db->query($sql);
            }

            $sql = "INSERT INTO `".$TABLE_NAME."` (`dpid`, 
                `SN`, `FACTORY_NAME`, `MAC`, 
                `NAT_IP`, `WIRED`, `DSIGNAL`, `SPEED`, 
                `UPLOAD_BD`, `DOWNLOAD_BD`, `TIME`) VALUES ";

            foreach ($detail_array as $value) {
                    $sql .= "(NULL, 
                    '{$this->SN}', '{$value['FACTORY_NAME']}', '{$value['MAC']}', 
                    '{$value['NAT_IP']}', '{$value['WIRED']}', '{$value['DSIGNAL']}', '{$value['SPEED']}', 
                    '{$value['UPLOAD_BD']}', '{$value['DOWNLOAD_BD']}', '{$time}'),";
            }
            $sql = rtrim($sql, ',');
            $rs = $this->db->query($sql);
        }

	}









}
