<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>组卷首页</title>
<link href="/img/css.css" rel="stylesheet" type="text/css" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
</head>

<body class="textc">
<ul class="topmenu">
	<strong>组卷系统导航:</strong>
</ul>
<br/>
<div class="hbox center"">
<?php
    error_reporting( E_ALL&~E_NOTICE );
	$number=$_GET['number'];
    echo "<a href='twopairs.php'>";
    echo "<img src='images/icon_nav_button.png' alt=''>";
    echo "</div>";
    echo "<p>获得试题答错次数及试题对出现次数</p>";
    echo "</a>";

    echo "<a href='coefficient.php'>";
    echo "<img src='images/icon_nav_button.png' alt=''>";
    echo "</div>";
    echo "<p>计算试题对相关度</p>";
    echo "</a>";

    echo "<a href='independentitem.php'>";
    echo "<img src='images/icon_nav_button.png' alt=''>";
    echo "</div>";
    echo "<p>所有相关度默认为0的试题对</p>";
    echo "</a>";

    echo "<a href='itemdifficulty.php'>";
    echo "<img src='images/icon_nav_button.png' alt=''>";
    echo "</div>";
    echo "<p>在线计算试题难度</p>";
    echo "</a>";

    echo "<a href='knowledgepoint.php'>";
    echo "<img src='images/icon_nav_button.png' alt=''>";
    echo "</div>";
    echo "<p>获得所有试题知识点</p>";
    echo "</a>";

    echo "<a href='lowitemknowledge.php'>";
    echo "<img src='images/icon_nav_button.png' alt=''>";
    echo "</div>";
    echo "<p>各个知识点最低相关度集合</p>";
    echo "</a>";

	echo "<a href='form.php'>";
    echo "<img src='images/icon_nav_button.png' alt=''>";
    echo "</div>";
    echo "<p>基于试题相关性的试题选择</p>";
    echo "</a>";
?>
</div>
<div class="hboxfoot center"></div>
<div class="clear"></div>
</body>
</html>