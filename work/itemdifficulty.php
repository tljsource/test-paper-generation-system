<html>
<?php
$start=time();
include "config/dbconnect.php";
set_time_limit(0); //运行时间持续到程序运行结束为止
error_reporting( E_ALL&~E_NOTICE );
$dataform=mysqli_query($link,'select * from exercise2_ans_history'); //设定做题记录中前500条记录为旧做题记录，后面的记录为新增的做题记录
$rows=mysqli_affected_rows($link);
$data_final_array=array();
$time=array();
	while ($dataform_array=mysqli_fetch_row($dataform)) {
		$data_final_array[]=$dataform_array;
	}
	$columns=count($data_final_array[0]);

	$realdata_array=array(); 
	for ($i=0; $i < $rows; $i++) {
		for ($j=3; $j < $columns; $j++) { 
		 	$realdata_array[$i][]=$data_final_array[$i][$j];
		 } 
	}
	//var_dump($realdata_array);
	$length=count($realdata_array);

	$items_array=array();
	for ($i=0; $i < $length; $i++) { 
		$j=1;
		$student_data_length=count($realdata_array[$i]);
		while ($j < $student_data_length-1) {
			$items_array[$i][]=$realdata_array[$i][$j-1];
			$j=$j+2;
		}
	}
	//var_dump($items_array);

	//$itemarray=array();                
	$itemarray=array_values($items_array); //$itemarray数组是一个二维数组，存放的是所有题库试题号，用于统计每道试题的答题人数
	//var_dump($itemarray);    //所有答题记录中的试题
	$totalitem=array();
	$totalitemlength=count($itemarray);
    for($i=0;$i<$totalitemlength;$i++){
    	$len=count($itemarray[$i]);
    	for ($j=0; $j < $len; $j++) { 
    		$totalitem[]=$itemarray[$i][$j];
    	}
    }
    unset($itemarray);
    unset($items_array);
    //$totalitem存放的是所有试题，包括重复的试题
	$itemnum=array_count_values($totalitem);//$itemnum存放的是所有试题出现的次数，key是题号，value是出现次数
	//var_dump($itemnum);  //所有试题在答题记录中出现的次数
	$length1=count($itemnum);  //$itemnum表示整个题库一                            共有多少试题
	//var_dump($length1);   
	$itemkey=array_keys($itemnum); //该数组存放的是所有试题的题号


	//下面要统计所有试题的答错次数，用来为后续难度计算做准备
	$wrongitem=array(); 
	$j=1;
	for ($i=0; $i < $length; $i++) { 
		$student_data_length=count($realdata_array[$i]);
		while ($j < $student_data_length) {
			if ($realdata_array[$i][$j]<0) {
				$wrongitem[$i][]=$realdata_array[$i][$j-1];
			}
		$j=$j+2;
		}
		$j=1;
	}
	//$wrongarray=array();
	$wrongarray=array_values($wrongitem); 
	//var_dump($wrongarray);
	$wrongitemlength=count($wrongarray);
	$totalwrongitem=array();
    for($i=0;$i<$wrongitemlength;$i++){
    	$len=count($wrongarray[$i]);
    	for ($j=0; $j < $len; $j++) { 
    		$totalwrongitem[]=$wrongarray[$i][$j];
    	}
    }//$totalitem存放的是所有试题，包括重复的试题
	$wrongnum=array_count_values($totalwrongitem); //$wrongnum存放的是学生试题的答错次数
	//var_dump($wrongnum);
	$wrongkey=array_keys($wrongnum);
	//var_dump($wrongkey);   //该数组存放的是所有答错试题的题号

	unset($totalwrongitem);
	unset($wrongarray);
	unset($realdata_array);


	//下面求试题的难度值，用学生试题答错次数除以对应试题的答题次数
	//$itemlength=count($wrongnum); //求答错试题的所有试题数量
	$difficulty=array();
	$no=1;
	$sql="DELETE FROM `difficulty`";
	mysqli_query($link,$sql);
	$sql1="INSERT INTO `difficulty`(`no`, `item`, `difficulty`) VALUES";
	$sql2="INSERT INTO `difficulty`(`no`, `item`, `difficulty`) VALUES";
	for ($i=0; $i < $length1; $i++) { 
		if (in_array($itemkey[$i], $wrongkey)) {
			$key=$itemkey[$i];
			$wrong=$wrongnum[$key];
			$itemnumber=$itemnum[$key];
			$difficulty[$key]=sprintf("%.4f", $wrong/$itemnumber);
			$sql1.="('$no','$key','$difficulty[$key]'),";
		}else{
			$key=$itemkey[$i];
			$difficulty[$key]=0;
			$sql2.="('$no','$key','$difficulty[$key]'),";
			}
			$no++;
		}
	$sql1 = substr($sql1,0,strlen($sql1)-1);
	$sql2 = substr($sql2,0,strlen($sql2)-1);
	mysqli_query($link,$sql1);
	mysqli_query($link,$sql2);
	echo "试题难度计算成功";
	unset($wrongkey);
	unset($wrongnum);
	$endtime=time();
	$interval=$endtime-$start;
	echo "运行时间为".$interval;
?>
</html>