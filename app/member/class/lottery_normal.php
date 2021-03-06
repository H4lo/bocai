<?php

class lottery_normal
{
    static function add_order($userid,$bet_money,$balance,$bet_win,$assets,
                              $lottery_name,$rtype_name,$rType,$gType,
                              $goldArray,$aOddsArray,$aConcedeArray,
                              $lottery_number,$bet_time){

        if(!count($goldArray)>0){
            error2("子订单没有内容-彩票4。");
            return false;
        }

        global $mysqli;
        //生成图片
        function str_leng($str){ //取字符串长度
            mb_internal_encoding("UTF-8");
            return mb_strlen($str)*12;
        }

        $sql	=	"insert into order_lottery(user_id,Gtype,rtype_str,rtype,bet_info,bet_money,win,lottery_number,bet_time)
                    values ('$userid','$gType','$rtype_name','$rType','bet_info','$bet_money','$bet_win','$lottery_number','$bet_time')"; //新增一个投注项
        $mysqli->query($sql);
        $q1		=	$mysqli->affected_rows;
        if($q1!=1){
            return false;
        }
        $id 	=	$mysqli->insert_id;
        $datereg=	date("YmdHis").$id;

        $sql		= 	"select money from user_list where user_id='$userid' limit 0,1";
        $query 		=	$mysqli->query($sql);
        $rs			=	$query->fetch_array();
        $assets = $rs["money"];
        $balance = $assets-$bet_money;

        $sql	=	"update user_list set money=$balance where money>=$bet_money and $balance>=0 and user_id='$userid'";//扣钱
        $mysqli->query($sql);
        $q3		=	$mysqli->affected_rows;
        if($q3!=1){
            $sql	=	"delete from order_lottery where id=$id";//操作失败，删除订单
            $mysqli->query($sql);
            return false;
        }

        $sql = "INSERT INTO `money_log` (`user_id`,`order_num`,`about`,`update_time`,`type`,`order_value`,`assets`,`balance`) VALUES ('$userid','$datereg','$lottery_name',now(),'彩票下注','$bet_money','$assets','$balance');";
        $mysqli->query($sql);
        $q8		=	$mysqli->affected_rows;
        $money_log_id		=	$mysqli->insert_id;
        if($q8!=1){
            $sql	=	"update user_list set money=money+$bet_money where user_id='$userid'";//操作失败，还原客户资金
            $mysqli->query($sql);
            return false;
        }

        $sql	=	"update `order_lottery` set `order_num`='$datereg' where id='$id'"; //更新订单号
        $mysqli->query($sql);
        $q2		=	$mysqli->affected_rows;
        if($q2!=1){
            $sql	=	"delete from order_lottery where id=$id";//操作失败，删除订单
            $mysqli->query($sql);
            $sql = "DROP TRIGGER BeforeDeleteUserList;";
            $mysqli->query($sql);
            $sql	=	"delete from money_log where id=$money_log_id";//操作失败，删除金钱记录
            $mysqli->query($sql);
            $sql = "
			CREATE TRIGGER `BeforeDeleteMoneyLog` BEFORE delete ON `money_log`
			  FOR EACH ROW BEGIN
				insert into money_log(id) values (old.id);
			  END;
			";
            $mysqli->query($sql);
            $sql	=	"update user_list set money=money+$bet_money where user_id='$userid'";//操作失败，还原客户资金
            $mysqli->query($sql);
            return false;
        }else{//成功后插入子表
            //查询出赔率
            if($rType=="535" || in_array($rtype_name, array("万仟定位","万佰定位","万拾定位","万个定位","仟佰定位","仟拾定位","仟个定位","佰拾定位","佰个定位","拾个定位"))){
                $odds1 = odds_lottery_normal::getOddsByPart($lottery_name,$rtype_name,"part1");
                $odds2 = odds_lottery_normal::getOddsByPart($lottery_name,$rtype_name,"part2");
            }else{
                $odds = odds_lottery_normal::getOdds($lottery_name,$rtype_name);
            }
            //获取反水
            $sql   =	"select g.* from user_group g,user_list u
                                where u.user_id='$userid' and g.group_id=u.group_id limit 0,1";
            $query = $mysqli->query($sql);
            $fsRow   =	$query->fetch_array();

            for($i=0;$i<sizeof($goldArray);$i++){
                if($goldArray[$i]){
                    $bet_money_one = $goldArray[$i];
                    if(in_array($gType,array("TJ","CQ","JX"))){
                        $number = str_replace($rType.'-',"",$aConcedeArray[$i]);   //替换无用信息
                    }elseif(in_array($gType,array("D3","P3","T3"))){
                        $number = str_replace($rType,"",$aConcedeArray[$i]);   //替换无用信息
                    }
                    //设置相应的赔率
                    if(in_array($rtype_name, array("万仟定位","万佰定位","万拾定位","万个定位","仟佰定位","仟拾定位","仟个定位","佰拾定位","佰个定位","拾个定位"))){
                        $bet_rate = $odds1["h".($i+0)];
                        if($i>=50){
                            $bet_rate = $odds2["h".($i-50)];
                        }
                    }elseif(in_array($rtype_name, array("组选三","组选六","一字过关","(前三)组选三","(中三)组选三","(后三)组选三","(前三)组选六","(中三)组选六","(后三)组选六"))){
                        $sizeSelect = count(explode("*",$aConcedeArray[$i]));
                        if($rType=="WP"){
                            if($sizeSelect==2){
                                $bet_rate = $odds["h18"];
                            }elseif($sizeSelect==3){
                                $bet_rate = $odds["h19"];
                            }
                        }else{
                            if(in_array($rType, array("GST","595","596","597"))){
                                $bet_rate = $odds["h".($sizeSelect-5)];
                            }
                            if(in_array($rType, array("GSS","598","599","600"))){
                                $bet_rate = $odds["h".($sizeSelect-4)];
                            }
                        }
                    }elseif($rType=="OEOU"){   //B3两面
                        $number = $aConcedeArray[$i];
                        $sequence = lottery_normal::getOeouLocationByCode($aConcedeArray[$i]);
                        $bet_rate = $odds["h".$sequence];
                    }elseif($rType=="535"){    //B5两面
                        $number = $aConcedeArray[$i];
                        $sequence = lottery_normal::get535ByCode($aConcedeArray[$i]);
                        $bet_rate = $odds1["h".$sequence];
                        if($sequence>=54){
                            $bet_rate = $odds2["h".($sequence-54)];
                        }
                    }else{
                        $bet_rate = $odds["h".($i+0)];
                    }
                    $win_money = $bet_rate * $bet_money_one;
                    if($bet_money_one >= $fsRow[strtolower($gType).'_bet']){
                        $fs_money = $bet_money_one*$fsRow[strtolower($gType).'_bet_reb'];
                    }

                    $sql	=	"insert into order_lottery_sub (order_num,number,bet_rate,bet_money,win,fs,balance)
                                 value ('$datereg','$number','$bet_rate','$bet_money_one','$win_money','$fs_money','$balance')";
                    $mysqli->query($sql);
                    $q4		=	$mysqli->affected_rows;
                    $id_sub 	=	$mysqli->insert_id;
                    $datereg_sub=	date("YmdHis").$id_sub;

                    $sql	=	"update `order_lottery_sub` set `order_sub_num`='$datereg_sub' where id='$id_sub'"; //更新订单号
                    $mysqli->query($sql);
                    $q2		=	$mysqli->affected_rows;

                    if($q4!=1 || $q2!=1){
                        $sql	=	"delete from order_lottery_sub where order_num='$datereg'";//操作失败，删除子订单
                        $mysqli->query($sql);
                        $sql	=	"delete from order_lottery where id=$id";//操作失败，删除订单
                        $mysqli->query($sql);
                        $sql = "DROP TRIGGER BeforeDeleteUserList;";
                        $mysqli->query($sql);
                        $sql	=	"delete from money_log where id=$money_log_id";//操作失败，删除金钱记录
                        $mysqli->query($sql);
                        $sql = "
                            CREATE TRIGGER `BeforeDeleteMoneyLog` BEFORE delete ON `money_log`
                              FOR EACH ROW BEGIN
                                insert into money_log(id) values (old.id);
                              END;
                            ";
                        $mysqli->query($sql);
                        $sql	=	"update user_list set money=money+$bet_money where user_id='$userid'";//操作失败，还原客户资金
                        $mysqli->query($sql);
                        return false;
                    }else{
                        $C_Patch=$_SERVER['DOCUMENT_ROOT'];
                        include_once($C_Patch."/app/member/utils/convert_name.php");
                        include_once($C_Patch."/resource/lottery/getContentName.php");
                        $tm=date("Y-m-d H:i:s",time());
                        $height	=	26; //高
                        $gTypeZhName = getZhPageTitle($gType);
                        $betInfoIm = getName($number,$gType);
                        $width	=	str_leng($gTypeZhName.'='.$lottery_number.'='.$rtype_name.'='.$betInfoIm.'='.$bet_money.'='.$fs_money.'='.$bet_rate.'='.$tm); //宽
                        $im		=	imagecreate($width,$height);
                        $bkg	=	imagecolorallocate($im,255,255,255); //背景色
                        $font	=	imagecolorallocate($im,150,182,151); //边框色
                        $sort_c	=	imagecolorallocate($im,0,0,0); //字体色
                        $name_c	=	imagecolorallocate($im,243,118,5); //字体色
                        $guest_c=	imagecolorallocate($im,34,93,156); //字体色
                        $info_c	=	imagecolorallocate($im,51,102,0); //字体色
                        $money_c=	imagecolorallocate($im,255,0,0); //字体色
                        $tm_c=	imagecolorallocate($im,0,0,0); //字体色
                        $fnt	=	$C_Patch."/app/member/ttf/simhei.ttf";

                        imagettftext($im,10,0,7,18,$sort_c,$fnt,$gTypeZhName); //彩票类别
                        imagettftext($im,10,0,str_leng($gTypeZhName.'=='),18,$name_c,$fnt,$lottery_number); //彩票期号
                        imagettftext($im,10,0,str_leng($gTypeZhName.$lottery_number.'==='),18,$guest_c,$fnt,$rtype_name); //投注玩法
                        imagettftext($im,10,0,str_leng($gTypeZhName.$lottery_number.$rtype_name.'===='),18,$info_c,$fnt,$betInfoIm); //投注内容
                        imagettftext($im,10,0,str_leng($gTypeZhName.$lottery_number.$rtype_name.$betInfoIm.'====='),18,$info_c,$fnt,$bet_money); //交易金额
                        imagettftext($im,10,0,str_leng($gTypeZhName.$lottery_number.$rtype_name.$betInfoIm.$bet_money.'======'),18,$money_c,$fnt,$fs_money); //反水
                        imagettftext($im,10,0,str_leng($gTypeZhName.$lottery_number.$rtype_name.$betInfoIm.$bet_money.$fs_money.'======='),18,$money_c,$fnt,$bet_rate); //赔率
                        imagettftext($im,10,0,str_leng($gTypeZhName.$lottery_number.$rtype_name.$betInfoIm.$bet_money.$fs_money.$bet_rate.'========'),18,$tm_c,$fnt,$tm); //交易时间
                        imagerectangle($im,0,0,$width-1,$height-1,$font); //画边框
                        if(!is_dir($C_Patch."\\order\\".substr($datereg_sub,0,8))) mkdir($C_Patch."\\order\\".substr($datereg_sub,0,8));
                        imagejpeg($im,$C_Patch."\\order\\".substr($datereg_sub,0,8)."/$datereg_sub.jpg"); //生成图片
                        imagedestroy($im);
                    }
                }
            }
            //验证订单和子订单的可赢金额是否一致，这里判断POST是否为外边传过来的
            $sql		= 	"select win from order_lottery where id=$id limit 1";
            $query 		=	$mysqli->query($sql);
            $rs1			=	$query->fetch_array();

            $sql		= 	"select SUM(win) AS win_total from order_lottery_sub where order_num='$datereg'";
            $query 		=	$mysqli->query($sql);
            $rs2			=	$query->fetch_array();

            if($rs1['win'] != $rs2['win_total']){
                $sql	=	"delete from order_lottery_sub where order_num='$datereg'";//操作失败，删除子订单
                $mysqli->query($sql);
                $sql	=	"delete from order_lottery where id=$id";//操作失败，删除订单
                $mysqli->query($sql);
                $sql = "DROP TRIGGER BeforeDeleteUserList;";
                $mysqli->query($sql);
                $sql	=	"delete from money_log where id=$money_log_id";//操作失败，删除金钱记录
                $mysqli->query($sql);
                $sql = "
                            CREATE TRIGGER `BeforeDeleteMoneyLog` BEFORE delete ON `money_log`
                              FOR EACH ROW BEGIN
                                insert into money_log(id) values (old.id);
                              END;
                            ";
                $mysqli->query($sql);
                $sql	=	"update user_list set money=money+$bet_money where user_id='$userid'";//操作失败，还原客户资金
                $mysqli->query($sql);
                return false;
            }
        }

        //验证一下账户金额
        $usermoney=0;
        $sql		= 	"select money from user_list where user_id='$userid' limit 1";
        $query 		=	$mysqli->query($sql);
        $rs			=	$query->fetch_array();

        $usermoney=$rs['money'];


        $sql		= 	"select balance from money_log where user_id='$userid' order by id desc limit 0,1";
        $query 		=	$mysqli->query($sql);
        $rs_l			=	$query->fetch_array();
        if($rs_l['balance']!=$usermoney){
            $sql = "update user_list set online=0,Oid='',status='异常',remark='$lottery_name($datereg)下注后资金异常$bet_time' where user_id='$userid'";
            $mysqli->query($sql);
            return false;
        }
        return true;
    }

    static function getOeouLocationByCode($aConcede){
        $sequence = "";
        if($aConcede=="M_ODD"){
            $sequence = "0";
        }elseif($aConcede=="M_EVEN"){
            $sequence = "1";
        }elseif($aConcede=="M_OVER"){
            $sequence = "2";
        }elseif($aConcede=="M_UNDER"){
            $sequence = "3";
        }elseif($aConcede=="M_PRIME"){
            $sequence = "4";
        }elseif($aConcede=="M_COMPO"){
            $sequence = "5";
        }

        elseif($aConcede=="C_ODD"){
            $sequence = "6";
        }elseif($aConcede=="C_EVEN"){
            $sequence = "7";
        }elseif($aConcede=="C_OVER"){
            $sequence = "8";
        }elseif($aConcede=="C_UNDER"){
            $sequence = "9";
        }elseif($aConcede=="C_PRIME"){
            $sequence = "10";
        }elseif($aConcede=="C_COMPO"){
            $sequence = "11";
        }

        elseif($aConcede=="U_ODD"){
            $sequence = "12";
        }elseif($aConcede=="U_EVEN"){
            $sequence = "13";
        }elseif($aConcede=="U_OVER"){
            $sequence = "14";
        }elseif($aConcede=="U_UNDER"){
            $sequence = "15";
        }elseif($aConcede=="U_PRIME"){
            $sequence = "16";
        }elseif($aConcede=="U_COMPO"){
            $sequence = "17";
        }

        elseif($aConcede=="MC_ODD"){
            $sequence = "18";
        }elseif($aConcede=="MC_EVEN"){
            $sequence = "19";
        }elseif($aConcede=="MC_OVER"){
            $sequence = "20";
        }elseif($aConcede=="MC_UNDER"){
            $sequence = "21";
        }elseif($aConcede=="MC_PRIME"){
            $sequence = "22";
        }elseif($aConcede=="MC_COMPO"){
            $sequence = "23";
        }

        elseif($aConcede=="MU_ODD"){
            $sequence = "24";
        }elseif($aConcede=="MU_EVEN"){
            $sequence = "25";
        }elseif($aConcede=="MU_OVER"){
            $sequence = "26";
        }elseif($aConcede=="MU_UNDER"){
            $sequence = "27";
        }elseif($aConcede=="MU_PRIME"){
            $sequence = "28";
        }elseif($aConcede=="MU_COMPO"){
            $sequence = "29";
        }

        elseif($aConcede=="CU_ODD"){
            $sequence = "30";
        }elseif($aConcede=="CU_EVEN"){
            $sequence = "31";
        }elseif($aConcede=="CU_OVER"){
            $sequence = "32";
        }elseif($aConcede=="CU_UNDER"){
            $sequence = "33";
        }elseif($aConcede=="CU_PRIME"){
            $sequence = "34";
        }elseif($aConcede=="CU_COMPO"){
            $sequence = "35";
        }

        elseif($aConcede=="MCU_ODD"){
            $sequence = "36";
        }elseif($aConcede=="MCU_EVEN"){
            $sequence = "37";
        }elseif($aConcede=="MCU_OVER"){
            $sequence = "38";
        }elseif($aConcede=="MCU_UNDER"){
            $sequence = "39";
        }elseif($aConcede=="MCU_PRIME"){
            $sequence = "40";
        }elseif($aConcede=="MCU_COMPO"){
            $sequence = "41";
        }
        return $sequence;
    }


    static function get535ByCode($aConcede){
        $sequence = "";
        if($aConcede=="535-ODD"){
            $sequence = "0";
        }elseif($aConcede=="535-EVEN"){
            $sequence = "1";
        }elseif($aConcede=="540-OVER"){
            $sequence = "2";
        }elseif($aConcede=="540-UNDER"){
            $sequence = "3";
        }elseif($aConcede=="545-PRIME"){
            $sequence = "4";
        }elseif($aConcede=="545-COMPO"){
            $sequence = "5";
        }

        elseif($aConcede=="536-ODD"){
            $sequence = "6";
        }elseif($aConcede=="536-EVEN"){
            $sequence = "7";
        }elseif($aConcede=="541-OVER"){
            $sequence = "8";
        }elseif($aConcede=="541-UNDER"){
            $sequence = "9";
        }elseif($aConcede=="546-PRIME"){
            $sequence = "10";
        }elseif($aConcede=="546-COMPO"){
            $sequence = "11";
        }

        elseif($aConcede=="537-ODD"){
            $sequence = "12";
        }elseif($aConcede=="537-EVEN"){
            $sequence = "13";
        }elseif($aConcede=="542-OVER"){
            $sequence = "14";
        }elseif($aConcede=="542-UNDER"){
            $sequence = "15";
        }elseif($aConcede=="547-PRIME"){
            $sequence = "16";
        }elseif($aConcede=="547-COMPO"){
            $sequence = "17";
        }

        elseif($aConcede=="538-ODD"){
            $sequence = "18";
        }elseif($aConcede=="538-EVEN"){
            $sequence = "19";
        }elseif($aConcede=="543-OVER"){
            $sequence = "20";
        }elseif($aConcede=="543-UNDER"){
            $sequence = "21";
        }elseif($aConcede=="548-PRIME"){
            $sequence = "22";
        }elseif($aConcede=="548-COMPO"){
            $sequence = "23";
        }

        elseif($aConcede=="539-ODD"){
            $sequence = "24";
        }elseif($aConcede=="539-EVEN"){
            $sequence = "25";
        }elseif($aConcede=="544-OVER"){
            $sequence = "26";
        }elseif($aConcede=="544-UNDER"){
            $sequence = "27";
        }elseif($aConcede=="549-PRIME"){
            $sequence = "28";
        }elseif($aConcede=="549-COMPO"){
            $sequence = "29";
        }

        elseif($aConcede=="550-ODD"){
            $sequence = "30";
        }elseif($aConcede=="550-EVEN"){
            $sequence = "31";
        }elseif($aConcede=="560-OVER"){
            $sequence = "32";
        }elseif($aConcede=="560-UNDER"){
            $sequence = "33";
        }elseif($aConcede=="570-PRIME"){
            $sequence = "34";
        }elseif($aConcede=="570-COMPO"){
            $sequence = "35";
        }

        elseif($aConcede=="551-ODD"){
            $sequence = "36";
        }elseif($aConcede=="551-EVEN"){
            $sequence = "37";
        }elseif($aConcede=="561-OVER"){
            $sequence = "38";
        }elseif($aConcede=="561-UNDER"){
            $sequence = "39";
        }elseif($aConcede=="571-PRIME"){
            $sequence = "40";
        }elseif($aConcede=="571-COMPO"){
            $sequence = "41";
        }

        elseif($aConcede=="552-ODD"){
            $sequence = "42";
        }elseif($aConcede=="552-EVEN"){
            $sequence = "43";
        }elseif($aConcede=="562-OVER"){
            $sequence = "44";
        }elseif($aConcede=="562-UNDER"){
            $sequence = "45";
        }elseif($aConcede=="572-PRIME"){
            $sequence = "46";
        }elseif($aConcede=="572-COMPO"){
            $sequence = "47";
        }

        elseif($aConcede=="553-ODD"){
            $sequence = "48";
        }elseif($aConcede=="553-EVEN"){
            $sequence = "49";
        }elseif($aConcede=="563-OVER"){
            $sequence = "50";
        }elseif($aConcede=="563-UNDER"){
            $sequence = "51";
        }elseif($aConcede=="573-PRIME"){
            $sequence = "52";
        }elseif($aConcede=="573-COMPO"){
            $sequence = "53";
        }

        elseif($aConcede=="554-ODD"){
            $sequence = "54";
        }elseif($aConcede=="554-EVEN"){
            $sequence = "55";
        }elseif($aConcede=="564-OVER"){
            $sequence = "56";
        }elseif($aConcede=="564-UNDER"){
            $sequence = "57";
        }elseif($aConcede=="574-PRIME"){
            $sequence = "58";
        }elseif($aConcede=="574-COMPO"){
            $sequence = "59";
        }
        elseif($aConcede=="555-ODD"){
            $sequence = "60";
        }elseif($aConcede=="555-EVEN"){
            $sequence = "61";
        }elseif($aConcede=="565-OVER"){
            $sequence = "62";
        }elseif($aConcede=="565-UNDER"){
            $sequence = "63";
        }elseif($aConcede=="575-PRIME"){
            $sequence = "64";
        }elseif($aConcede=="575-COMPO"){
            $sequence = "65";
        }
        elseif($aConcede=="556-ODD"){
            $sequence = "66";
        }elseif($aConcede=="556-EVEN"){
            $sequence = "67";
        }elseif($aConcede=="566-OVER"){
            $sequence = "68";
        }elseif($aConcede=="566-UNDER"){
            $sequence = "69";
        }elseif($aConcede=="576-PRIME"){
            $sequence = "70";
        }elseif($aConcede=="576-COMPO"){
            $sequence = "71";
        }
        elseif($aConcede=="557-ODD"){
            $sequence = "72";
        }elseif($aConcede=="557-EVEN"){
            $sequence = "73";
        }elseif($aConcede=="567-OVER"){
            $sequence = "74";
        }elseif($aConcede=="567-UNDER"){
            $sequence = "75";
        }elseif($aConcede=="577-PRIME"){
            $sequence = "76";
        }elseif($aConcede=="577-COMPO"){
            $sequence = "77";
        }
        elseif($aConcede=="558-ODD"){
            $sequence = "78";
        }elseif($aConcede=="558-EVEN"){
            $sequence = "79";
        }elseif($aConcede=="568-OVER"){
            $sequence = "80";
        }elseif($aConcede=="568-UNDER"){
            $sequence = "81";
        }elseif($aConcede=="578-PRIME"){
            $sequence = "82";
        }elseif($aConcede=="578-COMPO"){
            $sequence = "83";
        }
        elseif($aConcede=="559-ODD"){
            $sequence = "84";
        }elseif($aConcede=="559-EVEN"){
            $sequence = "85";
        }elseif($aConcede=="569-OVER"){
            $sequence = "86";
        }elseif($aConcede=="569-UNDER"){
            $sequence = "87";
        }elseif($aConcede=="579-PRIME"){
            $sequence = "88";
        }elseif($aConcede=="579-COMPO"){
            $sequence = "89";
        }

        elseif($aConcede=="580-ODD"){
            $sequence = "90";
        }elseif($aConcede=="580-EVEN"){
            $sequence = "91";
        }elseif($aConcede=="583-OVER"){
            $sequence = "92";
        }elseif($aConcede=="583-UNDER"){
            $sequence = "93";
        }elseif($aConcede=="586-PRIME"){
            $sequence = "94";
        }elseif($aConcede=="586-COMPO"){
            $sequence = "95";
        }
        elseif($aConcede=="581-ODD"){
            $sequence = "96";
        }elseif($aConcede=="581-EVEN"){
            $sequence = "97";
        }elseif($aConcede=="584-OVER"){
            $sequence = "98";
        }elseif($aConcede=="584-UNDER"){
            $sequence = "99";
        }elseif($aConcede=="587-PRIME"){
            $sequence = "100";
        }elseif($aConcede=="587-COMPO"){
            $sequence = "101";
        }
        elseif($aConcede=="582-ODD"){
            $sequence = "102";
        }elseif($aConcede=="582-EVEN"){
            $sequence = "103";
        }elseif($aConcede=="585-OVER"){
            $sequence = "104";
        }elseif($aConcede=="585-UNDER"){
            $sequence = "105";
        }elseif($aConcede=="588-PRIME"){
            $sequence = "106";
        }elseif($aConcede=="588-COMPO"){
            $sequence = "107";
        }
        return $sequence;
    }
}

?>