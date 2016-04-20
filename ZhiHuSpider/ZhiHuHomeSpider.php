<?php
/**
 * Created by Yang xunwu.
 * User: mao
 * Date: 2016/3/19
 * Time: 14:59
 */

namespace ZhiHuSpider;

class ZhiHuHomeSpider extends ZhiHuSpider
{
	private $startPage;
	private $maxXHRTimes = 0;

	public function startFetch()
	{
		echo "开始抓取主页数据\n";
		/**
		 * get 请求主页
		 */
		$this -> connector -> setOpt(CURLOPT_URL, $this -> startPage);
		$this -> connector -> setOpt(CURLOPT_HTTPGET, 1);
		$this -> pageSource = $this -> connector -> exec();
		$this -> URLParser -> parserURL($this -> pageSource);
		$this -> URLContainer -> addURL($this -> URLParser -> getResult(), $this -> voteThreshold);
	
		/**
		 * ajax请求后续内容
		 */
		echo "开始抓取XHR数据\n";
		$this -> connector -> setURL('https://www.zhihu.com/node/TopStory2FeedList');
		$this -> connector -> setOpt(CURLOPT_POST, 1);
		$xsrf = $this -> connector -> getXrsf();

		for($offset = 20, $xhrTimes = 0; $xhrTimes < $this -> maxXHRTimes; $xhrTimes++, $offset += 20 )
		{
			$POSTFIELDS = 'params=%7B%22offset%22%3A'.$offset.'%2C%22start%22%3A%22'.$offset.'%22%7D&method=next&_xsrf='.$xsrf;
			$this -> connector -> setOpt(CURLOPT_POSTFIELDS, $POSTFIELDS);
			$this -> pageSource = $this -> connector -> exec();
			$this -> URLParser -> parserURLXHR($this -> pageSource);
			$this -> URLContainer -> adXHRdURL($this -> URLParser -> getResult(), $this -> voteThreshold);
		}
	}

	public function setMaxXHRTimes($maxXHRTimes)
	{
		$this -> maxXHRTimes = $maxXHRTimes;
	}

	public function setStartPage($startPage)
	{
		$this -> startPage = $startPage;
	}
}