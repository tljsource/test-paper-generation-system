	<?php
	$starttime=time();
	include "config/dbconnect.php";
	set_time_limit(0); //运行时间持续到程序运行结束为止
	error_reporting( E_ALL&~E_NOTICE );
	//首先去knowledge表中读出某章节下面的所有试题放入数组中
	$sql="SELECT `item` FROM `knowledge` WHERE `chapter`='243'";
	$result=mysqli_query($link,$sql);
	$length=mysqli_affected_rows($link);
	//var_dump($length);
	for ($i=0; $i < $length; $i++) { 
		$data=mysqli_fetch_array($result);
		$items[$i]=$data['item'];
		//$itemdata.=$items[$i].",";
	}
	//var_dump($items);

	$sql1="SELECT `id1`, `id2`, `cal_rho` FROM `ex2_relevancy` WHERE ";
	$id1="(";
	for ($i=0; $i < $length; $i++) { 
		$id1=$id1.$items[$i].",";
	}
	$item=substr($id1, 0,-1).")";
	$sql1=$sql1."id1 in ".$item."and id2 in".$item;
	//var_dump($sql1);
	$result1=mysqli_query($link,$sql1);
	$length1=mysqli_affected_rows($link);
	//var_dump($length1);
	for ($i=0; $i <$length1 ; $i++) { 
		$data1=mysqli_fetch_row($result1);
		for ($j=0; $j <3 ; $j++) { 
			$coe[$i][$j]=$data1[$j];
		}
	}
	//var_dump($coe);
	$itemarray=$coe;

	$sql2="DELETE FROM `knowledge_itempair`";
	mysqli_query($link,$sql2);
	for($i=0; $i < $length1; $i++) { 
		$item1=$itemarray[$i][0];
		$item2=$itemarray[$i][1];
		$coe=$itemarray[$i][2]*10;
		$pairs="INSERT INTO `knowledge_itempair`(`item1`, `item2`, `coefficient`) VALUES ('$item1','$item2','$coe')";
		mysqli_query($link,$pairs);
	}

	//为了获得关联数组
	$sql3="SELECT * FROM `knowledge_itempair`";
	$result2=mysqli_query($link,$sql3);
	for ($i=0; $i < $length1; $i++) { 
		$data3=mysqli_fetch_assoc($result2);
		$itempairs[$i]=$data3;
	}
	$itemjson=json_encode($itempairs);
	//echo "sucess";
	echo $itemjson;
?>