<?php
function graph_slurm_sdiag_jobs ( &$rrdtool_graph ) {
	global $rrd_dir;
	
	$series =
		  "DEF:'submitted'='${rrd_dir}/slurm_jobs_submitted.rrd':'sum':AVERAGE "
		. "DEF:'started'='${rrd_dir}/slurm_jobs_started.rrd':'sum':AVERAGE "
		. "DEF:'cancelled'='${rrd_dir}/slurm_jobs_cancelled.rrd':'sum':AVERAGE "
		. "DEF:'failed'='${rrd_dir}/slurm_jobs_failed.rrd':'sum':AVERAGE "
		. "DEF:'completed'='${rrd_dir}/slurm_jobs_completed.rrd':'sum':AVERAGE "
		. "AREA:'cancelled'#ECD748:'cancelled' "
		. "STACK:'failed'#EA644A:'failed' "
		. "STACK:'completed'#54EC48:'completed' "
		#. "LINE2:'cancelled'#C9B215 "
		#. "LINE2:'failed'#CC3118 "
		#. "LINE2:'completed'#24BC14 "

		. "LINE2:'submitted'#48C4EC:'submitted' "
		. "LINE2:'started'#7648EC:'started' "
		;
	
	//required
	$rrdtool_graph['title'] = '-- Slurm Jobs Since SDIAG Reset --';
	$rrdtool_graph['vertical-label'] = 'jobs';
	$rrdtool_graph['series'] = $series;
	$rrdtool_graph['lower-limit'] = '0';
	$rrdtool_graph['extras'] = '-R light';

	return $rrdtool_graph;
}
#  RED      #EA644A #CC3118
#  #  ORANGE   #EC9D48 #CC7016
#  #  YELLOW   #ECD748 #C9B215
#  #  GREEN    #54EC48 #24BC14
#  #  BLUE     #48C4EC #1598C3
#  #  PINK     #DE48EC #B415C7
#  #  PURPLE   #7648EC #4D18E4
#
?>
