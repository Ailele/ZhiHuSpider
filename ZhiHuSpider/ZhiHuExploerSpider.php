<?php
/**
 * Created by Yang xunwu.
 * User: mao
 * Date: 2016/3/19
 * Time: 23:49
 */


namespace ZhiHuSpider;

class ZhiHuExoloreSpider extends ZhiHuSpider
{
	private $maxXHRTimes;

	public function startFetch()
	{
		$this -> connector -> setOpt(CURLOPT_HTTPGET, 1);

		for($offset = 0; $offset < $this -> maxXHRTimes; $offset += 5)
		{
			$this -> connector -> setOpt(CURLOPT_URL, 'https://www.zhihu.com/node/ExploreAnswerListV2?params=%7B%22offset%22%3A'.$offset.'%2C%22type%22%3A%22day%22%7D');
			$this -> pageSource = $this -> connector -> exec();
			$this -> URLParser -> parserURL($this -> pageSource);
			$this -> URLContainer -> addURL($this -> URLParser -> getResult(), $this -> voteThreshold);
		}
	}

	public function setMaxXHR($maxXHRTimes)
	{
		$this -> maxXHRTimes = $maxXHRTimes;
	}
}