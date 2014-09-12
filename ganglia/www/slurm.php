<?php
// Author: Kasper S. Eenberg (2014)
// Version: 1.0

$metrics = ["slurm_sdiag_jobs", "slurm_nodes", "slurm_hours_maxwait", "slurm_jobs_current", "slurm_users_current", "slurm_user_cores"];
$periods = ["hour", "2hr", "4hr", "day", "week", "month", "year"];

function getPeriodHeader($title) {
    $special_menu = "&nbsp;&nbsp;<select name=\"special\" id=\"special\">";
    $special_menu .= "    <option value=\"/slurm.php\">Slurm</option>";
    $special_menu .= "    <option value=\"/index.php\">Grid</option>";
    $special_menu .= "    <option value=\"/storage.php\">Storage</option>";
    $special_menu .= "</select>";
    $special_menu .= "<script type=\"text/javascript\">";
    $special_menu .= " var urlmenu = document.getElementById( 'special' );";
    $special_menu .= " urlmenu.onchange = function() {";
    $special_menu .= "      window.open( this.options[ this.selectedIndex ].value, '_self');";
    $special_menu .= " };";
    $special_menu .= "</script>";

	return '
<nav class="navbar navbar-default navbar-static-top" role="navigation">
       ' . $special_menu . '
  <div class="container container-fluid">
  	<a class="navbar-brand" href="slurm.php">' . $title . '</a>
  	<ul class="nav navbar-nav pull-right">
		<li><a data-role="hour" href="?page=overview&period=hour">Hour</a></li>
		<li><a data-role="2hr" href="?page=overview&period=2hr">2Hour</a></li>
		<li><a data-role="4hr" href="?page=overview&period=4hr">4Hour</a></li>
		<li><a data-role="day" href="?page=overview&period=day">Day</a></li>
		<li><a data-role="week" href="?page=overview&period=week">Week</a></li>
		<li><a data-role="month" href="?page=overview&period=month">Month</a></li>
		<li><a data-role="year" href="?page=overview&period=year">Year</a></li>
	</ul>
  </div>
</nav>';
}

function getGraph($period, $metric, $legend, $href) {
	if($legend) {
		$legend = "";
	} else {
		$legend = "&legend=hide";
	}
	return '
		<div class="col-lg-6 col-md-12">
		<div class="panel panel-info text-center">' .
			#<div class="panel-heading"><h3 class="panel-title">' . $metric . " - " . $period .'</h3></div>
			'<div class="panel-body">
				<a href="' . $href . '">
					<img src="graph.php?r=' . $period . $legend . '&z=normal&g=' . $metric . '">
				</a>
			</div>
		</div>
		</div>
		';
}

$page = $_GET["page"];
if ($page  == "overview" || $page == "") {

	$data = getPeriodHeader("GenomeDK Slurm Status");

	$period = $_GET["period"];
	if (!$period) {
		$period = "hour";
	}

	$data .= '
		<div class="container">
	';

	foreach ($metrics as $metric) {
		$data .= getGraph($period, $metric, 0, 'slurm.php?page=detail&metric=' . $metric);
	}

	$data .= '
		</div>
	';
        $data .= '
                <script type="text/javascript">
                        window.setTimeout(function() {
                                location.reload();
                        }, 60000);
                </script>
        ';


} elseif ($page == "detail") {
	$metric = $_GET['metric'];

	$data = getPeriodHeader("GenomeDK Slurm " . $metric . " Overview");

	$data .= '
		<div class="container">
	';

	foreach ($periods as $period) {
		$data .= getGraph($period, $metric, 1, 'graph.php?r=' . $period . '&z=xxlarge&g=' . $metric);
	}

	$data .= '
		</div>
	';
} else {
	$data = "Unknown page";
}
?><!doctype html>
<html>
<head>
	<style type="text/css">
		.center {
			text-align: center;
		}

		.offset200 {
			margin-left: 200px;
		}
	</style>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
</head>

<body>

<?php
echo $data;
?>

<script type="text/javascript">
$(document).ready(function(){
	// Highlight the active page.
	var r = /period=((:?\w|\d)+)/;

	var val = location.search.match(r);
	if(!val) {
		val = ['', 'hour'];
	}

	$('a[data-role="' + val[1] + '"]').parent().addClass("active");
});
</script>

</body>
</html>
