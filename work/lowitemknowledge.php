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
    $sql="SELECT `chapter` FROM `knowledge`";
    $data=mysqli_query($link,$sql);
    $length=mysqli_affected_rows($link);
    for ($i=0; $i <$length ; $i++) { 
        $chap=mysqli_fetch_row($data);
        $chapter[]=$chap[0]; //此时数组$chapter中存放的都是比例，其中key是章节（知识点），values是对应的知识点比例值
    }
    //var_dump($chapter);   //现在数据库中存在的所有章节
    $chapvalue=array_count_values($chapter);
    $chapter=array_keys($chapvalue);
    $len2=count($chapter);
   // var_dump($chapter);
    //每个知识点之间的试题是无关的，因此只考虑找到同一个知识点下面的相关度为0的那些试题放入集合中，然后与不同知识点相同集合合并，最终出题从集合中出，这样的相关性肯定最小。
    $sql4="DELETE FROM `lowitemknowledge`";
    mysqli_query($link,$sql4);
    $sql3="INSERT INTO `lowitemknowledge`(`item`, `chapter`) VALUES";
    for ($k=0; $k< $len2; $k++) { 
        $itemsarray=array();
        $scope="";
        $coe=array();
        $root=array();
        $itemcoe=array();
        $sql1="SELECT `item` FROM `knowledge` WHERE `chapter`='$chapter[$k]'";
        $result=mysqli_query($link,$sql1);
        $length1=mysqli_affected_rows($link);
        //var_dump($length1);
        for ($j=0; $j < $length1; $j++) { 
            $items=mysqli_fetch_row($result);
            $itemsarray[]=$items[0];   // $itemsarray存放的是所有知识点下面的试题
            $scope.="$itemsarray[$j],";
        }
        $scope= substr($scope,0,strlen($scope)-1);
        //var_dump($scope);
    //下面要取出所有该知识点下面的相关度为0的试题对 
        $sql2="SELECT `id1`, `id2`,`cal_rho` FROM `ex2_relevancy` WHERE `id1` IN "."(".$scope.")"."and `id2` IN "."(".$scope.")";
        $result1=mysqli_query($link,$sql2);
        $length2=mysqli_affected_rows($link);
        for ($j=0; $j < $length2; $j++) { 
            $coe[]=mysqli_fetch_row($result1);
            $itemcoe[]=$coe[$j][2];
        }
        //var_dump($coe);
        asort($itemcoe);
        $key=array_keys($itemcoe);
       //var_dump($key[0]);        //每次循环该数组中都是相关度为0，并且也是同一个知识点下面的试题对
       $root[]=$coe[$key[0]][0];
       $root[]=$coe[$key[0]][1];
       for ($j=0; $j < $length1-2; $j++) { 
        $key=array();
        $swap=array();
           for ($i=1; $i < $length2; $i++) { 
               if(in_array($coe[$i][0],$root) && !in_array($coe[$i][1],$root)){
                        if(!isset($swap[$coe[$i][1]])) $swap[$coe[$i][1]] = 0;
                        $swap[$coe[$i][1]] += $coe[$i][2];
                    }
                    if(in_array($coe[$i][1],$root) && !in_array($coe[$i][0],$root)){
                        if(!isset($swap[$coe[$i][0]])) $swap[$coe[$i][0]] = 0;
                        $swap[$coe[$i][0]] += $coe[$i][2];
                    }
                }
                //var_dump($swap);
                asort($swap);
                $key=array_keys($swap);
                $root[]=$key[0];
            }
            //var_dump($root);
            //echo "<hr>";
            //break;
        $len1=count($root);
        for ($j=0; $j < $len1; $j++) {
            if (!empty($root[$j])) {
                $sql3.="('$root[$j]','$chapter[$k]'),";
            }
        }

       //每个章节都找到最小相关度集合，然后依次放入数据表中
       //var_dump($root);
    }
    $sql3=substr($sql3,0,strlen($sql3)-1);
    mysqli_query($link,$sql3);

        echo "组卷运行时间：";
        $endtime=time();
        $a=$endtime-$starttime;
        echo $a;             //代码运行时间
    ?>
    </body>
    </html>

