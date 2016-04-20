<?php
/**
 * Created by Yang xunwu.
 * User: mao
 * Date: 2016/3/19
 * Time: 14:48
 */

namespace ZhiHuSpider;

abstract class ZhiHuSpider
{
	protected $connector;
	protected $URLParser;
	protected $URLContainer;
	protected $voteThreshold;
	protected $pageSource;

	public function __construct(ZhiHuSpider\ZhiHuConnector $connector)
	{
		$this -> connector = $connector;
	}

	abstract function startFetch();

	public function setURLParser(ZhiHuURL\ZhiHuURLParser $URLParser)
	{
		echo "添加解析规则\n";
		$this -> URLParser = $URLParser;
	}

	public function getURLParser()
	{
		return $this -> URLParser;
	}

	public function setContainer(ZhiHuURL\ZhiHuURLContainer $URLContainer)
	{
		echo "添加储存器\n";
		$this -> URLContainer = $URLContainer;
	}

	public function getContainer()
	{
		return $this -> URLContainer;
	}

	public function setVoteThreshold($threshold)
	{
		$this -> voteThreshold = $threshold;
	}
}