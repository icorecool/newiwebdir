<?php
if (!defined('IN_HANFOX')) exit('Access Denied');

$pagename = '数据归档';
$pageurl = '?mod=archives';
$tplfile = 'archives.html';
$tpldir = 'archives';
$table = $DB->table('websites');

/** 缓存设置 */
$smarty->compile_dir .= $tpldir;
$smarty->cache_dir .= $tpldir;
$smarty->cache_lifetime = $options['cache_time_list'] * 3600;

$pagesize = 10;
$curpage = intval($_GET['page']);
if ($curpage > 1) {
	$start = ($curpage - 1) * $pagesize;
} else {
	$start = 0;
	$curpage = 1;
}
$nowpage = ($curpage > 0) ? ' - 第'.$curpage.'页': '';
		
$setdate = intval($_GET['date']);
$cache_id = ($setdate && strlen($setdate) == 6 ? $setdate.'-' : '').$curpage;

if (!$smarty->isCached($tplfile, $cache_id)) {
	$smarty->assign('site_title', $pagename.$nowpage.' - '.$options['site_name']);
	$smarty->assign('site_keywords', '网站存档，目录存档，数据归档');
	$smarty->assign('site_description', '可根据年份、月份来查询，让你及时了解某一时间段内网站的收录情况。');
	$smarty->assign('site_path', get_sitepath().' &nbsp;&rsaquo;&nbsp; '.$pagename);
	$smarty->assign('site_rss', get_rssfeed());
	
	$where = "w.web_status=3";
	if ($setdate && strlen($setdate) == 6) {
		$year = substr($setdate, 0, 4);
		if ($year >= 2038 || $year <= 1970) {
			$year = gmdate('Y');
			$month = gmdate('m');
		} else {
			$month = substr($setdate, -2);
			$start_timestamp = strtotime($year.'-'.$month.'-1');
			if ($month == 12) {
				$end_year = $year + 1;
				$end_month = 1;
			} else {
				$end_year  = $year;
				$end_month = $month + 1;
			}
			$end_timestamp = strtotime($end_year.'-'.$end_month.'-1');
		}
		$where .= " AND w.web_ctime>='".$start_timestamp."' AND w.web_ctime<'".$end_timestamp."'";
		
		$timetext = $year.'年'.$month.'月';
		
		$smarty->assign('site_title', $timetext.' - 网站数据归档'.$nowpage.' - '.$options['site_name']);
		$smarty->assign('site_description', $timetext.'网站数据归档列表。');
		$smarty->assign('site_path', get_sitepath().' &nbsp;&rsaquo;&nbsp; <a href="'.$pageurl.'">数据归档</a> &nbsp;&rsaquo;&nbsp; '.$timetext);
		$smarty->assign('timetext', $timetext);
				
		$pageurl .= '&date='.$setdate;
	}
	
	$websites = get_website_list($where, 'web_ctime', 'DESC', $start, $pagesize);
	$total = $DB->get_count($table.' w', $where);
	$showpage = showpage($pageurl, $total, $curpage, $pagesize);
			
	$smarty->assign('pagename', $pagename);
	$smarty->assign('nowpage', $nowpage);
	$smarty->assign('archives', get_archives());
	$smarty->assign('total', $total);
	$smarty->assign('websites', $websites);
	$smarty->assign('showpage', $showpage);
}
	
smarty_output($tplfile, $cache_id);
?>