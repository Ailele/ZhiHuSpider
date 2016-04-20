<?php
/**
 * Created by Yang xunwu.
 * User: mao
 * Date: 2016/3/19
 * Time: 14:46
 */

namespace ZhiHuSpider\ZhiHuURL;


class ZhiHuURLContainer
{
	protected $Container;
	protected $totalURLNum;

	public function __construct()
	{
		$this -> Container = array();
		$this -> totalURLNum = 0;
	}

	public function getURLContainer()
	{
		return $this -> Container;
	}

	public function getTotalNum()
	{
		return $this -> totalURLNum;
	}

	public function clean()
	{
		$this -> Container = array();
		$this -> totalURLNum = 0;
	}

	/**
	 * @param $matches array() [1]=>url数组  [2]=>标题数组  [3]=>票数数组
	 * @param $votes int 票数阈值
	 * @return int 返回添加了得数量
	 */
	public function addURL($matches, $votes)
	{
		$total = count($matches[0]);

		for ($i=0; $i < $total; $i++) {
			if ($matches[3][$i] >= $votes)
			{
				/**
				 * 部分链接是https://www.zhihu.com开始的,部分是/question开始的，统一链接为绝对地址https://www.zhihu.com
				 */
				if (preg_match("#/question.*#", $matches[1][$i]))
				{
					$this -> Container['https://www.zhihu.com'.$matches[1][$i]] = array($matches[2][$i], $matches[3][$i]);
				}
				else
				{
					$this -> Container[$matches[1][$i]] = array($matches[2][$i], $matches[3][$i]);
				}
				$this -> totalURLNum ++;
			}
			else
			{
				continue;
			}
		}
		return $total;
	}

	public function adXHRdURL($matches, $votes)
	{
		$total = count($matches[0]);

		for ($i=0; $i < $total; $i++) {
			if ($matches[3][$i] >= $votes && !empty($matches[1][$i]) && !empty($matches[2][$i]) && !empty($matches[3][$i]) )
			{
				$matches[1][$i] = stripslashes($matches[1][$i]);
				if (preg_match("#/question.*#", $matches[1][$i]))
				{
					$this -> Container['https://www.zhihu.com'.$matches[1][$i]] = array(UnicodeToUTF8($matches[2][$i]), $matches[3][$i]);
				}
				else
				{
					$this -> Container[$matches[1][$i]] = array($matches[2][$i], $matches[3][$i]);
				}
				$this -> totalURLNum ++;
			}
			else
			{
				continue;
			}
		}
		return $total;
	}
}