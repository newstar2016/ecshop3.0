<?php

/**
 * ECSHOP 会员中心
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: user.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
require_once(ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_o2o.php');
defined('EBusinessID') or define('EBusinessID', '1256465');
//电商加密私钥，快递鸟提供，注意保管，不要泄漏
defined('AppKey') or define('AppKey', '3e9fc3eb-e01c-49e1-8bae-c1a757433df6');
//请求url
defined('ReqURL') or define('ReqURL', 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx');
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'order_tracking';
$companyName=$_REQUEST["com"]; //保存快递公司的名称
$typeCom = $_REQUEST["com"]; //快递公司
$typeNu = $_REQUEST["nu"]; //运单号码

if ($action == 'order_tracking')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'includes/lib_payment.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    include_once(dirname(ROOT_PATH) . '/plugins/kdniao/company.php');

    $result = array('status' => 0, 'message' => '','data'=>'');
    if (isset ($typeCom) && isset ($typeNu)) {
        $logisticResult = "";
        $i = 3;
        do {
            $logisticResult = getOrderTracesByJson($typeCom, $typeNu);
            $i--;
        } while ((null == $logisticResult || "" == $logisticResult) && $i > 0);

        //write_log("运单【".$typeNu."，".$typeCom."】查询结果：".$logisticResult,'kuaidi',0);
        if (null == $logisticResult || "" == $logisticResult) { //无返回结果
            $result['message'] = '物流信息查询失败，请稍后重试';
        } else {
            //解析返回json
            $objjson = json_decode($logisticResult, true);
            $succ = $objjson["Success"];
            if (true === $succ) {
                $objList = $objjson["Traces"];
                if(empty($objList)){
                    $result['message']=$objjson["Reason"];
                }else{
                    $result['state']=1;
                    rsort($objList);
                    $result['data']=$objList;
                }

            } else {
                $result['message'] = '暂无物流信息';
            }
        }
    }else{
        $result['message'] = '请输入物流公司和物流运单号';
    }
}
$smarty->assign('expressName',$companyName);
$smarty->assign('expressNo',$typeNu);
$smarty->assign('result',$result);
$smarty->display('order_trace.dwt');

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


//页面调用方式
//			<a class="order_tracking" href="order_trace.php?act=order_tracking&order_id={$order.order_id}&com={$dsmerList.shipping_name}&nu={$dsmerList.invoice_no}">查看物流</a>
?>