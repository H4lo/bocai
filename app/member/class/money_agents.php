<?php
class moneyAgents{
	static function chongzhi($uname,$money,$assets,$order=0,$status=2,$about=''){
	
		
		global $mysqli;
		$sql_user	=	"update agents_list set money=money+$money where agents_name='$uname'";
        $result2 = $mysqli->query($sql_user);
        $sql_money  =   "INSERT INTO `money_log_agents` (`agents_name`,`order_num`,`about`,`update_time`,`type`,`order_value`,`assets`,`balance`) VALUES ('$uname','$order','$about',now(),'后台充值','$money','$assets',$assets+$money);";
        $result = $mysqli->query($sql_money);
		
        return true;
	}
		  
	static function tixian($uname,$money,$assets,$order=0,$status=2,$about=''){
		
		global $mysqli;
    	$sql_user	=	"update agents_list set money=money-$money where agents_name='$uname'";
        $result2 = $mysqli->query($sql_user);
    	$money		=	0-$money; //把金额置成带符号数字
    	if($order	==	'0'){
			$order	=	date("YmdHis")."_".$_SESSION['username'];
		}
        $sql_money  =   "INSERT INTO `money_log_agents` (`agents_name`,`order_num`,`about`,`update_time`,`type`,`order_value`,`assets`,`balance`) VALUES ('$uname','$order','$about',now(),'后台提现','$money','$assets',$assets+$money);";
        $result = $mysqli->query($sql_money);
        return true;
    }
}
?>