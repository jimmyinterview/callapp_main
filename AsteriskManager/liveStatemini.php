<?php

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$begintime = $time;
$inuse      = Array();
$dict_queue = Array();
$filter_queues = array("2500055");

require("config.php");
require("asmanager.php");
require("realtime_functions.php");
if(isset($_SESSION['QSTATS']['hideloggedoff'])) {
    $ocultar= $_SESSION['QSTATS']['hideloggedoff'];
} else {
    $ocultar="false";
}
if(isset($_SESSION['QSTATS']['filter'])) {
    $filter= $_SESSION['QSTATS']['filter'];
} else {
    $filter="";
}

$am=new AsteriskManager();
$am->connect($manager_host,$manager_user,$manager_secret);

$channels = get_channels ($am);
foreach($channels as $ch=>$chv) {
  list($chan,$ses) = split("-",$ch,2);
  $inuse["$chan"]=$ch;
}

$queues   = get_queues   ($am,$channels);

foreach ($queues as $key=>$val) {
  $queue[] = $key;
}

///QUEUES
//echo "<h2>".$lang[$language]['agent_status']."</h2><br/>";

$color['unavailable']="flesh_off.png";
$color['unknown']="#dadada";
$color['busy']="flesh_inc.png";
$color['dialout']="#d0303f";
$color['ringing']="flesh_ringing.png";
$color['not in use']="flesh_free.png";
$color['paused']="#000000";

foreach($filter_queues  as $qn) {
	if($filter=="" || stristr($qn,$filter)) {
		$contador=1;
		if(!isset($queues[$qn]['members'])) continue;

		foreach($queues[$qn]['members'] as $key=>$val) {
			 
			$stat="";
			$last="";
			$dur="";
			$clid="";
			$akey = $queues[$qn]['members'][$key]['agent'];
			$aname = $queues[$qn]['members'][$key]['name'];
			$aval = $queues[$qn]['members'][$key]['type'];
			if(array_key_exists($key,$inuse)) {
				if($aval=="not in use") {
					$aval = "dialout";
				}
				if($channels[$inuse[$key]]['duration']=='') {
					$newkey = $channels[$inuse[$key]]['bridgedto'];
					$dur = $channels[$newkey]['duration_str'];
					$clid = $channels[$newkey]['callerid'];
				} else {
					$newkey = $channels[$inuse[$key]]['bridgedto'];
					$clid = $channels[$newkey]['callerid'];
					$dur = $channels[$inuse[$key]]['duration_str'];
				}
			}
			$stat = $queues[$qn]['members'][$key]['status'];
			$last = $queues[$qn]['members'][$key]['lastcall'];

			if(($aval == "unavailable" || $aval == "unknown") && $ocultar=="true") {
				// Skip
			} else {
				if($contador==1) {
					echo '<tr>
                            <td colspan="6" style="border-left: 1px solid #E6E6E6;border-right: 1px solid #E6E6E6;">სთეიშენები</td>
                          </tr>
					      <tr class="tb_head" style="border: 1px solid #E6E6E6;">
                            <td style="width:75px">შიდა ნომერი</td>
                            <td style="width:50px">სტატუსი</td>
                          </tr>';
				}

				if($contador%2) {
					$odd="class='odd'";
				} else {
					$odd="";
				}

				if($last<>"") {
					$last=$last." ".$lang[$language]['min_ago'];
				} else {
					$last = $lang[$language]['no_info'];
				}

				$agent_name = agent_name($aname);

				echo '<tr style="border: 1px solid #E6E6E6;">';
				echo "<td>$agent_name</td>";

				if($stat<>"") {
				$aval="paused";
			}

			if(!array_key_exists($key,$inuse)) {
					if($aval=="busy") $aval="not in use";
			}

			$aval2 = ereg_replace(" ","_",$aval);
			$mystringaval = $lang[$language][$aval2];

			if($mystringaval=="") $mystringaval = $aval;
			echo '<td class="td_center"><img alt="inner" src="media/images/icons/'.$color[$aval].'" height="14" width="14"></td>';	
			echo "</tr>";
			$contador++;
			}
			}
		if($contador>1) {
		//echo "</table><br/>\n";
		}
	}
}

?>

