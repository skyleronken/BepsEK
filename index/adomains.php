<?php require_once('inc/config.php') ?>
<?php if (!isset($_SESSION['admin']) or $_SESSION['admin'] != true) {
    header('Location: index.php');
    die();
}
?>
<?php $title = 'Domains' ?>
<?php require_once('inc/functions/admin.php') ?>
<?php if (is_post() && check_token($_POST['token'])) {
    switch ($_POST['action']) {
    case 'add':
        $result = domains_server_add($_POST['domain']);
    break;
    case 'remove':
        $result = domains_server_remove($_POST['domain']);
    break;
    }
}
?>

<?php require_once('inc/admin.header.php') ?>
<?php require_once('inc/admin.hnav.php') ?>
<?php require_once('inc/admin.lnav.php') ?>
        <!--main content start-->
        <section class="main-content-wrapper">
            <section id="main-content">            
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Server domains</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                            <?php if (domains_server_exist()) { ?>
                                <table id="fileslist" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Domain</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php domains_server_show() ?>
                                    </tbody>
                                </table>
                            <?php } else { ?>
                                <p>There isn't any domain configured on the server.</p>
                            <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Add domain</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                                <div class="panel-body">
                                <form role="form" method="post" action="">
                                    <div class="form-group">
                                        <label for="domain">Domain</label>
                                        <input type="text" class="form-control" id="domain" placeholder="The domain needs to be already registered and configured" name="domain">
                                    </div>
                                    <input type="hidden" name="action" value="add" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>

                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Remove domain</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                                <div class="panel-body">
                                <form role="form" method="post" action="">
                                    <div class="form-group">
                                        <label for="domain">Domain</label>
                                        <input type="text" class="form-control" id="domain" placeholder="Enter the domain to be destroyed" name="domain">
                                    </div>
                                    <input type="hidden" name="action" value="remove" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Submit</button>
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
    <script src="static/js/application.domains.js"></script>
    <!--Load these page level functions-->
    <script>
    $(document).ready(function() {
    });
    </script>

</body>

</html>
