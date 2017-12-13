<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
<title>组卷试题选择</title>
</head>
<body>
	<?php
	$starttime=time();
	include "config/dbconnect.php";
	set_time_limit(0); //运行时间持续到程序运行结束为止
	error_reporting( E_ALL&~E_NOTICE );
	
	if (!empty($_POST)) {
	$papernum=$_POST['papernum'];    //$papernum为总题量
	}
	//var_dump($papernum);
	//将各个知识点下面的试题取出来放入各自的数组
	//先取出有多少个章节，然后按照章节去取出各个章节的最小相关度集合
	$sql="SELECT `chapter` FROM `knowledge`";
	$result=mysqli_query($link,$sql);
	$length=mysqli_affected_rows($link);
	//var_dump($length);    //$length数组中存放的是所有章节，就是有多少章节
	for ($i=0; $i < $length; $i++) { 
		$chapter=mysqli_fetch_row($result);
		//var_dump($chapter[0]);
		$sql1="SELECT `item` FROM `lowitemknowledge` WHERE `chapter`='$chapter[0]'";
		$result1=mysqli_query($link,$sql1);
		$length1=mysqli_affected_rows($link);
		for ($j=0; $j <$length1 ; $j++) { 
			$item=mysqli_fetch_row($result1);
			$chap[$chapter[0]][]=$item[0];
		}
	}
	//var_dump($chap);  //数组$chap中的key表示各个章节号，value表示对应章节的最小相关度集合
	$lenchap=count($chap);  //一共有多少章节
	//出卷为了知识点更加全面，将知识点均分题量，当不足1题时以1题计算，然后判断各个难度区间是否满足，当难度区间已满则跳过，直到题量满足要求
	$section1=[];
	$section2=[];
	$section3=[];
	$section4=[];
	$section5=[];
	$section6=[];   //这些集合是6个难度区间
	$point=[];   //用来判断各个章节下面添加了多少试题，比较，有没有超过设定值
	$select=[];   //所有选择的试题，用来求相关度和
	$items=[];   //每个章节依次选择了哪些题目

	$item1=floor(0.02*$papernum);   
	$item2=floor(0.14*$papernum);
	$item3=floor(0.34*$papernum);
	$item4=floor(0.34*$papernum);
	$item5=floor(0.14*$papernum);
	$item6=floor(0.02*$papernum);   //各个难度区间规定的题量

	$section1len=count($section1);
	$section2len=count($section2);
	$section3len=count($section3);
	$section4len=count($section4);
	$section5len=count($section5);
	$section6len=count($section6);

	$knowledgeitem=floor($papernum/$lenchap);   //每个知识点均分题量
	//每个知识点均分题量，就是每个知识点涉及到的题量相等，当不满足1题时以1题计算
	$chapterkey=array_keys($chap);   //所有章节
	//var_dump($knowledgeitem);

	//根据每个章节下面的试题，当难度区间未满并且该知识点的题量未满时加入，知识点题量满足之后就跳过该知识点，并且所有试题加起来不等于总题量时，后面在进行调整
    for ($i=0; $i < $length; $i++) {
    	$flag=0; 
    	$len=count($chap[$chapterkey[$i]]);
    	for ($j=0; $j < $len; $j++) { 
    		$lowitem=$chap[$chapterkey[$i]][$j];
    		$sql2="SELECT `item`, `difficulty` FROM `difficulty` WHERE `item`='$lowitem'";
    		$re=mysqli_query($link,$sql2);
			$diff=mysqli_fetch_row($re);

    		if ((($diff[1]>=0)&&($diff[1]<1/6))&&($section1len<$item1)) {
				if ($point[$chapterkey[$i]]<=$knowledgeitem) {
					$section1[]=$diff[0];
					$select[]=$diff[0];
					$flag=1;          //$flag用来防止所有区间都满时的最后一个试题在总题量还未满的情况下重复添加
					$point[$chapterkey[$i]]++;
					$items[$chapterkey[$i]][]=$diff[0];
					$degree1.=$diff[0].",";
				}
			}elseif ((($diff[1]>=1/6)&&($diff[1]<2/6))&&($section2len<$item2)) {
				if ($point[$chapterkey[$i]]<=$knowledgeitem) {
					$section2[]=$diff[0];
					$select[]=$diff[0];
					$flag=1;          //$flag用来防止所有区间都满时的最后一个试题在总题量还未满的情况下重复添加
					$point[$chapterkey[$i]]++;
					$items[$chapterkey[$i]][]=$diff[0];
					$degree2.=$diff[0].",";
				}
			}elseif ((($diff[1]>=2/6)&&($diff[1]<3/6))&&($section3len<$item3)) {
				if ($point[$chapterkey[$i]]<=$knowledgeitem) {
					$section3[]=$diff[0];
					$select[]=$diff[0];
					$flag=1;          //$flag用来防止所有区间都满时的最后一个试题在总题量还未满的情况下重复添加
					$point[$chapterkey[$i]]++;
					$items[$chapterkey[$i]][]=$diff[0];
					$degree3.=$diff[0].",";
				}
			}elseif ((($diff[1]>=3/6)&&($diff[1]<4/6))&&($section4len<$item4)) {
				if ($point[$chapterkey[$i]]<=$knowledgeitem) {
					$section4[]=$diff[0];
					$select[]=$diff[0];
					$flag=1;          //$flag用来防止所有区间都满时的最后一个试题在总题量还未满的情况下重复添加
					$point[$chapterkey[$i]]++;
					$items[$chapterkey[$i]][]=$diff[0];
					$degree4.=$diff[0].",";
				}
			}elseif ((($diff[1]>=4/6)&&($diff[1]<5/6))&&($section5len<$item5)) {
				if ($point[$chapterkey[$i]]<=$knowledgeitem) {
					$section5[]=$diff[0];
					$select[]=$diff[0];
					$flag=1;          //$flag用来防止所有区间都满时的最后一个试题在总题量还未满的情况下重复添加
					$point[$chapterkey[$i]]++;
					$items[$chapterkey[$i]][]=$diff[0];
					$degree5.=$diff[0].",";
				}
			}elseif ((($diff[1]>=5/6)&&($diff[1]<=1))&&($section6len<$item6)) {
				if ($point[$chapterkey[$i]]<=$knowledgeitem) {
					$section6[]=$diff[0];
					$select[]=$diff[0];
					$flag=1;          //$flag用来防止所有区间都满时的最后一个试题在总题量还未满的情况下重复添加
					$point[$chapterkey[$i]]++;
					$items[$chapterkey[$i]][]=$diff[0];
					$degree6.=$diff[0].",";
				}
			}else{
				$abandon[]=$diff[0];   //用来存放不符合区间的那些相关度低的试题用来后续调整用
			}
				$section1len=count($section1);
				$section2len=count($section2);
				$section3len=count($section3);
				$section4len=count($section4);
				$section5len=count($section5);
				$section6len=count($section6);
	    		if ($point[$chapterkey[$i]]==($knowledgeitem+1)) {
	    			break;
    			}
    		}
		}

	/*	var_dump($section1);
		var_dump($section2);
		var_dump($section3);
		var_dump($section4);
		var_dump($section5);
		var_dump($section6);*/
		//var_dump($lenchap);
		for ($i=0; $i <$lenchap ; $i++) {
			$selecteditem=array();
			$dif=array();
			$len1=count($items[$chapterkey[$i]]);
			for ($j=0; $j < $len1; $j++) { 
				$selecteditem[]=$items[$chapterkey[$i]][$j];  //为了保证array_diff的第二个参数为数组，不然报错
			}
			$dif=array_diff($chap[$chapterkey[$i]], $selecteditem);
			//var_dump($selecteditem[$chapterkey[$i]]);
			$otheritem[$chapterkey[$i]]=array_values($dif);
		}
		//var_dump($otheritem);


		for ($i=0; $i < $length; $i++) {
				$flag1=0;
				$len2=count($otheritem[$chapterkey[$i]]);
				if (($section1len+$section2len+$section3len+$section4len+$section5len+$section6len)==$papernum) {
		    			break;
		    	}
		    	for ($j=0; $j < $len2; $j++) { 
		    		$lowotheritem=$otheritem[$chapterkey[$i]][$j];
		    		$sql3="SELECT `item`, `difficulty` FROM `difficulty` WHERE `item`='$lowotheritem'";
		    		$re1=mysqli_query($link,$sql3);
					$diff1=mysqli_fetch_row($re1);

		    		if (($diff1[1]>=0)&&($diff1[1]<1/6)){
						$section1[]=$diff1[0];
						$flag1=1;
						$select[]=$diff1[0];
						$degree1.=$diff1[0].",";
					}elseif (($diff1[1]>=1/6)&&($diff1[1]<2/6)) {
						$section2[]=$diff1[0];
						$select[]=$diff1[0];
						$flag1=1;
						$degree2.=$diff1[0].",";
					}elseif (($diff1[1]>=2/6)&&($diff1[1]<3/6)) {
						$section3[]=$diff1[0];
						$select[]=$diff1[0];
						$flag1=1;
						$degree3.=$diff1[0].",";
					}elseif (($diff1[1]>=3/6)&&($diff1[1]<4/6)) {
						$section4[]=$diff1[0];
						$select[]=$diff1[0];
						$flag1=1;
						$degree4.=$diff1[0].",";
					}elseif (($diff1[1]>=4/6)&&($diff1[1]<5/6)) {
						$section5[]=$diff1[0];
						$select[]=$diff1[0];
						$flag1=1;
						$degree5.=$diff1[0].",";
					}elseif (($diff1[1]>=5/6)&&($diff1[1]<=1)) {
						$section6[]=$diff1[0];
						$select[]=$diff1[0];
						$flag1=1;
						$degree6.=$diff1[0].",";
					}else{
						$abandon[]=$diff1[0];   //用来存放不符合区间的那些相关度低的试题用来后续调整用
					}
						$section1len=count($section1);
						$section2len=count($section2);
						$section3len=count($section3);
						$section4len=count($section4);
						$section5len=count($section5);
						$section6len=count($section6);

					if ($flag1==1) {  //为了保证每次调整的时候涉及的知识点更加多
						break;
					}
				}
			}	
			echo "相关度低的试题在每个难度区间的分布如下："."<br>";
			echo "难度区间[0，,1/6)的试题为".$degree1."<br>";
			echo "难度区间[1/6，,2/6)的试题为".$degree2."<br>";
			echo "难度区间[2/6，,3/6)的试题为".$degree3."<br>";
			echo "难度区间[3/6，,4/6)的试题为".$degree4."<br>";
			echo "难度区间[4/6，,5/6)的试题为".$degree5."<br>";
			echo "难度区间[5/6，,1]的试题为".$degree6."<br>";
			echo "<hr>";

	/* 	sort($select);  //从小到大排列保证id1比id2小
	 	//var_dump($select);
	 	$coe=0;
	 	for ($i=0; $i <$papernum ; $i++) { 
	 		$sql="SELECT  `id1`, `id2`, `cal_rho` FROM `ex2_relevancy` WHERE `id1`='$select[$i]'";
	 		$result=mysqli_query($link,$sql);
	 		$length1=mysqli_affected_rows($link);
	 		for ($j=0; $j <$length1 ; $j++) { 
	 			$data=mysqli_fetch_row($result);
	 			if (in_array($data[1], $select)) {
	 				$coe=$coe+$data[2];
	 			}
	 		}
	 	}
	 	var_dump($coe);*/   //这段代码是为了求得该试卷平均相关度

		echo "组卷运行时间：";
		$endtime=time();
	    $a=$endtime-$starttime;
	    echo $a;             //代码运行时间

	?>
	</body>
	</html>
