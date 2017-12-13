<html>
<head>
<title>计算试题相关性</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
</head>
<body>
<?php
$starttime=time();
include "config/dbconnect.php";
set_time_limit(0); //运行时间持续到程序运行结束为止
error_reporting( E_ALL&~E_NOTICE );

//先把所有的试题对取出来
$sql="SELECT `id1`, `id2`, `group`FROM `twopairs_items`";
$result=mysqli_query($link,$sql);
$length=mysqli_affected_rows($link);
for ($i=0; $i <$length ; $i++) { 
	$data=mysqli_fetch_row($result);
	$pairs[$i]=$data;
}
	//var_dump($pairs);
	$sql5="INSERT INTO `ex2_relevancy`(`no`, `id1`, `id2`, `cal_rho`,`fixed_rho`) VALUES";
	$sql6="DELETE FROM `ex2_relevancy`";
	mysqli_query($link,$sql6);
	$no=0;
//由于试题对的两个试题都是来自于同一个章节，因此判断该试题对的试题来自哪个章节时只需要判断其中一个
for ($i=0; $i < $length; $i++) { 
	$item1=$pairs[$i][0];
	$item2=$pairs[$i][1];

	$sql1="SELECT `chapter` FROM `knowledge` WHERE `item`='$item1'";
	$result1=mysqli_query($link,$sql1);
	$chapter=mysqli_fetch_row($result1);
	//var_dump($chapter[0]);   //数组chapter中的都是各个试题对对应的章节
	//echo "<hr>";
	//计算试题对相关度，首先要获得该章节下的事项数量
	$sql2="SELECT *FROM `exercise2_ans_history` WHERE `chap_lesson`='$chapter[0]'";
	$result2=mysqli_query($link,$sql2);
	$length1=mysqli_affected_rows($link);

	//取出试题对中的试题各自错的次数
	$sql3="SELECT `number` FROM `items_single_num` WHERE `item`='$item1'";
	$result3=mysqli_query($link,$sql3);
	$data3=mysqli_fetch_row($result3);
	$TA=$data3[0];

	$sql4="SELECT `number` FROM `items_single_num` WHERE `item`='$item2'";
	$result4=mysqli_query($link,$sql4);
	$data4=mysqli_fetch_row($result4);
	$TB=$data4[0];
	//计算相关度
	$T=$length1;
	$TAB=$pairs[$i][2];

	if(($TA<=500||$TB<=500)){
		$a=($T*$TAB)-($TA*$TB);
		$b=$TA*($T-$TA)*$TB*($T-$TB);
		$c=sqrt($b);
		$p=$a/$c;
		$no++;
		$coe=sprintf("%.4f", $p);
		if ($item1<$item2) {
				$sql5.="('$no','$item1','$item2','$coe','$coe'),";
			}else{
				$sql5.="('$no','$item2','$item1','$coe','$coe'),";
			}
        }
}
	$sql5= substr($sql5,0,strlen($sql5)-1);
	//echo $sql5;
	mysqli_query($link,$sql5);  
	                     
	$end=time();
	$interval=$end-$starttime;
	echo "运行时间为".$interval."<br>";
	echo "知识点计算成功";
?>
</body>
</html>