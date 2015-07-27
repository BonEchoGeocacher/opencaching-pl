<?php

	function writeReason($type)
	{
		switch( $type )
		{
			case '1':
				return "Uwaga co do lokalizacji skrzynki";
			case '2':
				return "Nieodpowiedni wpis w logu";
			case '3':
				return "Nieodpowiednia zawartość skrzynki";
			case '4':
				return "Inny";
		}
	}
	
	function writeStatus($status)
	{
		switch( $status )
		{
			case '0':
				return "<font color='red'>nowe</font>";
			case '1':
				return "<font color='orange'>w toku</font>";
			case '2':
				return "<font color='green'>zamknięte</font>";
			case '3':
				return "<font color='blue'>zajrzyj tu!</font>";
		}
	}

	function colorCacheStatus($text, $id )
	{
		switch( $id )
		{
			case '1':
				return "<font color='green'>$text</font>";
			case '2':
				return "<font color='orange'>$text</font>";
			case '3':
				return "<font color='red'>$text</font>";
			default:
				return "<font color='gray'>$text</font>";
		}
	}
	
	function nonEmptyCacheName($cacheName)
	{
		if( str_replace(" ", "", $cacheName) == "" )
			return "[bez nazwy]";
		return $cacheName;
	}
	
	function getUsername($userid)
	{
		$sql = "SELECT username FROM user WHERE user_id='".sql_escape(intval($userid))."'";
		$query = mysql_query($sql) or die();
		if( mysql_num_rows($query) > 0)
			return mysql_result($query,0);
		return null;
	}
	
	//prepare the templates and include all neccessary
	require_once('./lib/common.inc.php');
	$tplname = 'viewreports';
	$content = '';
	// tylko dla członków Rady
	if ($error == false && $usr['admin'])
	{
		if( $_GET['archiwum'] == 1 )
		{
			tpl_set_var('arch_curr', "bieżących zgłoszeń");
			tpl_set_var('archiwum', 0);
			$show_archive = " reports.status = 2 AND ";
			$sorting_order = "DESC";
		}
		else
		{
			tpl_set_var('arch_curr', "archiwum");
			tpl_set_var('archiwum', 1);
			$show_archive = " reports.status <> 2 AND ";
			$sorting_order = "ASC";
		}
		$sql = "SELECT cache_status.id AS cs_id, cache_status.pl AS cache_status, reports.id as report_id, reports.user_id as user_id, reports.changed_by as changed_by, reports.changed_date as changed_date, reports.cache_id as cache_id, reports.type as type, reports.text as text, reports.submit_date as submit_date, reports.responsible_id as responsible_id, reports.status as status, user.username as username, user.user_id as user_id, caches.name as cachename, caches.status AS c_status FROM cache_status, reports, user, caches WHERE cache_status.id = caches.status AND ".sql_escape($show_archive)." user.user_id = reports.user_id AND caches.cache_id = reports.cache_id ORDER BY submit_date ".sql_escape($sorting_order);
		$query = mysql_query($sql) or die("DB error");
		while( $report = mysql_fetch_array($query) )
		{
			$content .= "<tr>";
			//$username_sql = "SELECT username FROM user WHERE user_id='".sql_escape($report['user_id'])."'";
			//$username_query = mysql_query($username_sql) or die("DB error");
			//$username = mysql_result($username_query,0);
			
			$content .= "<td bgcolor='white'>".$report['report_id']."</td>";
			$content .= "<td bgcolor='white'>".$report['submit_date']."</td>";
			$content .= "<td bgcolor='white'><a href='viewcache.php?cacheid=".$report['cache_id']."'>".nonEmptyCacheName($report['cachename'])."</a></td>";
			$content .= "<td bgcolor='white'>".colorCacheStatus($report['cache_status'], $report['c_status'])."</td>";
			$content .= "<td bgcolor='white'><a href='viewreport.php?reportid=".$report['report_id']."'>".writeReason($report['type'])."</a></td>";
			$content .= "<td bgcolor='white'><a href='viewprofile.php?userid=".$report['user_id']."'>".$report['username']."</a></td>";
			$content .= "<td bgcolor='white'><a href='viewprofile.php?userid=".$report['responsible_id']."'>".getUsername($report['responsible_id'])."</a></td>";
			$content .= "<td bgcolor='white' width='60'>".writeStatus($report['status'])."</td>";
			$content .= "<td bgcolor='white'>".($report['changed_by']=='0'?'':getUsername($report['changed_by']))."</td>";
			$content .= "<td bgcolor='white'>".($report['changed_date'])."</td>";
			$content .= "</tr>";
		}
		tpl_set_var('content', $content);
	}
	else
	{
		$tplname = 'viewreports_error';
	}
	tpl_BuildTemplate();
	
?>