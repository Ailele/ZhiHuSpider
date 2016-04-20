<?php
/**
 * Created by Yang xunwu.
 * User: mao
 * Date: 2016/3/21
 * Time: 8:54
 */

define('ZhiHuTopicSpiderPattern', '/<h2><a.*?href="(.*?)">(.*?)<\/a><\/h2>.+?;" data-votecount="(\w+)"/s');

define('ZhiHuHomeSpiderPattern', '#<h2><a.*?href="(.*?)">(.*?)</a></h2>.+?;" data-votecount="(\w+)"#s');
define('ZhiHuHomeSpiderXHRPattern', '/question_link.+?href=\\\"(.+?)">(.+?)<.+?data-votecount=\\\"(.*?)\\\"/');

define('ZhiHuUserSpiderrXHRPattern', '#<h2><a.*?href="(.*?)">(.*?)</a></h2>.+?;" data-votecount="(\w+)"#s');

define('ZhiHuExploerSpiderPattern', '#<h2><a.*?href="(.*?)">(.*?)</a></h2>.+?;" data-votecount="(\w+)"#s');
