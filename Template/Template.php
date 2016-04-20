<?php
/**
 * Created by Yang xunwu.
 * User: mao
 * Date: 2016/3/19
 * Time: 14:54
 */
	$HTML = "
<!DOCTYPE html>
<html>
<head>
	<meta charset='utf-8'>
	<style type='text/css'>
	body {
		width: 100%;
		padding: 0;
		margin: 0;
		background-color: rgb(236, 239, 241)
	}

	.title {
		background: rgb(52, 73, 94);
		position: fixed;
		width: 100%;
		height: 60px;
	}

	.title h3 {
		color: white;
		padding-left:20%;
	}

	.wrap {
		padding-top: 100px;
	}

	.content {
		padding-top: 20px;
		margin-left: 20%;
		background-color: #F7F9F9;
		width: 60%;
		font-family: 'Microsoft Yahei', sans-serif;
	}

	a {
		font: 15px/1.2em 'Microsoft YaHei',Arial,Helvetica,sans-serif;
		padding-top:10px;
		padding-left:30px;
		color: #333;
		text-decoration:none;
		display:block;

	}

	a:hover {
		color:#017e66;
	}

	</style>
</head>
	<body>
		<div class='title'><h3>".$title."</h3></div>
		<div class='wrap'>
			<div class='content'>".$content."</div>
		</div>
	</body>
</html>";

