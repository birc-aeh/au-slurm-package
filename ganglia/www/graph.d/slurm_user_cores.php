<?php

# Customized distinct colors
$color= array("#00FF00", "#0000FF", "#FF0000", "#01FFFE", "#FFA6FE", "#FFDB66", "#006401", "#95003A", "#007DB5", "#FF00F6", "#FFEEE8", "#774D00", "#90FB92", "#0076FF", "#D5FF00", "#FF937E", "#6A826C", "#FF029D", "#FE8900", "#7A4782", "#7E2DD2", "#85A900", "#FF0056", "#A42400", "#00AE7E", "#BDC6FF", "#BDD393", "#00B917", "#9E008E", "#C28C9F", "#FF74A3", "#01D0FF", "#E56FFE", "#0E4CA1", "#91D0CB", "#BE9970", "#968AE8", "#BB8800", "#DEFF74", "#00FFC6", "#FFE502", "#008F9C", "#98FF52", "#7544B1", "#B500FF", "#00FF78", "#FF6E41", "#6B6882", "#5FAD4E", "#A75740", "#A5FFD2", "#FFB167", "#009BFF", "#E85EBE");

sort($color);

function get_color($i) {
	global $color;
	return $color[$i % sizeof($color)];
}
	 

function graph_slurm_user_cores ( &$rrdtool_graph ) {
	global $rrd_dir;

	$count = 0;
	exec("/com/extra/slurm/14.03.0/bin/sacctmgr -n show users | awk '{print $1}'", $users);

	sort($users, SORT_STRING);

	$l = $_GET["legend"];
	if($l != "hide") {
		$legend = 1;
	} else {
		$legend = 0;
	}

	$series = "";

	$maxlen = 0;

	$limit = 1000;
	$i = 0;
	foreach($users as $user) {
		$name = str_replace(".", "_", $user);

		if(strlen($user) > $maxlen) {
			$maxlen = strlen($user);
		}

		if(file_exists("${rrd_dir}/slurm_cores_" . $user . ".rrd")) {
			$series .= "DEF:'" . $name . "'='${rrd_dir}/slurm_cores_" . $user . ".rrd':'sum':AVERAGE ";
		}
		$i++;
		if($i == $limit) {
			break;
		}
	}

	$series .= "DEF:'cores'='${rrd_dir}/slurm_cores_total.rrd':'sum':AVERAGE ";


	$series .= "LINE:'cores'#FF0000:'#Cores' ";
	$series .="VDEF:'cores_last'='cores',LAST ";
	$series .="VDEF:'cores_min'='cores',MINIMUM ";
	$series .="VDEF:'cores_avg'='cores',AVERAGE ";
	$series .="VDEF:'cores_max'='cores',MAXIMUM ";
	$series .="GPRINT:'cores_last':'Last\:%5.2lf%s' ";
	$series .="GPRINT:'cores_min':'Min\:%5.2lf%s' ";
	$series .="GPRINT:'cores_avg':'Avg\:%5.2lf%s' ";
	$series .="GPRINT:'cores_max':'Max\:%5.2lf%s'\\\c ";

	# Add a line for each user, and an entry for each user if we are displaying the legend.
	$i = 0;
	foreach($users as $user) {
		$name = str_replace(".", "_", $user);

		if(file_exists("${rrd_dir}/slurm_cores_" . $user . ".rrd")) {		  

			if($i == 0) {
				$series .= "AREA:'" . $name . "'" . get_color($i) . ":'";
			} else {
				$series .= "STACK:'" . $name . "'" . get_color($i) . ":'";
			}
			if($legend) {
				$series .= sprintf("%" . $maxlen . "s", $user) ."' ";
			} else {
				$series .= "' ";
			}
			$series .= "VDEF:" . $name . "_last=" . $name . ",LAST ";
			$series .= "VDEF:" . $name . "_min=" . $name . ",MINIMUM ";
			$series .= "VDEF:" . $name . "_avg=" . $name . ",AVERAGE ";
			$series .= "VDEF:" . $name . "_max=" . $name . ",MAXIMUM ";
			if($legend) {
				$series .= "GPRINT:'" . $name . "_last':'Now\:%5.0lf%s' ";
				$series .= "GPRINT:'" . $name . "_min':'Min\:%5.0lf%s' ";
				$series .= "GPRINT:'" . $name . "_avg':'Avg\:%5.0lf%s' ";
				$series .= "GPRINT:'" . $name . "_max':'Max\:%5.0lf%s'\\\c ";
			}
		}
		$i++;
	}

	foreach($users as $user) {
		$name = str_replace(".", "_", $user);
		if(file_exists("${rrd_dir}/slurm_cores_" . $user . ".rrd")) {
		}
	}
	
	//required
	$rrdtool_graph['title'] = '-- Slurm Cores per User --';
	$rrdtool_graph['vertical-label'] = 'cores';
	$rrdtool_graph['series'] = $series;
	$rrdtool_graph['lower-limit'] = '0';
	$rrdtool_graph['extras'] = '-R light';

	return $rrdtool_graph;
}
?>
