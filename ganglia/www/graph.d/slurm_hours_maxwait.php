<?php
function graph_slurm_hours_maxwait ( &$rrdtool_graph ) {
	global $rrd_dir;
	
	$series =
		  "DEF:'normal'='${rrd_dir}/slurm_hours_maxwait_normal.rrd':'sum':AVERAGE "
		. "DEF:'express'='${rrd_dir}/slurm_hours_maxwait_express.rrd':'sum':AVERAGE "
		. "DEF:'fat1'='${rrd_dir}/slurm_hours_maxwait_fat1.rrd':'sum':AVERAGE "
		. "DEF:'fat2'='${rrd_dir}/slurm_hours_maxwait_fat2.rrd':'sum':AVERAGE "
		. "LINE2:'normal'#54EC48:'normal' "
		. "LINE2:'express'#EC9D48:'express' "
		. "LINE2:'fat1'#48C4EC:'fat1' "
		. "LINE2:'fat2'#EA644A:'fat2' "
		;
	
	//required
	$rrdtool_graph['title'] = '-- Slurm Max Waiting Time Per Queue --';
	$rrdtool_graph['vertical-label'] = 'hours';
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
