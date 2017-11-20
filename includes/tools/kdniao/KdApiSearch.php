<?php

/**
 *
 * 快递鸟物流轨迹即时查询接口
 * @author xlhu
 * @version 1.0.0
 * 2016-03-24 19:03:00
 * ID:1237100
 * KEY:518a73d8-1f7f-441a-b644-33e77b49d846
 */

//电商ID
define('IN_ECS', true);
require ('../../includes/init.php');
defined('EBusinessID') or define('EBusinessID', '1256465');
//电商加密私钥，快递鸟提供，注意保管，不要泄漏
defined('AppKey') or define('AppKey', '3e9fc3eb-e01c-49e1-8bae-c1a757433df6');
//请求url
defined('ReqURL') or define('ReqURL', 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx');

//调用获取物流轨迹
//-------------------------------------------------------------
$typeCom = $_GET["com"]; //快递公司
$typeNu = $_GET["nu"]; //运单号码
include_once ("company.php");
if (isset ($typeCom) && isset ($typeNu)) {
	$logisticResult = "";
	$i = 3;
	do {
		$logisticResult = getOrderTracesByJson($typeCom, $typeNu);
		$i--;
	} while ((null == $logisticResult || "" == $logisticResult) && $i > 0);
	
	log_kuaidi("运单【".$typeNu."，".$typeCom."】查询结果：".$logisticResult);
	
	if (null == $logisticResult || "" == $logisticResult) { //无返回结果
		echo '物流信息查询失败，请稍后重试';
	} else {
		//解析返回json
		$objjson = json_decode($logisticResult, true);
		$succ = $objjson["Success"];
		if (true === $succ) {
			$objList = $objjson["Traces"];
			//二维数组 根据AcceptTime重新升序排序
			//$objList = my_sort($objList,'AcceptTime',SORT_ASC,SORT_STRING);  
			$newArr = array ();
			for ($j = 0; $j < count($objList); $j++) {
				$newArr[] = $arr[$j]['AcceptTime'];
			}
			array_multisort($newArr, $objList);
			$content = "";
			$length = count($objList);
			$i = 0;
			foreach ($objList AS $obj) {
				$i++;
				if ($i != $length) {
					$content = $content . "<span>" . $obj["AcceptTime"] . "</span><span>&nbsp;&nbsp;<img style='width:18px;height:18px' src=\"images/hui.gif\"/>&nbsp;&nbsp;</span><span>" . $obj["AcceptStation"] . "</span></br>";
				} else {
					$content = $content . "<span>" . $obj["AcceptTime"] . "</span><span>&nbsp;&nbsp;<img style='width:18px;height:18px' src=\"images/lv.gif\"/>&nbsp;&nbsp;</span><span>" . $obj["AcceptStation"] . "</span></br>";
				}
			}
			print_r($content);
		} else {
			echo '暂无物流信息';
		}
	}
} else {
	echo '物流信息查询失败，请稍后重试';
}
exit ();

function log_kuaidi($msg) {
	error_log("[" . local_date('Y-m-d H:i:s', time()) . "] " . $msg . "\r\n", 3, '../logkuaidi.log');
}

//-------------------------------------------------------------

/**
 * Json方式 查询订单物流轨迹
 * 
 * 
 * 没有物流轨迹的
{
    "EBusinessID": "1109259",
    "Traces": [],
    "OrderCode": "",
    "ShipperCode": "SF",
    "LogisticCode": "118461988807",
    "Success": false,
    "Reason": null
}
 * 
 * 
 * 
 * 有物流轨迹的
{
    "EBusinessID": "1109259",
    "OrderCode": "",
    "ShipperCode": "SF",
    "LogisticCode": "118461988807",
    "Success": true,
    "State": 3,
    "Reason": null,
    "Traces": [
        {
            "AcceptTime": "2014/06/25 08:05:37",
            "AcceptStation": "正在派件..(派件人:邓裕富,电话:18718866310)[深圳 市]",
            "Remark": null
        },
        {
            "AcceptTime": "2014/06/25 04:01:28",
            "AcceptStation": "快件在 深圳集散中心 ,准备送往下一站 深圳 [深圳市]",
            "Remark": null
        },
        {
            "AcceptTime": "2014/06/25 01:41:06",
            "AcceptStation": "快件在 深圳集散中心 [深圳市]",
            "Remark": null
        },
        {
            "AcceptTime": "2014/06/24 20:18:58",
            "AcceptStation": "已收件[深圳市]",
            "Remark": null
        },
        {
            "AcceptTime": "2014/06/24 20:55:28",
            "AcceptStation": "快件在 深圳 ,准备送往下一站 深圳集散中心 [深圳市]",
            "Remark": null
        },
        {
            "AcceptTime": "2014/06/25 10:23:03",
            "AcceptStation": "派件已签收[深圳市]",
            "Remark": null
        },
        {
            "AcceptTime": "2014/06/25 10:23:03",
            "AcceptStation": "签收人是：已签收[深圳市]",
            "Remark": null
        }
    ]
}
 * 
 */
function getOrderTracesByJson($shipperCode, $logisticCode) {
	$requestData = "{\"OrderCode\":\"\",\"ShipperCode\":\"" . $shipperCode . "\",\"LogisticCode\":\"" . $logisticCode . "\"}";
	$datas = array (
		'EBusinessID' => EBusinessID,
		'RequestType' => '1002',
		'RequestData' => urlencode($requestData),
		'DataType' => '2',
		
	);
	$datas['DataSign'] = encrypt($requestData, AppKey);
	$result = sendPost(ReqURL, $datas);

	//根据公司业务处理返回的信息......

	return $result;
}

/**
 * XML方式 查询订单物流轨迹
 */
function getOrderTracesByXml() {
	$requestData = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>" .
	"<Content>" .
	"<OrderCode></OrderCode>" .
	"<ShipperCode>SF</ShipperCode>" .
	"<LogisticCode>589707398027</LogisticCode>" .
	"</Content>";

	$datas = array (
		'EBusinessID' => EBusinessID,
		'RequestType' => '1002',
		'RequestData' => urlencode($requestData),
		'DataType' => '1',
		
	);
	$datas['DataSign'] = encrypt($requestData, AppKey);
	$result = sendPost(ReqURL, $datas);

	//根据公司业务处理返回的信息......

	return $result;
}

/**
 *  post提交数据 
 * @param  string $url 请求Url
 * @param  array $datas 提交的数据 
 * @return url响应返回的html
 */
function sendPost($url, $datas) {
	$temps = array ();
	foreach ($datas as $key => $value) {
		$temps[] = sprintf('%s=%s', $key, $value);
	}
	$post_data = implode('&', $temps);
	$url_info = parse_url($url);
	$httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
	$httpheader .= "Host:" . $url_info['host'] . "\r\n";
	$httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
	$httpheader .= "Content-Length:" . strlen($post_data) . "\r\n";
	$httpheader .= "Connection:close\r\n\r\n";
	$httpheader .= $post_data;
	$fd = fsockopen($url_info['host'], 80);
	fwrite($fd, $httpheader);
	$gets = "";
	$headerFlag = true;
	while (!feof($fd)) {
		if (($header = @ fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
			break;
		}
	}
	while (!feof($fd)) {
		$gets .= fread($fd, 128);
	}
	fclose($fd);

	return $gets;
}

/**
 * 电商Sign签名生成
 * @param data 内容   
 * @param appkey Appkey
 * @return DataSign签名
 */
function encrypt($data, $appkey) {
	return urlencode(base64_encode(md5($data . $appkey)));
}
?>