<?php

/**
 * 注意：这是一个配置文件的例子
 * 实际部署时，应该将本文件名字中间的 sample 字样去掉，如改为 custom-config.php
 * 
 */




define("WECHAT_TOKEN", "0926970a8dade655a801ed97a6390e87");

define("WECHAT_APPID", "wxd88538be061d285d");
define("WECHAT_APPSECRET", "42cdb60997f66c15c79f4208ad77dfee");

$weichat_menu_struct = "{
  			   \"button\":[
  			   {
          			\"type\":\"click\",
          			\"name\":\"寻觅热点\",
          			\"key\":\"FIND_WIFI\"
      			},
      			{
           			\"type\":\"click\",
           			\"name\":\"分享热点\",
           			\"key\":\"SHARE_WIFI\"
      			},
      			{
           			\"type\":\"click\",
           			\"name\":\"我的积分\",
           			\"key\":\"MY_POINT\"
      			}]
 			}";

/*
$weichat_menu_struct = "{
  			   \"button\":[
  			   {
          			\"type\":\"click\",
          			\"name\":\"寻觅热点\",
          			\"key\":\"FIND_WIFI\"
      			},
      			{
           			\"type\":\"click\",
           			\"name\":\"分享热点\",
           			\"key\":\"SHARE_WIFI\"
      			},
      		{
           		\"name\":\"更多\",
           		\"sub_button\":[
            	{
               		\"type\":\"click\",
               		\"name\":\"个人信息\",
               		\"key\":\"MY\"
            	},
            	{
               		\"type\":\"click\",
               		\"name\":\"使用说明\",
               		\"key\":\"README\"
            	},
            	{
               		\"type\":\"click\",
               		\"name\":\"关于我们\",
               		\"key\":\"ABOUT\"
            	},
				]
       		}]
 		}";
 		
*/

define("WECHAT_MENU_STRUCT",$weichat_menu_struct);

