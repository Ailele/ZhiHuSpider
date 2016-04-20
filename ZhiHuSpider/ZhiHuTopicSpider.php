<?php
/**
 * Created by Yang xunwu.
 * User: mao
 * Date: 2016/3/19
 * Time: 16:42
 */

namespace ZhiHuSpider;



class ZhiHuTopicSpider extends ZhiHuSpider
{
	private $topicID;
	private $topicName;
	private $mainTopicList;
	private $subTopicList;
	private $maxPageNum;
	private $allTopic = array();

	/**
	 * 抓取话题精华里面攒数超过voteThreshold
	 */
	public function startFetch()
	{
		$this -> connector -> setOpt(CURLOPT_HTTPGET, 1);
		for($pageIndex = 1; $pageIndex < $this -> maxPageNum; $pageIndex++)
		{
			echo "开始抓取第 $pageIndex 页\n";
			$this -> connector -> setOpt(CURLOPT_URL, 'https://www.zhihu.com/topic/'.$this -> topicID.'/top-answers?page='.$pageIndex);
			$this -> pageSource = $this -> connector -> exec();
			$this -> URLParser -> parserURL($this -> pageSource);
			$this -> URLContainer -> addURL($this -> URLParser -> getResult(), $this -> voteThreshold);
		}
	}

	/**
	 * @param $root array ('根ID' => array('根名称', '父ID'));
	 * @return array 所有的子话题数组 array('话题ID' => array('话题名称', '父ID') ...)
	 */
	public function findAllSubTopic($root)
	{
		//带查找自话题的数组 话题ID => 福话题ID
		//root  19776749
		$temp = $root;
		$this -> connector -> setOpt(CURLOPT_POST, 1);
		$this -> connector -> setOpt(CURLOPT_POSTFIELDS, '_xsrf='.$this -> connector -> getXrsf());

		while(count($temp) > 0)
		{
			//获取待查找话题temp数组首部第一个话题id
			$tempkeys = array_keys($temp);
			$now = array_shift($tempkeys);

			$url = 'https://www.zhihu.com/topic/'.$now.'/organize/entire';
			$this -> connector -> setOpt(CURLOPT_URL, $url);
			$page = $this -> connector -> exec();

			//获取自话题后，将自身从中去掉
			//"topic", "Microsoft Office", "19557307"]
			//$match[1] titleName   $match[2] titldID
			$pattern = '/topic", "(.*?)", "(\d{8})"/';
			// $pattern = '/topic(.*?)(\d{8})/';
			preg_match_all($pattern, $page, $match);
			unset($match[1][0]);
			unset($match[2][0]);
			$totalGet = count($match[1]);
			//将得到的子话题装入temp中, 均为 自身ID => array(话题标题，父话题ID)，插入待查找话题的尾部，等待继续查找
			for($idx = 1; $idx <= $totalGet; $idx++)
			{
				$temp[$match[2][$idx]] = array($match[1][$idx], $now);
			}

			//循环检查返回的json 是否需要多次ajax查找剩下的子话题 加载更多的unicode '\u52a0\u8f7d\u66f4\u591a'
			$ISXHRpattern = '/\\\u52a0\\\u8f7d\\\u66f4\\\u591a", "(\d{8})"/';
			while(preg_match_all($ISXHRpattern, $page, $match) !== 0)
			{
				$this -> connector -> setOpt(CURLOPT_URL, 'https://www.zhihu.com/topic/'.$now.'/organize/entire?child='.$match[1][0].'&parent='.$now);
				$page = $this -> connector -> exec();
				$pattern = '/topic", "(.*?)", "(\d{8})"/';
				preg_match_all($pattern, $page, $match);

				//将自己去掉
				unset($match[1][0]);
				unset($match[2][0]);

				//将得到的子话题装入temp中, 均为 自身ID => 父话题ID，插入待查找话题的尾部
				$totalGet = count($match[1]);
				//将得到的子话题装入temp中, 均为 自身ID => array(话题标题，父话题ID)，插入待查找话题的尾部，等待继续查找
				for($idx = 1; $idx <= $totalGet; $idx++)
				{
					$temp[$match[2][$idx]] = array($match[1][$idx], $now);
				}
			}

			//从数组头第一个元素，既当前查询的话题ID放入alltopic,表示话题已经查询过
			$this -> allTopic[$now] = $temp[$now];
			echo sprintf("话题ID:%8s\t    父话题ID：%8s\t    话题名:%s\n", $now, $temp[$now][1], UnicodeToUTF8($temp[$now][0]));
			unset($temp[$now]);
		}

		return $this -> allTopic;
	}


	/**
	 * @param $topicName string 通过话题中文名获得话题ID
	 * @return null 返回话题ID或者null(没有该话题)
	 */
	public function getTopicIdByName($topicName)
	{
		$this -> topicName = $topicName;
		$this -> connector -> setOpt(CURLOPT_URL, 'https://www.zhihu.com/autocomplete?token='.urlencode($topicName).'&max_matches=10&use_similar=0');
		$this -> connector -> setOpt(CURLOPT_HTTPGET, 1);
		$XHRResponse = $this -> connector -> exec();
		$pattern = '/"topic", ".*?", "(\d+?)"/';
		preg_match($pattern, $XHRResponse, $match);
		if (isset($match[1]))
		{
			return $this -> topicID = $match[1];
		}
		return null;
	}

	/**
	 * @param $topicId string 话题ID
	 * @return mixed 返回话题中文名
	 */
	public function getTopicNameById($topicId)
	{
		$this -> connector -> setOpt(CURLOPT_URL, 'https://www.zhihu.com/topic/'.$topicId.'/top-answers');
		$this -> connector -> setOpt(CURLOPT_HTTPGET, 1);
		$this -> pageSource = $this -> connector -> exec();
		$pattern = '/<h1 .*?"1">(.*?)</s';
		preg_match_all($pattern, $this -> pageSource, $matches);
		return $this -> topicName = $matches[1][0];
	}

	public function getTopicName()
	{
		return $this -> topicName;
	}

	/**
	 * @return mixed 话题广场所有主话题
	 * 返回数组 [话题名] => [ID]
	 */
	public function getMainTopicListAuto()
	{
		$this -> connector -> setOpt(CURLOPT_URL, 'https://www.zhihu.com/topics');
		$this -> connector -> setOpt(CURLOPT_HTTPGET, 1);
		$pageSource = $this -> connector -> exec();
		$pattern = '/data-id="(\d+)".*?href="#(.*?)"/';
		preg_match_all($pattern, $pageSource, $matches);
		$total = count($matches[0]);
		for($idx = 0; $idx < $total; $idx++)
		{
			$this -> mainTopicList[$matches[2][$idx]] = $matches[1][$idx];
		}
		return $this -> mainTopicList;
	}

	/**
	 * @param $topicName string 主话题中文名
	 * @return mixed 返回数组[话题中文名] => [话题ID]
	 * 获得话题列表里面的子话题 只取一面
	 */
	public function getSubTopicListByName($topicName)
	{
		$this -> connector -> setOpt(CURLOPT_URL, 'https://www.zhihu.com/node/TopicsPlazzaListV2');
		$this -> connector -> setOpt(CURLOPT_POST, 1);
		$topicID = $this -> mainTopicList[$topicName];
		$offset = 0;
		$xsrf = $this -> connector -> getXrsf();
		$hash = $this -> connector -> getHash();
		$this -> connector -> setOpt(CURLOPT_POSTFIELDS, 'method=next&params=%7B%22topic_id%22%3A'.$topicID.'%2C%22offset%22%3A'.$offset.'%2C%22hash_id%22%3A%22'.$hash.'%22%7D&_xsrf='.$xsrf);
		$pageSource = $this -> connector -> exec();
		$pattern = '#href=\\\"\\\/topic\\\/(.*?)\\\".*?xs.jpg\\\" alt=\\\"(.*?)\\\"#s';
		preg_match_all($pattern, $pageSource, $matches);
		$total = count($matches[0]);
		for($idx = 0; $idx < $total; $idx++)
		{
			$this -> subTopicList[UnicodeToUTF8($matches[2][$idx])] = $matches[1][$idx];
		}

		return ($this -> subTopicList);
	}

	/*
	 * 设置主题，输入中文主题名自动查找ID
	 */
	public function setTopicIDByName($topicName)
	{
		$this -> topicID = $this -> getTopicIdByName($topicName);
	}

	public function getTopicId()
	{
		return $this -> topicID;
	}

	public function findMaxPage()
	{
		$this -> connector -> setOpt(CURLOPT_URL,'https://www.zhihu.com/topic/'.$this -> topicID.'/top-answers');
		$this -> connector -> setOpt(CURLOPT_HTTPGET, 1);
		$content = $this -> connector -> exec();
		$pattern = '/page=\d+">(\d+)<\/a><\/span>/';
		$match = array();
		preg_match_all($pattern, $content, $match);
		if (count($match[1]) == 0)
		{
			return 0;
		}
		return $this -> maxPageNum = $match[1][count($match[1]) -1];
	}

	function setMaxPage($pageNum)
	{
		$this -> maxPageNum = $pageNum;
	}
}