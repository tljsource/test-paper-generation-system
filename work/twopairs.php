<html>
<head>
<title>获得试题对</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
</head>
<body>
<?php
$starttime=time();
include "config/dbconnect.php";
set_time_limit(0); //运行时间持续到程序运行结束为止
error_reporting( E_ALL&~E_NOTICE );
$dataform=mysqli_query($link,'select * from exercise2_ans_history'); //设定做题记录中前500条记录为旧做题记录，后面的记录为新增的做题记录
$record_total=mysqli_affected_rows($link);
for ($i=0; $i < 500; $i++) { 
	$jiu_record[$i]=mysqli_fetch_row($dataform);
}
//var_dump($jiu_record);

//该函数的作用是将数据库读取的记录变成只有错题，然后存入数组中
function wrongitems($record){	
		$columns=count($record[0]);
		$rows=count($record);
		for ($i=0; $i < $rows; $i++) {
			for ($j=3; $j < $columns; $j++) { 
		 		$record_data[$i][]=$record[$i][$j];
		 	} 
		}
		$length=count($record_data);
		$j=1;
		for ($i=0; $i < $length; $i++) { 
			$data_length=count($record_data[$i]);
			while ($j < $data_length) {
				if ($record_data[$i][$j]<0) {
					$items_wrong_array[$i][]=$record_data[$i][$j-1];
				}
			$j=$j+2;
			}
			$j=1;
		}            
		$wrongitems=array_values($items_wrong_array); 
		return $wrongitems;
	}

$jiu_wrongitems=wrongitems($jiu_record);//该数组存放的是所有做错的记录

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

	$jiuwrongitems_length=count($jiu_wrongitems);
	for ($i=0; $i <$jiuwrongitems_length; $i++) { 
		$twopairs_jiu[$i] = Combination($jiu_wrongitems[$i], 2);         
		echo '<pre>';
	}

	    $i=0;
		$twopairs_jiu_sum=array();  
		while ($i<$jiuwrongitems_length) {
			$twopairs_jiu_sum=array_merge($twopairs_jiu_sum,$twopairs_jiu[$i]); 
			$i++;
		}              //以上是为了把所有的错题对都放入一个数组中,这边的错题对是有重复对的

	$twopairs_jiu_num=array_count_values($twopairs_jiu_sum);  //不重复的各个试题对的做错次数
	$twopairs_jiu_keys=array_keys($twopairs_jiu_num);     //所有做错的试题对,存放格式不正确
	$twopairsjiukeys=$twopairs_jiu_keys;   //用于后面和新增的比较
	$twopairsnumjiu=count($twopairs_jiu_num);

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
	
	$twopairs_num=count($twopairs_jiu_keys);
	for ($i=0; $i < $twopairs_num; $i++) { 
		if (strlen($twopairs_jiu_keys[$i])>3) {
			$twopairs_jiu_keys[$i]=twopairskeys($twopairs_jiu_keys[$i]); 
		}
	}
	$twopairs_jiukeys=$twopairs_jiu_keys;   //$twopairs_jiukeys为存放真实试题号的数组
	$twopairs_jiuvalues=array_values($twopairs_jiu_num);//存放每一个试题对的数量
	
//$twopairs_jiukeys此时存放的是两个题号以及一个逗号，对应做错次数的数组为$twopairs_jiu_num，$twopairs_jiu_num的键值是次数，索引是试题对


	//以下函数是为了得到做题记录中所有的错题对应的做错次数
	function wrongitems_sum($record){
		$columns=count($record[0]);
		for ($i=0; $i < 500; $i++) {
			for ($j=3; $j < $columns; $j++) { 
		 		$realdata_array[$i][]=$record[$i][$j];
		 	} 
		}
		return $realdata_array;
	}
	$wrongitems_total=wrongitems_sum($jiu_record);

	function array_multi2single($array)     //该函数是为了将最终的二维数组$realdata_array合成一个一维数组
	{  
    	//首先定义一个静态数组常量用来保存结果  
    	static $result_array = array();  
    	//对多维数组进行循环  
    	foreach ($array as $value) {  
        //判断是否是数组，如果是递归调用方法  
        	if (is_array($value)) {  
            	array_multi2single($value);  
        	} else  //如果不是，将结果放入静态数组常量  
            	$result_array [] = $value;  
    	}  
    	//返回结果（静态数组常量）  
    	return $result_array;  
	}  
	$wrongitems_total_array=array_multi2single($wrongitems_total);//$wrongitems_total_array将所有数据放到一个数据里面

	function wrong_items_num($wrongitems_total_array){
		$k=1;
		$total_length=count($wrongitems_total_array);
			while ($k <= $total_length) {
				if ($wrongitems_total_array[$k]<0) {
					$wrong_id_sum[]=$wrongitems_total_array[$k-1];
				}
				$k=$k+2;
			}
		$wrong_num_fun=array_count_values($wrong_id_sum);
		return $wrong_num_fun;
	}
	$wrong_num=wrong_items_num($wrongitems_total_array);  //此时的$wrong_num存放的是所有做错的题目的次数
	$wrongkeys=array_keys($wrong_num);  //存放的是错有错题的题号
	$wrongvalues=array_values($wrong_num);   //存放的是错有错题的题号对应错的次数
	$wrongnum=count($wrong_num);


	//下面是要把旧的试题对的次数放入数据表中
		$sql10="DELETE FROM `twopairs_items`";
		mysqli_query($link,$sql10);
		$select=mysqli_query($link,'select * from twopairs_items'); 
		$sign=mysqli_fetch_array($select);
		if ($sign[group]=='') {
			$sql= "insert into `twopairs_items` (`no`,`id1`,`id2`,`group`,`time`) values";
			for($i=0;$i<$twopairs_num;$i++){
				$no=$i+1;
				$items1=$twopairs_jiukeys[$i][0];
				$items2=$twopairs_jiukeys[$i][2];
				$pairs=$twopairs_jiuvalues[$i];
				$timetwo=date('y-m-d h:i:s',time());
				$sql.="('$no','$items1','$items2','$pairs','$timetwo'),";
			}
			$sql = substr($sql,0,strlen($sql)-1);
			mysqli_query($link,$sql);
		}


		//下面是要把旧的单个试题错的次数放入数据表中
		//为了加快数据表的插入速率，先删除整个表再重新插入
		$sql9="DELETE FROM `items_single_num`";
		mysqli_query($link,$sql9);
		$select1=mysqli_query($link,'select * from items_single_num'); 
		$sign1=mysqli_fetch_array($select1);
		if ($sign1[item]=='') {
		$sql1= "insert into `items_single_num`(`no`,`item`,`number`,`time`) values";
		for($i=0;$i<$wrongnum;$i++){
			$no=$i+1;
			$items=$wrongkeys[$i];
			$number=$wrongvalues[$i];
			$timesingle=date('y-m-d h:i:s',time());
		$sql1.="('$no','$items','$number','$timesingle'),";
		}
		$sql1 = substr($sql1,0,strlen($sql1)-1);
		mysqli_query($link,$sql1);
	}

	

	//取出新增数据，首先是计算新增记录中的单个错题的次数，然后与旧的做题记录的次数相加
	//以下部分是对新增做题记录的同样处理
	for ($i=500; $i < $record_total; $i++) { 
		$new_record[]=mysqli_fetch_row($dataform);    //$new_record数组中存放的是新增记录的所有字段值
	}
	$new_wrongitems=wrongitems($new_record);//该数组存放的是所有新增记录中的做错的记录

	//以下是得到各个新增记录的所有试题对，但是存放的试题格式不和实际情况
	$newwrongitems_length=count($new_wrongitems);
	for ($i=0; $i <$newwrongitems_length; $i++) { 
		$twopairs_new[$i] = Combination($new_wrongitems[$i], 2);         
		echo '<pre>';
	}

	    $i=0;
		$twopairs_new_sum=array();  
		while ($i<$newwrongitems_length) {
			$twopairs_new_sum=array_merge($twopairs_new_sum,$twopairs_new[$i]); 
			$i++;
		}              //以上是为了把所有的错题对都放入一个数组中,这边的错题对是有重复对的

	$twopairs_new_num=array_count_values($twopairs_new_sum);  //不重复的各个试题对的做错次数
	$twopairs_new_keys=array_keys($twopairs_new_num);     //所有做错的试题对,存放格式不正确
	$twopairsnewkeys=$twopairs_new_keys;//用于后面类似哈希的计算


	$twopairs_num_new=count($twopairs_new_keys);
	for ($i=0; $i < $twopairs_num_new; $i++) { 
		if (strlen($twopairs_new_keys[$i])>3) {
			$twopairs_new_keys[$i]=twopairskeys($twopairs_new_keys[$i]); 
		}
	}
	$twopairs_newkeys=$twopairs_new_keys;   //$twopairs_jiukeys为存放真实试题号的数组

	$twopairs_newvalues=array_values($twopairs_new_num);//存放新增做题记录的每一个试题对的数量即做错次数

	//将新增记录的试题对与旧试题对匹配起来，对应上则update数据库中的试题对
	$j=0;
	$new_emergencyitems=array();   //用来存放新出现的试题对及次数，放在原有旧的试题对的后面，防止顺序乱掉
	$sql4= "insert into `twopairs_items` (`no`,`id1`,`id2`,`group`,`time`) values";
	for ($i=0; $i <$twopairs_num_new ; $i++) { 
		if (in_array($twopairsnewkeys[$i],$twopairsjiukeys)) {
		$twopairs_jiu_num[$twopairsnewkeys[$i]]=$twopairs_jiu_num[$twopairsnewkeys[$i]]+$twopairs_new_num[$twopairsnewkeys[$i]];
		$time1=date('y-m-d h:i:s',time());
		$group=$twopairs_jiu_num[$twopairsnewkeys[$i]];
		$id1=$twopairs_newkeys[$i][0];
		$id2=$twopairs_newkeys[$i][2];
		$sql3="UPDATE `twopairs_items` SET `group`='$group',`time`='$time1' where `id1`='$id1' and `id2`='$id2'";
		mysqli_query($link,$sql3);
		} 
		else {
			$new_emergencyitems[$twopairsnewkeys[$i]]=$twopairs_newvalues[$i];
			$pairs=$new_emergencyitems[$twopairsnewkeys[$i]];
			$no=$twopairsnumjiu+$j+1;
			$items1=$twopairs_newkeys[$i][0];
			$items2=$twopairs_newkeys[$i][2];
			$time2=date('y-m-d h:i:s',time());
			$sql4.="('$no','$items1','$items2','$pairs','$time2'),";
			$j++;
		}
	}
		$sql4 = substr($sql4,0,strlen($sql4)-1);
		$pairs_new=mysqli_query($link,'select * from twopairs_items'); 
		$pairs_new_length=mysqli_affected_rows($link);
		if ($pairs_new_length<=$twopairsnumjiu) {
			mysqli_query($link,$sql4);
		}



	//下面是把新增的单个试题与旧记录中的单试题次数相加，不出现的试题添加
	$wrongitems_new=wrongitems($new_record);
	$wrongitemsnum=count($wrongitems_new);
	for ($i=0; $i < $wrongitemsnum; $i++) { 
		$columns_new=count($wrongitems_new[$i]);
		for ($j=0; $j < $columns_new; $j++) { 
			$wrongitems_total_array_new[]=$wrongitems_new[$i][$j];
		}
	}
	$wrong_num_new=array_count_values($wrongitems_total_array_new);
	$wrongnum_new=count($wrong_num_new);
	$new_wrongitems_keys=array_keys($wrong_num_new);
	$new_wrongitems_values=array_values($wrong_num_new);
	$j=0;
	$sql6= "insert into `items_single_num` (`no`,`item`,`number`,`time`) values";
	for ($i=0; $i < $wrongnum_new; $i++) { 
		if (in_array($new_wrongitems_keys[$i], $wrongkeys)) {
			$wrong_num[$new_wrongitems_keys[$i]]=$wrong_num[$new_wrongitems_keys[$i]]+$new_wrongitems_values[$i];
			$number=$wrong_num[$new_wrongitems_keys[$i]];
			$time3=date('y-m-d h:i:s',time());
			$item=$new_wrongitems_keys[$i];
			$sql5="UPDATE `items_single_num` SET `number`='$number',`time`='$time3' where `item`='$item'";
			mysqli_query($link,$sql5);
		}
		else {
			$no=$wrongnum+$j+1;
			$number=$new_wrongitems_values[$i];
			$item=$new_wrongitems_keys[$i];
			$time4=date('y-m-d h:i:s',time());
			$sql6.="('$no','$item','$number','$time4'),";
			$j++;
		}
	}
	$sql6 = substr($sql6,0,strlen($sql6)-1);
	$wrong_new=mysqli_query($link,'select * from items_single_num'); 
	$wrong_new_length=mysqli_affected_rows($link);
	if ($wrong_new_length<=$wrongnum) {
		mysqli_query($link,$sql6);
	}

	//下面是根据试题对表以及单个试题表求得试题对间的相关系数
	$pairsform=mysqli_query($link,'select * from twopairs_items'); 
	$length_pairs=mysqli_affected_rows($link);
	for ($i=0; $i < $length_pairs; $i++) { 
		$a[$i]=mysqli_fetch_row($pairsform);
		$pairsitemtotal[$i][0]=$a[$i][1];
		$pairsitemtotal[$i][1]=$a[$i][2];
		$pairsitemtotal[$i][2]=$a[$i][3];
	}

	$wrongitemform=mysqli_query($link,'select * from items_single_num'); 
	$length_wrongitem=mysqli_affected_rows($link);
	for ($i=0; $i < $length_wrongitem; $i++) { 
		$b[$i]=mysqli_fetch_row($wrongitemform);
		$wrongitemtotal[$i]=$b[$i][2];
		$wrongitemtag[$i]=$b[$i][1];
	}

	for ($i=0; $i <$length_pairs ; $i++) {
		$j=0;
		$count=0;
		while ( ($count<=1)&&($j<=1)) {
			for ($k=0; $k < $length_wrongitem; $k++) { 
				if ($pairsitemtotal[$i][$j]==$wrongitemtag[$k]) {
					$single_num_array[$i][]=$wrongitemtotal[$k];  
				}
			}
		$j=$j+1;
		$count++;
		}
	}
   
	$endtime=time();
	$a=$endtime-$starttime;
	echo "<br>";
	echo "运行时间为".$a;
?>
</body>
</html>