<?php if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start(); ?>
<?php require_once('inc/config.php') ?>
<?php if (!isset($_SESSION['admin']) or $_SESSION['admin'] != true) {
    header('Location: index.php');
    die();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php $title = 'Admin' ?>
<?php require_once('inc/functions/admin.php') ?>
<?php if (is_post() && check_token($_POST['token'])) {
    switch ($_POST['action']) {
    case 'clear_stats':
        clear_stats();
    break;
    case 'clear_users':
        clear_users();
    break;
    case 'clear_domains':
        clear_domains();
    break;
    case 'clear_scans':
        clear_scans();
    break;
    case 'clear_files':
        clear_files();
    break;
    case 'clear_exploit':
        clear_exploit();
    break;
    case 'nuke':
        nuke();
    break;
    }
}

?>
<?php require_once('inc/admin.header.php') ?>
<?php require_once('inc/admin.hnav.php') ?>
<?php require_once('inc/admin.lnav.php') ?>

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
        <!--main content start-->
        <section class="main-content-wrapper">
            <section id="main-content">
                <!--tiles start-->
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="dashboard-tile detail tile-red">
                            <div class="content">
                                <h1 class="text-left timer" data-from="0" data-to="<?php echo total_users()?>" data-speed="2500"> </h1>
                                <p>Users</p>
                            </div>
                            <div class="icon"><i class="fa fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="dashboard-tile detail tile-turquoise">
                            <div class="content">
                                <h1 class="text-left timer" data-from="0" data-to="<?php echo total_hits() ?>" data-speed="2500"> </h1>
                                <p>Hits</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="dashboard-tile detail tile-blue">
                            <div class="content">
                                <h1 class="text-left timer" data-from="0" data-to="<?php echo total_exploited() ?>" data-speed="2500"> </h1>
                                <p>Exploited</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="dashboard-tile detail tile-purple">
                            <div class="content">
                                <h1 class="text-left timer" data-to="<?php echo total_domains() ?>" data-speed="2500"> </h1>
                                <p>Domains</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!--tiles end-->
                <!--dashboard charts and map start-->
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
                                <form role="form" method="post" action="">
                                    <input type="hidden" name="action" value="clear_users" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Clear users</button>
                                </form>
                                <form role="form" method="post" action="">
                                    <input type="hidden" name="action" value="clear_domains" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Clear domains</button>
                                </form>
                                <form role="form" method="post" action="">
                                    <input type="hidden" name="action" value="clear_files" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Clear files</button>
                                </form>
                                <form role="form" method="post" action="">
                                    <input type="hidden" name="action" value="clear_scans" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Clear scans</button>
                                </form>
                                <form role="form" method="post" action="">
                                    <input type="hidden" name="action" value="clear_scans" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Clear exploit</button>
                                </form>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Nuke</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <form role="form" method="post" action="">
                                    <input type="hidden" name="action" value="nuke" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Nuke</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
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
                                            <th>Owner</th>
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
                                        <h3 class="panel-title">Server Status</h3>
                                        <div class="actions pull-right">
                                            <i class="fa fa-chevron-down"></i>
                                            <i class="fa fa-times"></i>
                                        </div>
                                    </div>
                                    <div class="panel-body">

                                        <span class="sublabel">CPU Usage </span>
                                        <div class="progress progress-striped">
                                            <div class="progress-bar progress-bar-default" style="width: <?php echo load_percentage() ?>%"><?php echo load_percentage() ?>%</div>
                                        </div>
					
					<span class="sublabel">RAM Usage </span>
                                        <div class="progress progress-striped">
                                            <div class="progress-bar progress-bar-success" style="width: <?php echo get_server_memory_usage() ?>%"><?php echo get_server_memory_usage() ?>%</div>
                                        </div>
					
                                        <span class="sublabel">Disk Usage </span>
                                        <div class="progress progress-striped">
                                            <div class="progress-bar progress-bar-primary" style="width: <?php echo disk_percentage() ?>%"><?php echo disk_percentage() ?>%</div>
                                        </div>

                                    </div>
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
    <script src="static/js/application.admin.js"></script>
    <!--Page Level JS-->
    <script src="static/plugins/countTo/jquery.countTo.js"></script>
    <script src="static/plugins/dataTables/js/jquery.dataTables.js"></script>
    <script src="static/plugins/dataTables/js/dataTables.bootstrap.js"></script>
    <script src="static/plugins/mask/js/jquery.maskedinput.min.js"></script>
    <!-- Morris  -->
    <script src="static/plugins/morris/js/morris.min.js"></script>
    <script src="static/plugins/morris/js/raphael.2.1.0.min.js"></script>
    <!--Load these page level functions-->
    <script>
    $(document).ready(function() {
        app.timer();
        $('#hitslist').dataTable();
        app.morrisPie();
    });
    </script>

</body>

</html>
