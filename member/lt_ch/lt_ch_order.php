<?php
session_start();
header("Expires: Mon, 26 Jul 1970 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
include_once "../../app/member/utils/login_check.php";
include_once "../../app/member/utils/error_handle.php";
include_once "../../app/member/utils/convert_name.php";
include_once "../../app/member/utils/time_util.php";

include_once "../../app/member/class/six_lottery_odds.php";
include_once "../../app/member/class/six_lottery_order.php";
include_once "../../app/member/class/six_lottery_schedule.php";
include_once "../../app/member/class/user_group.php";
$C_Patch=$_SERVER['DOCUMENT_ROOT'];
include_once($C_Patch."/app/member/cache/ltConfig.php");
include_once($C_Patch."/member/lt/lt_year_change.php");

include_once "../../member/lt/lt_util.php";

$odds_CH = six_lottery_odds::getOdds("CH");

$rType = $_POST["rtype"];

$ofTouch = $_POST["OfTouch"];
$ofTouch2 = $_POST["OfTouch2"];
$ofTouch3 = $_POST["OfTouch3"];
$ofTouch5 = $_POST["OfTouch5"];

if($rType=="CH_4"){
    $ch_name = "四全中";
    $odds_string = $odds_CH["h1"];
    $minChk = 4;
}elseif($rType=="CH_3"){
    $ch_name = "三全中";
    $odds_string = $odds_CH["h2"];
    $minChk = 3;
}elseif($rType=="CH_32"){
    $ch_name = "三中二";
    $odds_string = "<br />中二 ".$odds_CH["h3"]."<br />中三 ".$odds_CH["h4"];
    $minChk = 3;
}elseif($rType=="CH_2"){
    $ch_name = "二全中";
    $odds_string = $odds_CH["h5"];
    $minChk = 2;
}elseif($rType=="CH_2S"){
    $ch_name = "二中特";
    $odds_string = "<br />中特 ".$odds_CH["h6"]."<br />中二 ".$odds_CH["h7"];
    $minChk = 2;
}elseif($rType=="CH_2SP"){
    $ch_name = "特串";
    $odds_string = $odds_CH["h8"];
    $minChk = 2;
}

$totalArray = array();

if($ofTouch || $ofTouch2 || $ofTouch3 || $ofTouch5){
    $numArray = array();
    if($ofTouch || $ofTouch2){
        if($ofTouch){
            $selectArray = $_POST["spa"];
        }elseif($ofTouch2){
            $selectArray = $_POST["nf"];
        }

        $a1 = explode(", ",$selectArray[0]);
        $a2 = explode(", ",$selectArray[1]);

        for ($i2 = 0; $i2 < count($a1); $i2++) {
            $numArray[] = $a1[$i2];
        }
        for ($i3 = 0; $i3 < count($a2); $i3++) {
            $numArray[] = $a2[$i3];
        }
        for ($i2 = 0; $i2 < count($a1); $i2++) {
            for ($i3 = 0; $i3 < count($a2); $i3++) {
                $totalArray[] = $a1[$i2].", ".$a2[$i3];
            }
        }
    }elseif($ofTouch3){
        $x = $_POST["X"];
        $f = $_POST["F"];

        $fArray = getFArray();
        $a11 = $xArray[$x];
        $a22 = $fArray[$f];
        $a1 = $xArray[$x];
        $a2 = $fArray[$f];

        for ($i2 = 0; $i2 < count($a11); $i2++) {
            for ($i3 = 0; $i3 < count($a22); $i3++) {
                if ($a11[$i2] == $a22[$i3]) {
                    array_splice($a2, $i3, 1);
                }
            }
        }
        for ($i2 = 0; $i2 < count($a1); $i2++) {
            $numArray[] = $a1[$i2];
        }
        for ($i3 = 0; $i3 < count($a2); $i3++) {
            $numArray[] = $a2[$i3];
        }
        for ($i2 = 0; $i2 < count($a1); $i2++) {
            for ($i3 = 0; $i3 < count($a2); $i3++) {
                $totalArray[] = $a1[$i2].", ".$a2[$i3];
            }
        }
    }elseif($ofTouch5){
        $a1 = $_POST["Dantuo1"];
        $a2 = $_POST["Dantuo2"];

        for ($i2 = 0; $i2 < count($a1); $i2++) {
            $numArray[] = $a1[$i2];
        }
        for ($i3 = 0; $i3 < count($a2); $i3++) {
            $numArray[] = $a2[$i3];
        }

        if($minChk == 2){
            for ($i2 = 0; $i2 < count($a1); $i2++) {
                for ($i3 = 0; $i3 < count($a2); $i3++) {
                    $totalArray[] = $a1[$i2].", ".$a2[$i3];
                }
            }
        }elseif($minChk == 3){
            if (count($a1) == 1) {
                $minChk_sub = 2;
                $totalArray_21 = array();
                //初始化第一个数据
                $tmp2Array = array();
                for ($n = 0; $n < $minChk_sub; $n++) {
                    $tmp2Array[] = $a2[$n];
                }
                $totalArray_21[] = $tmp2Array;
                if (count($a2) > $minChk_sub) {
                    $totalSelectArray_21 = compile_array(count($a2), $minChk_sub);
                    //获取剩下组合
                    for ($j = 0; $j < count($totalSelectArray_21); $j++) {
                        $subArray_21 = array();
                        for ($k = 0; $k < $minChk_sub; $k++) {
                            $subArray_21[] = $a2[$totalSelectArray_21[$j][$k] - 1];
                        }
                        $totalArray_21[] = $subArray_21;
                    }
                }
                for ($i2 = 0; $i2 < count($totalArray_21); $i2++) {
                    $totalArray[] = $a1[0].", ".$totalArray_21[$i2][0].", ".$totalArray_21[$i2][1];
                }
            } elseif (count($a1) == 2) {
                for ($i3 = 0; $i3 < count($a2); $i3++) {
                    $totalArray[] = $a1[0].", ".$a1[1].", ".$a2[$i3];
                }
            }
        }elseif($minChk == 4){
            if (count($a1) == 1 || count($a1) == 2) {
                if(count($a1) == 1){
                    $minChk_sub = 3;
                }elseif(count($a1) == 2){
                    $minChk_sub = 2;
                }
                $totalArray_21 = array();
                //初始化第一个数据
                $tmp2Array = array();
                for ($n = 0; $n < $minChk_sub; $n++) {
                    $tmp2Array[] = $a2[$n];
                }
                $totalArray_21[] = $tmp2Array;
                if (count($a2) > $minChk_sub) {
                    $totalSelectArray_21 = compile_array(count($a2), $minChk_sub);
                    //获取剩下组合
                    for ($j = 0; $j < count($totalSelectArray_21); $j++) {
                        $subArray_21 = array();
                        for ($k = 0; $k < $minChk_sub; $k++) {
                            $subArray_21[] = $a2[$totalSelectArray_21[$j][$k] - 1];
                        }
                        $totalArray_21[] = $subArray_21;
                    }
                }
                if(count($a1) == 1){
                    for ($i2 = 0; $i2 < count($totalArray_21); $i2++) {
                        $totalArray[] = $a1[0].", ".$totalArray_21[$i2][0].", ".$totalArray_21[$i2][1].", ".$totalArray_21[$i2][2];
                    }
                }elseif(count($a1) == 2){
                    for ($i2 = 0; $i2 < count($totalArray_21); $i2++) {
                        $totalArray[] = $a1[0].", ". $a1[1].", ".$totalArray_21[$i2][0].", ".$totalArray_21[$i2][1];
                    }
                }
            } elseif (count($a1) == 3) {
                for ($i3 = 0; $i3 < count($a2); $i3++) {
                    $totalArray[] = $a1[0].", ".$a1[1].", ".$a1[2].", ".$a2[$i3];
                }
            }
        }
    }
}else{//正常号码
    $numArray = $_POST["num"];
    $tmp2Array = array();
    for($i=0;$i<$minChk;$i++){
        $tmp2Array[] = $numArray[$i];
    }
    $totalArray[] = implode(", ", $tmp2Array);

    if(count($numArray) > $minChk){
        $totalSelectArray = compile_array(count($numArray), $minChk);

        //获取剩下组合
        for($j=0;$j<count($totalSelectArray);$j++){
            $subArray = array();
            for($k=0;$k<$minChk;$k++){
                $subArray[] = $numArray[$totalSelectArray[$j][$k]-1];
            }
            $totalArray[] = implode(", ",$subArray);
        }
    }
}

foreach($totalArray as $key => $value){
    $postInfo .= '
    \'<input type=\"hidden\" name=\"totalArray[]\" value=\"'.$value.'\" />\n\'+
    ';
}
foreach($numArray as $key => $value){
    if($key==5 && count($numArray)>6){
        $betInfo .= $value.',<br />';
    }else{
        $betInfo .= $value.',';
    }
}
$betInfo = substr($betInfo,0,-1);


$page = '\'\'+
\'<div class=\"inner\">\n\'+
\'<div class=\"msg-title\">六合彩 连码 下注单</div>\n\'+
\'<div class=\"msg-text\">\n\'+
\'<form name=\"LAYOUTFORM\" action=\"/member/Grp/grpOrder.php\" method=\"post\" onsubmit=\"return false\">\n\'+
\'<div class=\"PlayType\">\n\'+
\'<span class=\"rr\">期数 : '.$qishu.'</span> &nbsp;\n\'+
\'<span style=\"color:white;background-color:#333;padding:0px 3px 0px 3px;\"> '.$ch_name.'</span> @\'+
\'<b class=\"OddsL\">'.$odds_string.' </b> <br />\n '.$betInfo.'<br />组合共 <font id=\"TotalBall\" color=\"red\">'.count($totalArray).'</font> 组\n\'+
\'</div>\n          <br />下注金额:\n\'+
\'<input type=\"text\" pattern=\"[0-9]*\" min=\"0\" id=\"gold\" name=\"gold\" class=\"OrderGold\"  /><br />\n\'+
\'<div style=\"display: none;\">\n          可赢金额:\n          <b id=\"pc\">0.00</b><br />\n          </div>\n\'+
\'最低限额: '.$lowestMoney.'<br />\n          \'+
\'最高限额: '.$maxMoney.'<br />\n          <br />\n          \n\'+
\'<div style=\"padding-left: 20px\">\n\'+
\'<input type=\"button\" name=\"btnCancel\" value=\"取消\" class=\"cancel_cen\" />\n            &nbsp;&nbsp;\n\'+
\'<input type=\"button\" name=\"btnSubmit\" value=\"确定\" class=\"submit_cen\" />\n          </div>\n\'+
\'<input type=\"hidden\" name=\"gid\" value=\"CH\" />\n\'+
\'<input type=\"hidden\" name=\"total_count\" value=\"'.count($totalArray).'\" />\n\'+
\'<input type=\"hidden\" name=\"ch_name\" value=\"'.$ch_name.'\" />\n\'+

'.$postInfo.'

 \'</form>\n      </div>\n    </div>\n    <div class=\"footer\"></div>\n\'';

if($_GET['cl']='wap'){

	echo '
var Left = document.getElementById("message_box");
Left.innerHTML = '.$page.';
Left.style.display = "";
var betO = betSpace.bet.instance();
betO.clientType="wap";

//派彩有1000000限制
betO.millionLimit = true;
betO.init("'.$lowestMoney.'", "'.$maxMoney.'", "9999999", "9999999", "'.$userMoney.'", "");
';





}else{
echo '
document.getElementById("bet-credit").innerHTML = "'.$userMoney.'";
var Left = document.getElementById("message_box");
Left.innerHTML = '.$page.';
Left.style.display = "";
var betO = betSpace.bet.instance();

//派彩有1000000限制
betO.millionLimit = true;
betO.init("'.$lowestMoney.'", "'.$maxMoney.'", "9999999", "9999999", "'.$userMoney.'", "");
';
}

function getFArray(){
    $fArray = array();
    $fArray["0"] = array("10","20","30","40");
    $fArray["1"] = array("01","11","21","31","41");
    $fArray["2"] = array("02","12","22","32","42");
    $fArray["3"] = array("03","13","23","33","43");
    $fArray["4"] = array("04","14","24","34","44");
    $fArray["5"] = array("05","15","25","35","45");
    $fArray["6"] = array("06","16","26","36","46");
    $fArray["7"] = array("07","17","27","37","47");
    $fArray["8"] = array("08","18","28","38","48");
    $fArray["9"] = array("09","19","29","39","49");
    return $fArray;
}