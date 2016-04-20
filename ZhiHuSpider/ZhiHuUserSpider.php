<?php
/**
 * Created by Yang xunwu.
 * User: mao
 * Date: 2016/3/20
 * Time: 12:38
 */
namespace ZhiHuSpider;

class ZhiHuUserSpider extends ZhiHuSpider
{
	private $folloeesNum;
	private $followersNum;
	private $userHash;
	private $Xrsf;

	public function startFetch()
	{

	}

	public function startFetchUser($userID, $maxPageNum, $voteThreshold)
	{
		echo "--------------------------------开始抓取--------------------------------\n{$userID}\t $maxPageNum 页\n";
		$this -> connector -> setOpt(CURLOPT_HTTPGET, 1);
		for($pageId = 1; $pageId <= $maxPageNum; $pageId++)
		{
			echo "$pageId\t";
			$url = 'https://www.zhihu.com/people/'.$userID.'/answers?page='.$pageId;
			$this -> connector -> setOpt(CURLOPT_URL, $url);
			$this -> pageSource = $this -> connector -> exec();
			$this -> URLParser -> parserURL($this -> pageSource);
			$this -> URLContainer -> addURL($this -> URLParser -> getResult(), $voteThreshold);
		}
		echo "\n--------------------------------抓取完毕--------------------------------\n";
	}

	public function getUserHash($userID)
	{
		$this -> connector -> setOpt(CURLOPT_URL, 'https://www.zhihu.com/people/'.$userID);
		$this -> connector -> setOpt(CURLOPT_HTTPGET, 1);
		$pattern = '/data-id="(\w{32})"/';
		$userPage = $this -> connector -> exec();
		preg_match_all($pattern, $userPage, $match);
		return $match[1][0];
	}

	public  function findMaxPage($userID)
	{
		$this -> connector -> setOpt(CURLOPT_URL,'https://www.zhihu.com/people/'.$userID.'/answers');
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


	/**
	 * @param $userName
	 * @return array 返回 id => 名字 数组
	 */

	public function getPeopleIDbyName($userName)
	{
		$url = 'https://www.zhihu.com/autocomplete?token='.urlencode($userName).'&max_matches=10&use_similar=0';
		$this -> connector -> setOpt(CURLOPT_HTTPGET, 1);
		$this -> connector -> setOpt(CURLOPT_URL, $url);
		$userInfo = $this -> connector -> exec();

		//eg:"people", "\u6768\u8bad\u6b66", "SheepOnSea"
		$pattern = '/"people", "(.*?)", "(.*?)"/';
		preg_match_all($pattern, $userInfo, $match);
		$userID = array();
		if (count($match[1]) > 0)
		{
			$userID[UnicodeToUTF8($match[2][0])] = UnicodeToUTF8($match[1][0]);
		}
		return $userID;
	}


	public function getFolloeesNum($userID)
	{
		$this -> connector -> setOpt(CURLOPT_HTTPGET, 1);
		$this -> connector -> setOpt(CURLOPT_URL, 'https://www.zhihu.com/people/'.$userID);
		$pageSource = $this -> connector -> exec();
		$pattern = '/<strong>(\d+)<\/strong><label> 人<\/label>/';
		preg_match_all($pattern, $pageSource, $matches);

		return $this -> folloeesNum = $matches[1][0];
	}

	public function getFollowersNum($userID)
	{
		$this -> connector -> setOpt(CURLOPT_HTTPGET, 1);
		$this -> connector -> setOpt(CURLOPT_URL, 'https://www.zhihu.com/people/'.$userID);
		$pageSource = $this -> connector -> exec();
		$pattern = '/<strong>(\d+)<\/strong><label> 人<\/label>/';
		$matches = array();
		preg_match_all($pattern, $pageSource, $matches);

		return $this -> followersNum = $matches[1][1];
	}

	//获取关注的人的信息
	public function getFolloeesDetail($userID)
	{
		$folloeesDetail = array();
		$this -> Xrsf = $this -> connector -> getXrsf();
		$hash = $this -> getUserHash($userID);
		$folloeesNum = $this -> getFolloeesNum($userID);
		echo "Folloees num is ".$folloeesNum."\n";
		$offset = 20;

		if ($folloeesNum > 0)
		{
			$maxXHRTimes = floor(($folloeesNum - 20)/ $offset) * $offset;
			$this -> connector -> setURL('https://www.zhihu.com/node/ProfileFolloweesListV2');
			$this -> connector -> setOpt(CURLOPT_POST, 1);
			$pattern = '/data-id=\\\"(\w{32})\\\".*?title=\\\"(.*?)\\\".*?data-tip=\\\"p\$t\$(.*?)\\\"/';

			for($xhrCnt = 0; $xhrCnt <= $maxXHRTimes; $xhrCnt += 20)
			{
				$postFields = 'method=next&params=%7B%22offset%22%3A'.$xhrCnt.'%2C%22order_by%22%3A%22created%22%2C%22hash_id%22%3A%22'.$hash.'%22%7D&_xsrf='.$this -> Xrsf;
				$this -> connector -> setOpt(CURLOPT_POSTFIELDS, $postFields);
				$XHRResponse = $this -> connector -> exec();
				preg_match_all($pattern, $XHRResponse, $maches);

				for($i = 0; $i < $offset; $i++)
				{
					$folloeesDetail[] = array($maches[1][$i], UnicodeToUTF8($maches[2][$i]), $maches[3][$i]);
				}
			}

			$leftXHRTimes = $folloeesNum % 20;
			if ($leftXHRTimes !== 0)
			{
				$postFields = 'method=next&params=%7B%22offset%22%3A'.($maxXHRTimes + 20).'%2C%22order_by%22%3A%22created%22%2C%22hash_id%22%3A%22'.$hash.'%22%7D&_xsrf='.$this -> Xrsf;
				$this -> connector -> setOpt(CURLOPT_POSTFIELDS, $postFields);
				$XHRResponse = $this -> connector -> exec();
				preg_match_all($pattern, $XHRResponse, $maches);

				for($i = 0; $i < $leftXHRTimes; $i++)
				{
					$folloeesDetail[] = array($maches[1][$i], UnicodeToUTF8($maches[2][$i]), $maches[3][$i]);
				}
			}

		}

		return $folloeesDetail;
	}

	public function getFollowersDetail($userID)
	{
		$folloeesDetail = array();
		$this -> Xrsf = $this -> connector -> getXrsf();
		$hash = $this -> getUserHash($userID);
		$followersNum = $this -> getFollowersNum($userID);
		echo "Folloees num is ".$followersNum."\n";
		$offset = 20;

		if ($followersNum > 0)
		{
			$maxXHRTimes = floor(($followersNum - 20)/ $offset) * $offset;
			$this -> connector -> setURL('https://www.zhihu.com/node/ProfileFollowersListV2');
			$this -> connector -> setOpt(CURLOPT_POST, 1);
			$pattern = '/data-id=\\\"(\w{32})\\\".*?title=\\\"(.*?)\\\".*?data-tip=\\\"p\$t\$(.*?)\\\"/';

			for($xhrCnt = 0; $xhrCnt <= $maxXHRTimes; $xhrCnt += 20)
			{
				$postFields = 'method=next&params=%7B%22offset%22%3A'.$xhrCnt.'%2C%22order_by%22%3A%22created%22%2C%22hash_id%22%3A%22'.$hash.'%22%7D&_xsrf='.$this -> Xrsf;
				$this -> connector -> setOpt(CURLOPT_POSTFIELDS, $postFields);
				$XHRResponse = $this -> connector -> exec();
				preg_match_all($pattern, $XHRResponse, $maches);

				for($i = 0; $i < $offset; $i++)
				{
					$folloeesDetail[] = array($maches[1][$i], UnicodeToUTF8($maches[2][$i]), $maches[3][$i]);
				}
			}

			$leftXHRTimes = $followersNum % 20;
			if ($leftXHRTimes !== 0)
			{
				$postFields = 'method=next&params=%7B%22offset%22%3A'.($maxXHRTimes + 20).'%2C%22order_by%22%3A%22created%22%2C%22hash_id%22%3A%22'.$hash.'%22%7D&_xsrf='.$this -> Xrsf;
				$this -> connector -> setOpt(CURLOPT_POSTFIELDS, $postFields);
				$XHRResponse = $this -> connector -> exec();
				preg_match_all($pattern, $XHRResponse, $maches);

				for($i = 0; $i < $leftXHRTimes; $i++)
				{
					$folloeesDetail[] = array($maches[1][$i], UnicodeToUTF8($maches[2][$i]), $maches[3][$i]);
				}
			}

		}

		return $folloeesDetail;
	}
}