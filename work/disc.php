<!DOCTYPE html>
<html>
<head>
	<title>
		find2freq
	</title>
</head>
<body>
<?php
$start=time();
ini_set("memory_limit","-1");
include "config/dbconnect.php";
set_time_limit(0); 
error_reporting( E_ALL&~E_NOTICE );
$sql="SELECT * FROM `exercise2_ans_history` WHERE `chap_lesson`='64'";
$result=mysqli_query($link,$sql);
$length=mysqli_affected_rows($link);
for ($i=0; $i < $length; $i++) { 
	$data=mysqli_fetch_row($result);
	$testrecord[$i]=$data;
}
//var_dump($testrecord); //该章节下所有答题记录

//统计所有学生的成绩
for ($i=0; $i <$length ; $i++) { 
	$j=4;
	while($j<23) {
		if ($testrecord[$i][$j]<0) {
			$wrong[$i][]=$testrecord[$i][$j-1];
		}
		$j=$j+2;
	}
}
//var_dump($wrong);//所有错题记录

//统计每个学生的总分
for ($i=0; $i <$length ; $i++) { 
	$len=count($wrong[$i]);
	$score[$i]=10-$len;
}
arsort($score);  //从高到低排列保持索引关系
//var_dump($score);

//获得高分组记录的索引
$length1=count($score);
$key=array_keys($score);
for ($i=0; $i < $length1*0.27-1; $i++) { 
	$highkey[$i]=$key[$i];
}

asort($score);
//var_dump($score);
//获得低分组记录的索引
$key1=array_keys($score);
for ($i=0; $i < $length1*0.27-1; $i++) { 
	$lowkey[$i]=$key1[$i];
}
//var_dump($lowkey);

//求区分度，用高分组通过率减去低分组的通过率
for ($i=0; $i < $length1*0.27-1; $i++) {
	$high[]=$wrong[$highkey[$i]];
	$low[]=$wrong[$lowkey[$i]];
}
//var_dump($high);  //发现$high里面没数据，说明全对
//var_dump($low);  //发现$high里面没数据，说明全对

for ($i=0; $i < $length1*0.27-1; $i++) { 
	$len2=count($low[$i]);
	for ($j=0; $j <$len2 ; $j++) { 
		$totalitem[]=$low[$i][$j];
	}
}

//低分组的错题数量
$lowitemnum=array_count_values($totalitem);
//var_dump($lowitemnum);

$lowgroupkey=array_keys($lowitemnum);
for ($i=0; $i < 10; $i++) { 
	$highnum=56;
	$lownum=56-$lowitemnum[$lowgroupkey[$i]];  //这个是低分组的学生答对的数量
	$disc[$lowgroupkey[$i]]=($highnum-$lownum)/56;
}
//var_dump($disc);
//求区间的平均区分度

$len1=count($disc);
$itemskey=array_keys($disc);

//求各个试题对的区分度差值，首先先取出该章节下的所有试题对
$sql1="SELECT `id1`, `id2`, `cal_rho` FROM `ex2_relevancy` WHERE ";
	$id1="(";
	for ($i=0; $i < $len1; $i++) { 
		$id1=$id1.$itemskey[$i].",";
	}
	$item=substr($id1, 0,-1).")";
	$sql1=$sql1."id1 in ".$item."and id2 in".$item;
	//var_dump($sql1);
	$result1=mysqli_query($link,$sql1);
	$length4=mysqli_affected_rows($link);
	//var_dump($length4);
	for ($i=0; $i <$length4 ; $i++) { 
		$data1=mysqli_fetch_row($result1);
		for ($j=0; $j <3 ; $j++) { 
			$coe[$i][$j]=$data1[$j];
		}
		$sort[$i]=$coe[$i][2];
	}
	//var_dump($coe);   //为该章节所有试题对的相关度
	asort($sort);
	//var_dump($sort);  //将取出来的试题对按照相关度排序，从小到大排列，以0.1区间间隔分类

	$sortkey=array_keys($sort);
	for ($i=0; $i < $length4; $i++) { 
		if ($coe[$sortkey[$i]][2]<0.1) {
			$interval1[]=$coe[$sortkey[$i]];
		}elseif ($coe[$sortkey[$i]][2]>=0.1 && $coe[$sortkey[$i]][2]<0.2) {
			$interval2[]=$coe[$sortkey[$i]];
		}elseif ($coe[$sortkey[$i]][2]>=0.2 && $coe[$sortkey[$i]][2]<0.3) {
			$interval3[]=$coe[$sortkey[$i]];
		}elseif ($coe[$sortkey[$i]][2]>=0.3 && $coe[$sortkey[$i]][2]<0.4) {
			$interval4[]=$coe[$sortkey[$i]];
		}elseif ($coe[$sortkey[$i]][2]>=0.4 && $coe[$sortkey[$i]][2]<0.5) {
			$interval5[]=$coe[$sortkey[$i]];
		}elseif ($coe[$sortkey[$i]][2]>=0.5 && $coe[$sortkey[$i]][2]<0.6) {
			$interval6[]=$coe[$sortkey[$i]];
		}elseif ($coe[$sortkey[$i]][2]>=0.6 && $coe[$sortkey[$i]][2]<0.7) {
			$interval7[]=$coe[$sortkey[$i]];
		}elseif ($coe[$sortkey[$i]][2]>=0.7 && $coe[$sortkey[$i]][2]<0.8) {
			$interval8[]=$coe[$sortkey[$i]];
		}
	}
	var_dump($interval1);
	echo "<hr>";
	var_dump($interval2);
	echo "<hr>";
	var_dump($interval3);
	echo "<hr>";
	var_dump($interval4);
	echo "<hr>";
	var_dump($interval5);
	echo "<hr>";
	var_dump($interval6);
	echo "<hr>";
	var_dump($interval7);
	echo "<hr>";
	var_dump($interval8);

//下面计算每个区间的平均区分度，按照每个试题对平均区分度之和再求平均，相当于把该区间的每个试题的区分度相加除以（2*试题对数量）
	$total1=0;
	$interval1len=count($interval1);
	for ($i=0; $i < $interval1len; $i++) { 
		for ($j=0; $j < 2; $j++) { 
			$total1=$total1+$disc[$interval1[$i][$j]];
		}
	}
	$total1=$total1/($interval1len*2);
	var_dump($total1);

	$total2=0;
	$interval2len=count($interval2);
	for ($i=0; $i < $interval2len; $i++) { 
		for ($j=0; $j < 2; $j++) { 
			$total2=$total2+$disc[$interval2[$i][$j]];
		}
	}
	$total2=$total2/($interval2len*2);
	var_dump($total2);

	$total3=0;
	$interval3len=count($interval3);
	for ($i=0; $i < $interval3len; $i++) { 
		for ($j=0; $j < 2; $j++) { 
			$total3=$total3+$disc[$interval3[$i][$j]];
		}
	}
	$total3=$total3/($interval3len*2);
	var_dump($total3);

	$total4=0;
	$interval4len=count($interval4);
	for ($i=0; $i < $interval4len; $i++) { 
		for ($j=0; $j < 2; $j++) { 
			$total4=$total4+$disc[$interval4[$i][$j]];
		}
	}
	$total4=$total4/($interval4len*2);
	var_dump($total4);

	$total5=0;
	$interval5len=count($interval5);
	for ($i=0; $i < $interval5len; $i++) { 
		for ($j=0; $j < 2; $j++) { 
			$total5=$total5+$disc[$interval5[$i][$j]];
		}
	}
	$total5=$total5/($interval5len*2);
	var_dump($total5);

	$total6=0;
	$interval6len=count($interval6);
	for ($i=0; $i < $interval6len; $i++) { 
		for ($j=0; $j < 2; $j++) { 
			$total6=$total6+$disc[$interval6[$i][$j]];
		}
	}
	$total6=$total6/($interval6len*2);
	var_dump($total6);

	$total7=0;
	$interval7len=count($interval7);
	for ($i=0; $i < $interval7len; $i++) { 
		for ($j=0; $j < 2; $j++) { 
			$total7=$total7+$disc[$interval7[$i][$j]];
		}
	}
	$total7=$total7/($interval7len*2);
	var_dump($total7);

	$total8=0;
	$interval8len=count($interval8);
	for ($i=0; $i < $interval8len; $i++) { 
		for ($j=0; $j < 2; $j++) { 
			$total8=$total8+$disc[$interval8[$i][$j]];
		}
	}
	$total8=$total8/($interval8len*2);
	var_dump($total8);

	
?>
</body>
</html>
