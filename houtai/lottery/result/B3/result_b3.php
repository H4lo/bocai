<?php
session_start();
header("Expires: Mon, 26 Jul 1970 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: text/html; charset=utf-8");

$C_Patch=$_SERVER['DOCUMENT_ROOT'];
include_once($C_Patch."/app/member/include/address.mem.php");
include_once($C_Patch."/app/member/include/config.inc.php");
include_once($C_Patch."/app/member/common/function.php");

include_once("../../../class/admin.php");
include_once("../../../common/login_check.php");
include_once("../Js_Class.php");

echo "<script>if(self == top) parent.location='" . BROWSER_IP . "'</script>\n";

check_quanxian("手工录入彩票结果");

$id	=	0;
$lottery_type = $_GET['type'];//彩票类型、3D彩、排列三、上海时时乐

if($lottery_type=="3D彩"){
    $query_time = date('Y-m-d',strtotime('-31 day'));
}elseif($lottery_type=="排列三"){
    $query_time = date('Y-m-d',strtotime('-31 day'));
}elseif($lottery_type=="上海时时乐"){
    $query_time = date("Y-m-d",time());
}

$qishu_query = "";
if($_GET['id'] > 0){
    $id	=	$_GET['id'];
}
if($_GET['s_time']){
    $query_time = $_GET['s_time'];
}
if($_GET['qishu_query']){
    $qishu_query = $_GET['qishu_query'];
}
if($lottery_type=="3D彩"){
    $gType = "d3";
}elseif($lottery_type=="排列三"){
    $gType = "p3";
}elseif($lottery_type=="上海时时乐"){
    $gType = "t3";
}

if($_GET["action"]=="add" && $id==0){
    $create_time = date("Y-m-d H:i:s",time());
    $qishu		=	$_POST["qishu"];
    $datetime	=	$_POST["datetime"];
    $ball_1		=	$_POST["ball_1"];
    $ball_2		=	$_POST["ball_2"];
    $ball_3		=	$_POST["ball_3"];
    $sql = "select id from lottery_result_".$gType." where qishu='$qishu'";
    $query = $mysqli->query($sql);
    $row    =	$query->fetch_array();
    if($row && $row["id"]){
        message("该期彩票结果已存在，请查询后编辑。","result_b3.php?type=$lottery_type&s_time=$query_time");
    }else{
        $sql		=	"insert into lottery_result_".$gType."(qishu,create_time,datetime,ball_1,ball_2,ball_3) values (".$qishu.",'".$create_time."','".$datetime."',".$ball_1.",".$ball_2.",".$ball_3.")";
        $mysqli->query($sql);
    }
}elseif($_GET["action"]=="edit" && $id>0){
    $sql		=	"select * from lottery_result_".$gType." WHERE id='$id'";
    $query	=	$mysqli->query($sql);
    $row    =	$query->fetch_array();
    $prev_text = "修改时间：".(date("Y-m-d H:i:s",time()))."。\n修改前内容：".$row["ball_1"].",".$row["ball_2"].",".$row["ball_3"]."。\n修改后内容：".$_POST["ball_1"].",".$_POST["ball_2"].",".$_POST["ball_3"]."。".'\n\n'.$row["prev_text"];

    $qishu		=	$_POST["qishu"];
    $datetime	=	$_POST["datetime"];
    $ball_1		=	$_POST["ball_1"];
    $ball_2		=	$_POST["ball_2"];
    $ball_3		=	$_POST["ball_3"];
    $sql		=	"update lottery_result_".$gType." set prev_text='".$prev_text."', qishu=".$qishu.",datetime='".$datetime."',ball_1=".$ball_1.",ball_2=".$ball_2.",ball_3=".$ball_3." where id=".$id."";
    $mysqli->query($sql);
}elseif($_GET["action"]=="delete" && $id>0){
    $sql		=	"delete from lottery_result_".$gType." WHERE id='$id'";
    $query	=	$mysqli->query($sql);
    if($query){echo '<script>alert("删除成功"); window.href.location="/bh-100/Lottery/result/B3/result_b3.php?status=0&type=3D%E5%BD%A9";</script>';}
	else{echo '<script>alert("删除失败"); window.href.location="/bh-100/Lottery/result/B3/result_b3.php?status=0&type=3D%E5%BD%A9";</script>';}
}
?><html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Welcome</title>
    <link rel="stylesheet" href="../../../images/css/admin_style_1.css" type="text/css" media="all" />
    <script language="javascript" src="../../../js/jquery-1.7.2.min.js"></script>
    <script language="javascript" src="query_b3.js"></script>
    <script language="JavaScript" src="/js/calendar.js"></script>
</head>
<body>
<div id="pageMain">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td valign="top">
<form name="form1" onSubmit="return check_submit();" method="post" action="?id=<?=$id?>&action=<?=$id>0 ? 'edit' : 'add'?>&type=<?=$lottery_type?>&s_time=<?=$query_time?>&qishu_query=<?=$qishu_query?>">
<?php
if($id>0 && !isset($_GET['action'])){
    $sql = "SELECT id,qishu,create_time,datetime,state,prev_text,
            ball_1,ball_2,ball_3
            FROM lottery_result_".$gType." WHERE id=$id limit 0,1";
    $query	=	$mysqli->query($sql);
    $rs		=	$query->fetch_array();
}
?>
<table width="100%" border="0" cellpadding="5" cellspacing="1" class="font12" style="margin-top:5px;" bgcolor="#798EB9">
<tr>
    <td  align="left" bgcolor="#3C4D82" style="color:#FFF">彩票类别：</td>
    <td  align="left" bgcolor="#3C4D82" style="color:#FFF"><strong><?=$lottery_type?></strong></td>
</tr>
<tr>
    <td width="60"  align="left" bgcolor="#F0FFFF">开奖期号：</td>
    <td  align="left" bgcolor="#FFFFFF"><input name="qishu" type="text" id="qishu" value="<?=$rs['qishu']?>" size="20" maxlength="16"/></td>
</tr>
<tr>
    <td align="left" bgcolor="#F0FFFF">开奖时间：</td>
    <td align="left" bgcolor="#FFFFFF"><input name="datetime" type="text" id="datetime" value="<?=$rs['datetime']?>" size="20" maxlength="19"/> 注意：时间格式务必填写正确，如2014-10-10 10:10:10</td>
</tr>
<tr>
    <td align="left" bgcolor="#F0FFFF">开奖号码：</td>
    <td align="left" bgcolor="#FFFFFF"><select name="ball_1" id="ball_1">
            <option value="0" <?=$rs['ball_1']==0 ? 'selected' : ''?>>0</option>
            <option value="1" <?=$rs['ball_1']==1 ? 'selected' : ''?>>1</option>
            <option value="2" <?=$rs['ball_1']==2 ? 'selected' : ''?>>2</option>
            <option value="3" <?=$rs['ball_1']==3 ? 'selected' : ''?>>3</option>
            <option value="4" <?=$rs['ball_1']==4 ? 'selected' : ''?>>4</option>
            <option value="5" <?=$rs['ball_1']==5 ? 'selected' : ''?>>5</option>
            <option value="6" <?=$rs['ball_1']==6 ? 'selected' : ''?>>6</option>
            <option value="7" <?=$rs['ball_1']==7 ? 'selected' : ''?>>7</option>
            <option value="8" <?=$rs['ball_1']==8 ? 'selected' : ''?>>8</option>
            <option value="9" <?=$rs['ball_1']==9 ? 'selected' : ''?>>9</option>
            <option value="" <?=$rs['ball_1']=='' ? 'selected' : ''?>>第一球</option>
        </select>
        <select name="ball_2" id="ball_2">
            <option value="0" <?=$rs['ball_2']==0 ? 'selected' : ''?>>0</option>
            <option value="1" <?=$rs['ball_2']==1 ? 'selected' : ''?>>1</option>
            <option value="2" <?=$rs['ball_2']==2 ? 'selected' : ''?>>2</option>
            <option value="3" <?=$rs['ball_2']==3 ? 'selected' : ''?>>3</option>
            <option value="4" <?=$rs['ball_2']==4 ? 'selected' : ''?>>4</option>
            <option value="5" <?=$rs['ball_2']==5 ? 'selected' : ''?>>5</option>
            <option value="6" <?=$rs['ball_2']==6 ? 'selected' : ''?>>6</option>
            <option value="7" <?=$rs['ball_2']==7 ? 'selected' : ''?>>7</option>
            <option value="8" <?=$rs['ball_2']==8 ? 'selected' : ''?>>8</option>
            <option value="9" <?=$rs['ball_2']==9 ? 'selected' : ''?>>9</option>
            <option value="" <?=$rs['ball_2']=='' ? 'selected' : ''?>>第二球</option>
        </select>
        <select name="ball_3" id="ball_3">
            <option value="0" <?=$rs['ball_3']==0 ? 'selected' : ''?>>0</option>
            <option value="1" <?=$rs['ball_3']==1 ? 'selected' : ''?>>1</option>
            <option value="2" <?=$rs['ball_3']==2 ? 'selected' : ''?>>2</option>
            <option value="3" <?=$rs['ball_3']==3 ? 'selected' : ''?>>3</option>
            <option value="4" <?=$rs['ball_3']==4 ? 'selected' : ''?>>4</option>
            <option value="5" <?=$rs['ball_3']==5 ? 'selected' : ''?>>5</option>
            <option value="6" <?=$rs['ball_3']==6 ? 'selected' : ''?>>6</option>
            <option value="7" <?=$rs['ball_3']==7 ? 'selected' : ''?>>7</option>
            <option value="8" <?=$rs['ball_3']==8 ? 'selected' : ''?>>8</option>
            <option value="9" <?=$rs['ball_3']==9 ? 'selected' : ''?>>9</option>
            <option value="" <?=$rs['ball_3']=='' ? 'selected' : ''?>>第三球</option>
        </select>
    </td>
</tr>
<tr>
    <td align="center" bgcolor="#FFFFFF">&nbsp;</td>
    <td align="left" bgcolor="#FFFFFF"><input name="submit" type="submit" class="submit80" value="确认发布"/></td>
</tr>
</table>
</form>

<form name="form2" onSubmit="return queryLottery();" method="get" action="?1=1">
<table width="100%" border="0" cellpadding="5" cellspacing="1" class="font12" style="margin-top:5px;" >
    <tr style="background-color:#FFFFFF;">
        <td align="left">
            &nbsp;&nbsp;开奖期号：
            <input name="qishu_query" type="text" id="qishu_query" value="<?=$qishu_query?>" size="20" maxlength="11"/>
            &nbsp;&nbsp;日期：
            <input name="s_time" type="text" id="s_time" value="<?=$query_time?>" onClick="new Calendar(2010,2020).show(this);" size="10" maxlength="10" readonly="readonly" />
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input name="submit" type="submit" class="submit80" value="搜索"/>
            <input name="type" type="hidden" value="<?=$lottery_type?>"/>
        </td>
    </tr>
</table>
</form>
<table width="100%" border="0" cellpadding="5" cellspacing="1" class="font12" style="margin-top:2px;" bgcolor="#798EB9">
    <tr style="background-color:#3C4D82; color:#FFF">
        <td align="center"><strong>彩票类别</strong></td>
        <td align="center"><strong>彩票期号</strong></td>
        <td align="center"><strong>开奖时间</strong></td>
        <td align="center"><strong>第一球</strong></td>
        <td align="center"><strong>第二球</strong></td>
        <td align="center"><strong>第三球</strong></td>
        <td align="center"><strong>总和</strong></td>
        <td align="center"><strong>龙虎</strong></td>
        <td align="center"><strong>三连</strong></td>
        <td align="center"><strong>跨度</strong></td>
        <td align="center"  height="25">结算</td>
        <td align="center"><strong>重算</strong></td>
        <td align="center"><strong>操作</strong></td>
    </tr>
    <?php

    $whereString = " DATE_FORMAT(datetime,'%Y-%m-%d') >= '$query_time' ";
    if($lottery_type=="上海时时乐"){
        $whereString = " DATE_FORMAT(datetime,'%Y-%m-%d') = '$query_time' ";
    }

    $sql = "SELECT id,qishu,create_time,datetime,state,prev_text,
        ball_1,ball_2,ball_3
        FROM lottery_result_".$gType." WHERE ".$whereString;
    if($qishu_query){
        $sql .= " AND qishu = '$qishu_query'";
    }
    $sql .= " ORDER BY qishu DESC ";
    $query	=	$mysqli->query($sql);
    while($rows = $query->fetch_array()){
        $color = "#FFFFFF";
        $over	 = "#EBEBEB";
        $out	 = "#ffffff";
        $hm 		= array();
        $hm[]		= BuLing($rows['ball_1']);
        $hm[]		= BuLing($rows['ball_2']);
        $hm[]		= BuLing($rows['ball_3']);
        if($rows['state']=="0"){
            $ok = '<a href="js_b3.php?qi='.$rows['qishu'].'&jsType=0&type='.$lottery_type.'&gtype='.$gType.'&s_time='.$query_time.'" title="点击结算"><font color="#0000FF">未结算</font></a>';
        }else{
            $ok = '<a href="js_b3.php?qi='.$rows['qishu'].'&jsType=1&type='.$lottery_type.'&gtype='.$gType.'&s_time='.$query_time.'" title="重新结算"><font color="#FF0000">已结算</font></a>';
        }
        if($rows['state']=="2"){
            $again = '<font color="#FF0000" style="font-size:18px">√</font>';
        }else{
            $again = '<font color="#0000FF" style="font-size:20px">×</font>';
        }
        ?>
        <tr align="center" onMouseOver="this.style.backgroundColor='<?=$over?>'" onMouseOut="this.style.backgroundColor='<?=$out?>'" style="background-color:<?=$color?>; line-height:20px;">
            <td height="25" align="center" valign="middle"><?=$lottery_type?></td>
            <td align="center" valign="middle"><?=$rows['qishu']?></td>
            <td align="center" valign="middle"><?=$rows['datetime']?></td>
            <td align="center" valign="middle"><img src="/images/Lottery/Images/Ball_2/<?=$rows['ball_1']?>.png"></td>
            <td align="center" valign="middle"><img src="/images/Lottery/Images/Ball_2/<?=$rows['ball_2']?>.png"></td>
            <td align="center" valign="middle"><img src="/images/Lottery/Images/Ball_2/<?=$rows['ball_3']?>.png"></td>
            <td><?=f3D_Auto($hm,1)?> / <?=f3D_Auto($hm,2)?> / <?=f3D_Auto($hm,3)?></td>
            <td><?=f3D_Auto($hm,4)?></td>
            <td><?=f3D_Auto($hm,5)?></td>
            <td><?=f3D_Auto($hm,6)?></td>
            <td><?=$ok?></td>
            <td><?=$again?></td>
            <td>
                <a href="?id=<?=$rows["id"]?>&type=<?=$lottery_type?>&s_time=<?=$query_time?>&qishu_query=<?=$qishu_query?>">编辑</a>
				<a onclick="return confirm('确定删除?');" href="?action=delete&id=<?=$rows["id"]?>&type=<?=$lottery_type?>&s_time=<?=$query_time?>&qishu_query=<?=$qishu_query?>">删除</a>
                <a onclick='queryResult("<?=$rows['id']?>")' title="查看修改记录"><font>查看记录</font></a>
                <input type="hidden" id="<?='prev_text'.$rows['id']?>" value="<?=$rows['prev_text']?>" />
            </td>
        </tr>
    <?php
    }
    ?>
</table></td>
</tr>
</table>
</div>
</body>
</html>