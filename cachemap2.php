<?php
function onTheList($theArray, $item)
{
	for( $i=0;$i<count($theArray);$i++)
	{
		if( $theArray[$i] == $item )
			return $i;
	}
	return -1;
}

function getDBFilter($userid)
{
	$filter = "11111111110111110134100000000000"; // default filter
	$query = mysql_query("SELECT * from map_settings_v2 WHERE `user_id`=$userid");
	while($row = mysql_fetch_assoc($query))
	{ 
		$filter = '';
		foreach($row as $k=>$v)
		{
			if( $k != 'user_id')
			$filter .= $v;
		}
	}
	//echo $filter;
	return $filter;
}

function setDBFilter($userid, $filter)
{
	$sql = "REPLACE INTO map_settings_v2 SET
					user_id = $userid,
					unknown = $filter[0],
					traditional = $filter[1],
					multicache = $filter[2],
					virtual = $filter[3],
					webcam = $filter[4],
					event = $filter[5],
					quiz = $filter[6],
					mobile = $filter[7],
					math = $filter[8],
					drivein = $filter[9],
					ignored = $filter[10],
					own = $filter[11],
					found = $filter[12],
					notyetfound = $filter[13],
					geokret = $filter[14],
					showsign = $filter[15],
					active = $filter[16],
					notactive = $filter[17],
					maptype = $filter[18],
					cachelimit = $filter[19],
					cachesort = $filter[20]
					";
	mysql_query($sql);
}

function makeDBFilter()
{
	$f = "";
	if( isset($_POST['u']) ) 
		$f .= "0";
	else $f .= "1"; 
	if( isset($_POST['t']) ) 
		$f .= "0";
	else $f .= "1";
	if( isset($_POST['m']) ) 
		$f .= "0";
	else $f .= "1";
	if( isset($_POST['v']) ) 
		$f .= "0";
	else $f .= "1";
	if( isset($_POST['w']) ) 
		$f .= "0";
	else $f .= "1";
	if( isset($_POST['e']) ) 
		$f .= "0";
	else $f .= "1";
	if( isset($_POST['q']) ) 
		$f .= "0";
	else $f .= "1";
	if( isset($_POST['o']) ) 
		$f .= "0";
	else $f .= "1";
	if( isset($_POST['c']) ) 
		$f .= "0";
	else $f .= "1";
	if( isset($_POST['d']) ) 
		$f .= "0";	
	else $f .= "1";
	if( isset($_POST['I']) )
		$f .= "0";
	else $f .= "1";
	if( isset($_POST['W']) )
		$f .= "0";
	else $f .= "1";
	if( isset($_POST['Z']) )
		$f .= "0";
	else $f .= "1";
	if( isset($_POST['A']) )
		$f .= "0";
	else $f .= "1";
	if( isset($_POST['N']) )
		$f .= "0";
	else $f .= "1";
	if( isset($_POST['C']) )
		$f .= "1";
	else $f .= "0";
	if( isset($_POST['T']) )
		$f .= "0";
	else $f .= "1";
	if( isset($_POST['Y']) )
		$f .= "0";
	else $f .= "1";
	if( isset($_POST['maptype'])) {
		$f .= $_POST['maptype'];
	} else {
		$f .= "3";
	}
	if( isset($_POST['cachelimit'])) {
		$f .= (($_POST['cachelimit'] / 50) - 1);
	} else {
		$f .= "4";
	}
	if( isset($_POST['cachesort'])) {
		$f .= $_POST['cachesort'];
	} else {
		$f .= "1";
	}
	//echo $f;
	return $f;
//	ifutmvweqocdIWZANCT
}


require_once('./lib/common.inc.php');
$tplname = 'cachemap2';
tpl_set_var('bodyMod', ' onload="load()" onunload="GUnload()"');
//tpl_set_var('BodyMod', ' onload="load()" onunload="GUnload()"');
global $usr;
global $get_userid;
global $filter;
global $caches_list;
global $language;
global $lang;

$userid = '';

$get_userid = strip_tags($_REQUEST['userid']);
//user logged in?
		if ($usr == false)
		{
			$target = urlencode(substr($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 1));
			header('Location: login.php?target='.$target);
		}
		else
{
	session_start();

	tpl_set_var('score', $language[$lang]['score']);
	tpl_set_var('recommendations', $language[$lang]['recommendations']);
	tpl_set_var('created_by', $language[$lang]['created_by']);
	tpl_set_var('found', $language[$lang]['found']);
	tpl_set_var('not_found', $language[$lang]['not_found']);
	tpl_set_var('add_to', $language[$lang]['add_to']);
	tpl_set_var('remove_from', $language[$lang]['remove_from']);
	tpl_set_var('scored', $language[$lang]['scored']);
	tpl_set_var('to_print_list', $language[$lang]['to_print_list']);
	tpl_set_var('active_caches_in_area', $language[$lang]['active_caches_in_area']);
	tpl_set_var('shown', $language[$lang]['shown']);
	tpl_set_var('next', $language[$lang]['next']);
	tpl_set_var('printer_friendly', $language[$lang]['printer_friendly']);
	tpl_set_var('user_map', $language[$lang]['user_map']);
	tpl_set_var('current_zoom', $language[$lang]['current_zoom']);
	tpl_set_var('colors', $language[$lang]['colors']);
	tpl_set_var('yellow', $language[$lang]['yellow']);
	tpl_set_var('green', $language[$lang]['green']);
	tpl_set_var('gray', $language[$lang]['gray']);
	tpl_set_var('red', $language[$lang]['red']);
	tpl_set_var('last_10_days', $language[$lang]['last_10_days']);
	tpl_set_var('own', $language[$lang]['own']);
	tpl_set_var('rest', $language[$lang]['rest']);
	tpl_set_var('max_caches_per_page', $language[$lang]['max_caches_per_page']);
	tpl_set_var('sort_by', $language[$lang]['sort_by']);
	tpl_set_var('by_name', $language[$lang]['by_name']);
	tpl_set_var('by_date', $language[$lang]['by_date']);
	tpl_set_var('hide_caches_type', $language[$lang]['hide_caches_type']);
	tpl_set_var('unknown_type', $language[$lang]['unknown_type']);
	tpl_set_var('traditional', $language[$lang]['traditional']);
	tpl_set_var('multicache', $language[$lang]['multicache']);
	tpl_set_var('virtual', $language[$lang]['virtual']);
	tpl_set_var('webcam', $language[$lang]['webcam']);
	tpl_set_var('event', $language[$lang]['event']);
	tpl_set_var('quiz', $language[$lang]['quiz']);
	tpl_set_var('moving', $language[$lang]['moving']);
	tpl_set_var('hide_caches', $language[$lang]['hide_caches']);
	tpl_set_var('ignored', $language[$lang]['ignored']);
	tpl_set_var('not_yet_found', $language[$lang]['not_yet_found']);
	tpl_set_var('temp_unavailables', $language[$lang]['temp_unavailables']);
	tpl_set_var('ready_to_find', $language[$lang]['ready_to_find']);
	tpl_set_var('other_options', $language[$lang]['other_options']);
	tpl_set_var('show_signes', $language[$lang]['show_signes']);
	tpl_set_var('filter', $language[$lang]['filter']);
	tpl_set_var('without_geokret', $language[$lang]['without_geokret']);
	tpl_set_var('founds', $language[$lang]['founds']);
	
	
	if( $get_userid == '')
		$userid = $usr['userid'];
	else 
		$userid = $get_userid;
	$rs = mysql_query("SELECT `latitude`, `longitude`, `username` FROM `user` WHERE `user_id`='$userid'");
	$record = mysql_fetch_array($rs);
	if( ($_REQUEST['lat'] != "" && $_REQUEST['lon'] != "") && ($_REQUEST['lat'] != 0 && $_REQUEST['lon'] != 0))
	{
		$coordsXY=$_REQUEST['lat'].",".$_REQUEST['lon'];
		$coordsX=$_REQUEST['lat'];
		if( $_REQUEST['inputZoom'] != "" )
			tpl_set_var('zoom', $_REQUEST['inputZoom']);
		else
			tpl_set_var('zoom', 11);
	}
	else
	{
		$coordsXY="$record[latitude],$record[longitude]";
		$coordsX="$record[latitude]";
		if ($coordsX=="" || $coordsX==0) 
		{
			$coordsXY="52.5,19.2";
			tpl_set_var('zoom', 6);
		}
		else
			tpl_set_var('zoom', 11);
	}
	
	if( isset($_REQUEST['print_list']) && $_REQUEST['print_list'] == 'y')
	{
		// add cache to print (do not duplicate items)
		if( count($_SESSION['print_list']) == 0 )
			$_SESSION['print_list'] = array();
		if( onTheList($_SESSION['print_list'], $_REQUEST['cacheid']) == -1 )
			array_push($_SESSION['print_list'],$_REQUEST['cacheid']);
	}
	if( isset($_REQUEST['print_list']) && $_REQUEST['print_list'] == 'n')
	{
		// remove cache from print list
		while( onTheList($_SESSION['print_list'], $_REQUEST['cacheid']) != -1 )
			unset($_SESSION['print_list'][onTheList($_SESSION['print_list'], $_REQUEST['cacheid'])]);
		$_SESSION['print_list'] = array_values($_SESSION['print_list']);
	}
		
	tpl_set_var('coords', $coordsXY);
	tpl_set_var('username', $record[username]);

	$filter = getDBFilter($usr['userid']);
	
	if( isset( $_POST['submit'] ) )
	{
			$makeFilterResult = makeDBFilter();
			setDBFilter($usr['userid'],$makeFilterResult);
			$filter = $makeFilterResult;
	}

	/*SET YOUR MAP CODE HERE*/
	tpl_set_var('cachemap_header', '<script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;key='.$googlemap_key.'" type="text/javascript"></script>
	<script language="JavaScript1.2" type="text/javascript">
	<!-- 
		window.onbeforeunload = saveMapType;
		function saveMapType(){
			var ajaxRequest;  // The variable that makes Ajax possible!
			try{
				// Opera 8.0+, Firefox, Safari
				ajaxRequest = new XMLHttpRequest();
			} catch (e){
				// Internet Explorer Browsers
				try{
					ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					try{
						ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
					} catch (e){
						// Something went wrong
						alert("Your browser broke!");
						return false;
					}
				}
			}
			// Create a function that will receive data sent from the server
			ajaxRequest.onreadystatechange = function(){
				if(ajaxRequest.readyState == 4){
					document.myForm.time.value = ajaxRequest.responseText;
				}
			}

			var mapid;
			switch (map.getCurrentMapType()) {
				case G_NORMAL_MAP:
					mapid = "0";
				break;
				case G_SATELLITE_MAP:
					mapid = "1";
				break;
				case G_HYBRID_MAP:
					mapid = "2";
				break;
				case G_PHYSICAL_MAP:
					mapid = "3";
				break;
				default:
					mapid = "0";
			}
			
			var climit = ((document.getElementById("cachelimit").value / 50) - 1);
			var csort = document.getElementById("cachesort").value;
			
			var queryString = "?maptype=" + mapid + "&cachelimit=" + climit + "&cachesort=" + csort;
			ajaxRequest.open("GET", "cachemapsettings.php" + queryString, false);
			ajaxRequest.send(null); 
			
		}		 
	//-->
	</script>');
	tpl_BuildTemplate(); 
}
?>

