<?php
/**
 * Created by Yang xunwu.
 * User: mao
 * Date: 2016/3/19
 * Time: 14:55
 */

//将Unicode编码为UTF8
function UnicodeToUTF8($unicode)
{
	//匹配所有字符和\u标识的unicode码
	$pattern = '/([\w]+)|(\\\u([\w]{4}))/i';

	preg_match_all($pattern, $unicode, $matches);
	if (!empty($matches))
	{
		$name = '';
		for ($j = 0; $j < count($matches[0]); $j++)
		{
			$str = $matches[0][$j];
			//将unicode码转换为utf-8编码字符其他正常字符不变
		
			if (strpos($str, '\\u') === 0)
			{
				$code = base_convert(substr($str, 2, 2), 16, 10);
				$code2 = base_convert(substr($str, 4), 16, 10);
				$c = chr($code).chr($code2);
				$c = iconv('UCS-2', 'UTF-8', $c);
				$name .= $c;
			}
			else
			{
				$name .= $str;
			}
		}
	}

	return $name;
}

/**
 * @param $URLContainer array 返回的URLContainer
 * @param $fileName string 文件名
 * @param $title string 标题
 */
function URLContainerTOHTML($URLContainer, $fileName, $title)
{
	$total = count($URLContainer);
	$keys = array_keys($URLContainer);
	$content = '';
	for($idx = 0; $idx < $total; $idx++)
	{
		$key = $keys[$idx];
		$url = explode('"', $key)[0];
		$content .= "<a href ='".$url."'>".$URLContainer[$key][1]."&nbsp;&nbsp;赞&nbsp;&nbsp;&nbsp;&nbsp;".$URLContainer[$key][0]."</a><br/>";
	}

	include 'D:\ZhiHuSpider\ZhiHuSpider/Template/Template.php';
	echo "写入文件".$fileName."\n";
	file_put_contents('D:\ZhiHuSpider\ZhiHuSpider/OutputFile/'.iconv('UTF-8', 'gb2312', $fileName).'.html', $HTML);
}


/**
 * @param $URLContainer array 返回的URLContainer
 * @param $DBHandle resource mysqli句柄
 */
function URLContainerTODB($URLContainer, $DBHandle)
{
	$total = count($URLContainer);
	$keys = array_keys($URLContainer);

	for($idx = 0; $idx < $total; $idx++)
	{
		$key = $keys[$idx];
		$sql = "insert answers(url, title, votes) values('".$key."','".$URLContainer[$key][0]."','".$URLContainer[$key][1]."');";
		$DBHandle -> query($sql);
	}
	echo "已写入 $total 条数据 \n";
}
