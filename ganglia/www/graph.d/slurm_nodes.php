<?php
function graph_slurm_nodes ( &$rrdtool_graph ) {
	global $rrd_dir;
	
	$series =
		  "DEF:'allocated'='${rrd_dir}/slurm_nodes_allocated.rrd':'sum':AVERAGE "
		. "DEF:'idle'='${rrd_dir}/slurm_nodes_idle.rrd':'sum':AVERAGE "
		. "DEF:'drained'='${rrd_dir}/slurm_nodes_drained.rrd':'sum':AVERAGE "
		. "DEF:'down'='${rrd_dir}/slurm_nodes_down.rrd':'sum':AVERAGE "
		. "AREA:'allocated'#54EC48:'allocated' "
		. "STACK:'idle'#4D18E4:'idle' "
		. "STACK:'drained'#48C4EC:'drained' "
		. "STACK:'down'#EA644A:'down' "
		;
	
	//required
	$rrdtool_graph['title'] = '-- Slurm Node Status --';
	$rrdtool_graph['vertical-label'] = 'nodes';
	$rrdtool_graph['series'] = $series;
	$rrdtool_graph['lower-limit'] = '0';
	$rrdtool_graph['extras'] = "-R light -M";

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
