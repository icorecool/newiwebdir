<?php
if (!defined('IN_HANFOX')) exit('Access Denied');

$cate_id = intval($_GET['cid']);

get_website_sitemap($cate_id);
?>