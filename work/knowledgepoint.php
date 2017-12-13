<html>
<head>
<title>知识点</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
</head>
<body>
<?php
$start=time();
include "config/dbconnect.php";
set_time_limit(0); //运行时间持续到程序运行结束为止
error_reporting( E_ALL&~E_NOTICE );
$dataform=mysqli_query($link,'select * from exercise2_ans_history'); 
$recordtotal=mysqli_affected_rows($link);
for ($i=0; $i < $recordtotal; $i++) { 
	$record[$i]=mysqli_fetch_row($dataform);
	$knowledge[$i]=$record[$i][23];
}
//var_dump($knowledge);   //$knowledge数组存放所有试题对应的知识点
$knowledgevalues=array_count_values($knowledge);
//var_dump($knowledgevalues);   
$knowledgepoint=array_keys($knowledgevalues);
//var_dump($knowledgepoint);   //$knowledgepoint数组存放的都是答题记录涉及到的所有知识点，没有重复的

$length=count($knowledgepoint);
for ($i=0; $i <$length; $i++) { 
	$sql="SELECT * FROM `exercise2_ans_history` WHERE `chap_lesson`='$knowledgepoint[$i]'";
	$data=mysqli_query($link,$sql);
	$itemrecord=mysqli_affected_rows($link);
	$itemarray=array();
	$recorddata=array();
	for ($j=0; $j <$itemrecord ; $j++) { 
		$itemarray[]=mysqli_fetch_row($data);     //此时$itemarray存放的是对应知识点章节的所有答题记录
		$len=count($itemarray[$j]);
		for ($t=3; $t < $len; $t++) { 
		 	$recorddata[$j][]=$itemarray[$j][$t];
		}  
		$k=1;
		$t=1;
		while ($t<$len-4) {
			$itemsarray[$knowledgepoint[$i]][]=$recorddata[$j][$t-1];
			$t=$t+2;
		}
		while ($k < $len) {
			if ($recorddata[$j][$k]<0) {
				$itemswrongarray[$knowledgepoint[$i]][]=$recorddata[$j][$k-1];
			}
		$k=$k+2;
		}
	}
}  
//var_dump($itemsarray);   //$itemsarray每章节下面的所有试题
for ($i=0; $i <$length ; $i++) { 
	$values=array_count_values($itemsarray[$knowledgepoint[$i]]);
	$keys=array_keys($values);
	$items[$knowledgepoint[$i]]=$keys;
}
$sql2="DELETE FROM `knowledge`";
mysqli_query($link,$sql2);

for ($k=0; $k < 20; $k++) {
	$sql1="INSERT INTO `knowledge`(`item`, `chapter`) VALUES"; 
	for ($i=$k*$length/20; $i < ($k+1)*$length/20; $i++) { 
		$pointlength=count($items[$knowledgepoint[$i]]);
		for ($j=0; $j < $pointlength; $j++) { 
			$item=$items[$knowledgepoint[$i]][$j];
			$sql1.="('$item','$knowledgepoint[$i]'),";
		}
	}
	$sql1 = substr($sql1,0,strlen($sql1)-1);
	//var_dump($sql1);
	mysqli_query($link,$sql1);
}
//var_dump($sql1);


//var_dump($itemswrongarray);		 //此时数组$itemswrongarray中的key为知识点的章节号，对应的values是章节下的错题，为了计算比例，知识点有侧重

//用来求各个知识点的比例，因此要求所有记录中的试题数量总数
/*$itemstotallength=0;
for ($i=0; $i < $length; $i++) { 
	$length8=count($itemsarray[$i]);
	$itemstotallength=$itemstotallength+$length8;
}
//var_dump($itemstotallength);

//$itemunique数组中存放的key是知识点章节，对应的键值也是数组，是当前章节下不重复的试题，因为是一个二维数组
for ($i=0; $i < $length; $i++) { 
	$itemlength=count($itemsarray[$knowledgepoint[$i]]);
	$itemunique[$knowledgepoint[$i]]=array();
	for ($j=0; $j < $itemlength; $j++) { 
		$a=$itemsarray[$knowledgepoint[$i]][$j];
		if (!in_array($a, $itemunique[$knowledgepoint[$i]])) {
			$itemunique[$knowledgepoint[$i]][]=$a;      //$itemunique包括所有试题，错的和正确的
		}
	}	
}
	//var_dump($itemunique);   //该数组存放的是每个章节下所有错题，不重复，key是章节，values是试题
	unset($itemsarray);

	$sum=0;
	for ($i=0; $i <$length ; $i++) { 
		$length7=count($itemunique[$knowledgepoint[$i]]);
		$sum=$sum+$length7;
	}
	//var_dump($sum);                 //$sum为所有不重复试题的数量
	$item1=array();
	$sql4="SELECT `item`, `chapter` FROM `knowledge` ";
	$result1=mysqli_query($link,$sql4);
	$length5=mysqli_affected_rows($link);
	for ($i=0; $i < $length5; $i++) { 
		$knowledge1[]=mysqli_fetch_row($result1);
		$item1[]=$knowledge1[$i][0];
	}
	//var_dump($item1);       //数据表中已经存在的试题

	$sql5="INSERT INTO `knowledge`(`item`, `chapter`) VALUES";
	$flag=0;
	for ($i=0; $i <$length ; $i++) { 
		$length6=count($itemunique[$knowledgepoint[$i]]);
		for ($j=0; $j < $length6; $j++) { 
			$items=$itemunique[$knowledgepoint[$i]][$j];
			if (!in_array($items, $item1)) {
				$flag++;
				$sql5.="('$items','$knowledgepoint[$i]'),";
			}else{
				$sql6="UPDATE `knowledge` SET `chapter`='$knowledgepoint[$i]' WHERE `item`='$items'";
				mysqli_query($link,$sql6);
			}
		}
	}
	if ($flag==$sum) {           //当有新的试题产生时不太正确
		$sql5 = substr($sql5,0,strlen($sql5)-1);
		//var_dump($sql5);
		mysqli_query($link,$sql5);
	}
	//unset($itemunique);

/*	//计算每个知识点的比例  $itemswrongarray中的都是每个知识点对应的错题
	for ($i=0; $i < $length; $i++) { 
		$length3=count($itemswrongarray[$knowledgepoint[$i]]);
		$p=$length3/$itemstotallength;
		$propotion[$knowledgepoint[$i]]=sprintf("%.4f", $p);
	}
	arsort($propotion);  //对知识点比例降序排列
	var_dump($propotion);
	//将各个章节知识点对应的比例放入数据表中存放

	unset($itemswrongarray);
	$sql2="SELECT `knowledgechapter` FROM `knowledgeproportion`";
	$result=mysqli_query($link,$sql2);
	$length4=mysqli_affected_rows($link);
	for ($i=0; $i < $length4; $i++) { 
		$chapter[]=mysqli_fetch_row($result);
		$array[]=$chapter[$i][0];
	}
	for ($i=0; $i <$length ; $i++) { 
		if (!in_array($knowledgepoint[$i], $array)) {
			$pro=$propotion[$knowledgepoint[$i]];
			$sql1="INSERT INTO `knowledgeproportion`(`knowledgechapter`, `proportion`) VALUES ('$knowledgepoint[$i]','$pro')";
			mysqli_query($link,$sql1);
		}else{
			$pro1=$propotion[$knowledgepoint[$i]];
			$sql3="UPDATE `knowledgeproportion` SET `proportion`='$pro1' WHERE `knowledgechapter`='$knowledgepoint[$i]'";
			mysqli_query($link,$sql3);
		}
	}	*/
	$end=time();
	$interval=$end-$start;
	echo "运行时间为".$interval."<br>";
	echo "知识点获取成功";
?>
</body>
</html>