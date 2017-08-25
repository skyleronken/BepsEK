
<?php

require_once('inc/config.php');
?>
<?php 
require_once('inc/functions/public.php');
if (!isset($_GET['i']) || empty($_GET['i']) || token_compare($_GET['i']) === false) {
    die("Invalid public stats token");
} elseif(token_compare($_GET['i']) !== false) {
    $id = token_compare($_GET['i']); 
}

?>
<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Beps | Public Stats</title>
    <meta name="description" content="">
    <meta name="author" content="Yugoslavian Business Network | Kriminalac">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <!-- Favicon -->
    <link rel="shortcut icon" href="static/img/favicon.ico" type="image/x-icon">
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="static/css/bootstrap.min.css">
    <!-- Font Icons -->
    <link rel="stylesheet" href="static/css/font-awesome.min.css">
    <link rel="stylesheet" href="static/css/simple-line-icons.css">
    <!-- CSS Animate -->
    <link rel="stylesheet" href="static/css/animate.css">
    <!-- Switchery -->
    <link rel="stylesheet" href="static/plugins/switchery/switchery.min.css">
    <!-- Custom styles for this theme -->
    <link rel="stylesheet" href="static/css/main.css">
    <!-- Morris  -->
    <link rel="stylesheet" href="static/plugins/morris/css/morris.css">
    <!-- Ammap -->
    <link rel="stylesheet" href="static/plugins/ammap/ammap.css" type="text/css">
    <!-- Fonts -->
    <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,900,300italic,400italic,600italic,700italic,900italic' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
    <!-- Feature detection -->
    <script src="static/js/modernizr-2.6.2.min.js"></script>
    <!-- Coded by Yugoslavian Business Network | User: Kriminalac@default.rs -->
</head>
<body class="off-canvas">
    <div id="container">
        <nav class="sidebar sidebar-left">
<h3 class="panel-title"><font color="white">Contact:beps-support@xmpp.jp</font></h3>

        </nav>
        <!--sidebar left end--><script type="text/javascript">
    var morrisPie = function() {

        Morris.Donut({
            element: 'browser-donut',
            data: [<?php morris_browser_donut($id) ?>],
            colors: [
                '#1abc9c',
                '#293949',
                '#e84c3d',
                '#3598db',
                '#2dcc70',
                '#f1c40f'
            ]
        });
    }
</script>
<script src="static/plugins/ammap/ammap.js" type="text/javascript"></script>
<script src="static/plugins/ammap/worldLow.js" type="text/javascript"></script>
<script type="text/javascript">
            AmCharts.makeChart("mapdiv", {
                type: "map",


                colorSteps: 10,

                dataProvider: {
                    map: "worldLow",
                    areas: [<?php show_map($id) ?>]
                },

                areasSettings: {
                    autoZoom: true
                },

                valueLegend: {
                    right: 10,
                    minValue: "Low value",
                    maxValue: "High value"
                }

            });
        </script>
        <!--main content start-->
                <section class="main-content-wrapper">
            <section id="main-content">
                <!--tiles start-->
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="dashboard-tile detail tile-red">
                            <div class="content">
                                <h1 class="text-left timer" data-from="0" data-to="<?php echo hits($id) ?>" data-speed="2500"> </h1>
                                <p>Hits</p>
                            </div>
                            <div class="icon"><i class="fa fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="dashboard-tile detail tile-turquoise">
                            <div class="content">
                                <h1 class="text-left timer" data-from="0" data-to="<?php echo rate($id) ?>" data-speed="2500"> </h1>
                                <p>Exploited</p>
                            </div>
                            <div class="icon"><i class="fa fa-bug"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="dashboard-tile detail tile-blue">
                            <div class="content">
                                <h1 class="text-left timer" data-from="0" data-to="<?php echo countries_total($id) ?>" data-speed="2500"> </h1>
                                <p>Countries</p>
                            </div>
                            <div class="icon"><i class="fa fa-flag"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="dashboard-tile detail tile-purple">
                            <div class="content">
                                <h1 class="text-left timer" data-to="<?php echo rate_percentage($id) ?>" data-speed="2500"> </h1>
                                <p>Rate %</p>
                            </div>
                            <div class="icon"><i class="fa fa-line-chart"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!--tiles end-->
                <!--dashboard charts and map start-->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Heatmap</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div id="mapdiv" style="width: 100%; background-color:#EEEEEE; height: 500px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Top 10 Countries</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table class="table">
                                  <thead>
                                    <tr>
                                      <th>#</th>
                                      <th>Country</th>
                                      <th>Hits</th>
                                    </tr>
                                  </thead>
                                  <tbody><?php countries($id) ?></tbody>
                                </table>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Browsers</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div id="browser-donut"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    
</div>
    <!--main content end-->
    <!--Global JS-->
    <script src="static/js/jquery-1.10.2.min.js"></script>
    <script src="static/js/bootstrap.min.js"></script>
    <script src="static/plugins/navgoco/jquery.navgoco.min.js"></script>
    <script src="static/plugins/waypoints/waypoints.min.js"></script>
    <script src="static/plugins/switchery/switchery.min.js"></script>
    <script src="static/js/application.js"></script>
    <!--Page Level JS-->
    <script src="static/plugins/countTo/jquery.countTo.js"></script>
    <script src="static/plugins/weather/js/skycons.js"></script>
    <script src="static/plugins/dataTables/js/dataTables.bootstrap.js"></script>
    <script src="static/plugins/morris/js/morris.min.js"></script>
    <script src="static/plugins/morris/js/raphael.2.1.0.min.js"></script>
    <!--Load these page level functions-->
    <script>
    $(document).ready(function() {
        app.timer();
        app.morrisPie();
    });
    </script>

</body>

</html>