<?php
function graph_slurm_jobs_current ( &$rrdtool_graph ) {
	global $rrd_dir;
	
	$series =
		  "DEF:'running'='${rrd_dir}/slurm_jobs_running.rrd':'sum':AVERAGE "
		. "DEF:'pending'='${rrd_dir}/slurm_jobs_pending.rrd':'sum':AVERAGE "
		. "DEF:'onhold'='${rrd_dir}/slurm_jobs_onhold.rrd':'sum':AVERAGE "
		. "LINE2:'running'#54EC48:'running' "
		. "LINE2:'pending'#EC9D48:'pending' "
		. "LINE2:'onhold'#48C4EC:'onhold' "
		;
	
	//required
	$rrdtool_graph['title'] = '-- Slurm Current Jobs Status --';
	$rrdtool_graph['vertical-label'] = 'jobs';
	$rrdtool_graph['series'] = $series;
	$rrdtool_graph['lower-limit'] = '0';
	$rrdtool_graph['extras'] = '-R light';

	return $rrdtool_graph;
}
#  RED  	#EA644A	#CC3118
#  ORANGE	#EC9D48	#CC7016
#  YELLOW	#ECD748	#C9B215
#  GREEN	#54EC48	#24BC14
#  BLUE	    #48C4EC	#1598C3
#  PINK	    #DE48EC	#B415C7
#  PURPLE	#7648EC	#4D18E4
?>
