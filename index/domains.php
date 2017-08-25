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
    case 'addmaster':
        $result = create_domain($_POST['name']);
    break;
    case 'addmassmaster':
        $result = create_mass_domain($_POST['lists']);
    break;
    case 'add':
        $result = domains_server_add($_POST['domain'],$_POST['description']);
    break;
	case 'mass_add':
        $result = domains_server_add_mass($_POST['domains']);
    break;
    case 'remove':
        $result = domains_server_remove($_POST['domains']);
    break;
    case 'removemaster':
        $result = remove_domains($_POST['domain']);
    break;
    }
}
?>

<?php require_once('inc/admin.header.php') ?>
<?php require_once('inc/admin.hnav.php') ?>
<?php require_once('inc/admin.lnav.php') ?>
		<!-- additional header shit -->
		  <script src="static/js/jquery-1.10.2.min.js"></script>
		  <style type="text/css">
			.panel-heading h4 {
			  white-space: nowrap;
			  overflow: hidden;
			  text-overflow: ellipsis;
			  line-height: normal;
			  width: 50%;
			  padding-top: 4px;
			}
		  </style>
		  <script>
			$(function(){
			  $("#allcb").click(function() {
				var chkBoxes = $("input[id^=cb]");
				chkBoxes.prop("checked", !chkBoxes.prop("checked"));
			  });
			  $("#alld").click(function() {
				var chkBoxes = $("input[id^=d]");
				chkBoxes.prop("checked", !chkBoxes.prop("checked"));
			  });
			}
			 );
		  </script>
		<!-- additional header shit end -->
        <!--main content start-->
        <section class="main-content-wrapper">
            <section id="main-content">            
			<?php
				if(isset($result)){
					echo domains_result_parser($_POST['action'],$result);
				}
			?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
							<h4 class="panel-title pull-left">
							  Proxy Domains					
							</h4>
							<div class="pull-right">
							  <form action="" method="POST">
								<button type="button" id="allcb" class="btn btn-default">Select/Deselect all</button>
								<input type="submit" style="margin-left:5px;" class="btn btn-danger" value="Delete selected" />
								</div>
							  <div class="clearfix">
							  </div>
							</div>
                            <div class="panel-body">
                            <?php if (domains_server_exist()) { ?>
                                <table id="fileslist" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Domain</th>
											<th>Description</th>
                                            <th>Last Checked</th>
											<th class="text-center">
											  <i style="font-size:16px;" class="fa fa-trash" aria-hidden="true">
											  </i>
											</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php domains_server_show() ?>
                                    </tbody>
                                </table>
								<input type="hidden" name="action" value="remove" />
                                <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
								</form>
                            <?php } else { ?>
                                <p>There isn't any domain configured on the server.</p>
                            <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Master domains</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                                <div class="panel-body">

				<form action="" method="POST">
				<button type="button" id="alld" class="btn btn-default">Select/Deselect all</button>
				<input type="submit" class="btn btn-danger" value="Delete selected" />

                                <table id="fileslist" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                <th>Domain</th>
				<th class="text-center">
				<i style="font-size:16px;" class="fa fa-trash" aria-hidden="true">
				</i>
				</th>
                                </tr>
                                </thead>

                                <tbody>
                                	<?php list_domains() ?>
                                </tbody>
                                </table>

				<input type="hidden" name="action" value="removemaster" />
                                <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
				</form>

				</div>
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
										<label for="description">Description</label>
										<textarea id="description" name="description" placeholder="Description is optional..." class="form-control"></textarea>
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
                                <h3 class="panel-title">Mass add domain</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                                <div class="panel-body">
                                <form role="form" method="post" action="">
                                    <div class="form-group">
                                        <label for="domains">Domains</label>
										<textarea id="domains" name="domains" placeholder="One domain per line" class="form-control"></textarea>
									</div>
                                    <input type="hidden" name="action" value="mass_add" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>

                            </div>
                            </div>
                        </div>
		</div>
		</div>

		<div class="row">
		<div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Add Master domain</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                                <div class="panel-body">
                                <form role="form" method="post" action="">
                                    <div class="form-group">
                                        <label for="name">Domain</label>
                                        <input type="text" class="form-control" id="name" placeholder="The domain name of master without http" name="name">
				    </div>
                                    <input type="hidden" name="action" value="addmaster" />
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
                                <h3 class="panel-title">Add Mass Master domain</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                                <div class="panel-body">
                                <form role="form" method="post" action="">
                                    <div class="form-group">
                                        <label for="lists">Domains</label>
					<textarea id="lists" name="lists" placeholder="One domain per line" class="form-control"></textarea>
				    </div>
                                    <input type="hidden" name="action" value="addmassmaster" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>
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
    <script src="static/js/application.domains.js"></script>
    <!--Load these page level functions-->
    <script>
    $(document).ready(function() {
    });
    </script>

</body>

</html>
