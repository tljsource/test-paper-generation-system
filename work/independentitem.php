<html>
<?php
$start=time();
ini_set("memory_limit","-1");
include "config/dbconnect.php";
set_time_limit(0); //运行时间持续到程序运行结束为止
error_reporting( E_ALL&~E_NOTICE );
$dataform=mysqli_query($link,'select * from exercise2_ans_history'); 
$rows=mysqli_affected_rows($link);
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
		while ($j < $student_data_length) {
				if ($realdata_array[$i][$j]<0) {
					$itemswrongarray[$i][]=$realdata_array[$i][$j-1];
				}
			$j=$j+2;
			}
			$j=1;
		while ($j < $student_data_length-1) {
			$items_array[$i][]=$realdata_array[$i][$j-1];
			$j=$j+2;
		}
	}
	//var_dump($itemswrongarray);
	//var_dump($items_array);  //$items_array数组中存放的是所有记录中的试题题号，包括了正确的和错误的试题
	unset($dataform);
	unset($data_final_array);
	unset($realdata_array);
	//下面要把重复的试题去掉，将上述$items_array中的试题合成一个数组，然后两两组合，最后去掉那些数据表已经有的试题对，其他的默认为0
	for ($i=0; $i < $rows; $i++) {
		$length1=count($items_array[$i]);
		for ($j=0; $j <$length1 ; $j++) { 
			$combine[]=$items_array[$i][$j];
		}
	}
	//var_dump($combine);   //$combine数组中存放的是所有答题记录中涉及到的试题，此时试题有重复
	$keyvalue=array_count_values($combine);
	$key=array_keys($keyvalue);  //$key数组中都是不重复的试题题号

		//以下这个函数是为了得到两两试题对
function Combination($sort, $num)
{
    $result = $data = array();
    if( $num == 1 ) {
        return $sort;
    }
    foreach( $sort as $k=>$v ) {
        unset($sort[$k]);
        $data   = Combination($sort,$num-1);
        foreach($data as $row) {
            $result[] = $v.','.$row;  
        }
    }
    return $result;
}

	$twopairs=Combination($key, 2);     //$twopairs为所有题库试题的两两组合
	//var_dump($twopairs);

	//由于组合的时候可能会出现前一个试题号大于后一个试题号，这样会导致后面已经存在的不为0的那些试题对重复出现，因此这里先把所有组合的试题位置换一下
	$length2=count($twopairs);
	for ($i=0; $i < $length2; $i++) { 
		if (strlen($twopairs[$i])>3) {
			$twopairs[$i]=twopairskeys($twopairs[$i]); 
			if ($twopairs[$i][0]>$twopairs[$i][2]) {
				$twopairs[$i]=$twopairs[$i][2].','.$twopairs[$i][0];
			}else{
				$twopairs[$i]=$twopairs[$i][0].','.$twopairs[$i][2];
			}
		}elseif ($twopairs[$i][0]>$twopairs[$i][2]) {
			$twopairs[$i]=$twopairs[$i][2].','.$twopairs[$i][0];
		}
	}
	//var_dump($twopairsnew);
	
	unset($keyvalue);
	unset($key);
	unset($combine);

	$sql1="SELECT `id1`, `id2` FROM `ex2_relevancy`";
	$result2=mysqli_query($link,$sql1);
	$len=mysqli_affected_rows($link);
	for ($i=0; $i <$len ; $i++) { 
		$data=mysqli_fetch_row($result2);
		$twopairswrongtotal[$i]=$data[0].','.$data[1];
	}
	//var_dump($twopairswrongtotal);

	//下面比较所有试题对与错题试题对，将差集默认为0插入数据表
	$default0=array_diff($twopairs,$twopairswrongtotal);
	//var_dump($default0);
	unset($twopairs);
	unset($twopairswrongtotal);
	$keys=array_keys($default0);
	//var_dump($keys);
	$twopairs_num=count($default0);
	for ($i=0; $i <$twopairs_num ; $i++) { 
		$default[$i]=$default0[$keys[$i]];
	}
	//var_dump($default);   //差集保持原来的索引，要改变索引号

//以下函数是为了把试题对的数组存放形式为两个试题号与逗号隔开，而不是按个存放，比如13题分成1和3存放
	function twopairskeys($twopairs_keys) {
	$j=0;
	$twopairs_keys_column=strlen($twopairs_keys);
	if ($twopairs_keys_column>3) {
		$tt=array();
		$ss=array();
		$kk=array();
			while ( $twopairs_keys[$j]!=',') {
				$tt[]=$twopairs_keys[$j];
				$j=$j+1;
			}
			$s=implode($tt);

			for($k=$j+1;$k<$twopairs_keys_column;$k++){
				$ss[]=$twopairs_keys[$k];
			}
			$t=implode($ss);

			array_push($kk,$s,',',$t);
			$twopairs_keys=$kk;	
			return $twopairs_keys;
		}
	}
	for ($i=0; $i < $twopairs_num; $i++) { 
		if (strlen($default[$i])>3) {
			$default[$i]=twopairskeys($default[$i]); 
		}
	}
	//var_dump($default);   //$default0数组中的都是默认为0的试题对
	unset($default0);

	$no=$len;
	for ($j=0; $j < 50; $j++) { 
		$sql="INSERT INTO `ex2_relevancy`(`no`, `id1`, `id2`, `cal_rho`,`fixed_rho`, `current_supporter`) VALUES";
		for ($i=$j*$twopairs_num/50; $i < (1+$j)*$twopairs_num/50; $i++) { 
			$no++;
			$TA=$default[$i][0];
			$TB=$default[$i][2];
			$result1=0;
			if ($TA<$TB) {
				$sql.="('$no','$TA','$TB','$result1','$result1','1'),";
			}else{
				$sql.="('$no','$TB','$TA','$result1','$result1','1'),";
			}
		}
		$sql=substr($sql,0,strlen($sql)-1);
		//var_dump($sql);
		mysqli_query($link,$sql);
	}
	$sql2="DELETE FROM `ex2_relevancy` WHERE `cal_rho`<0";
	mysqli_query($link,$sql2);  
	
	$endtime=time();
	$gap=$endtime-$start;
	echo "运行时间为".$gap;
?>
</body>
</html>