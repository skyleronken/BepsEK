
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
<?php $title = 'Files' ?>
<?php 
require_once('inc/functions/user.php'); 
$exp = subscription_expire();
?>
<?php if (is_post() && check_token($_POST['token'])) {
    switch ($_POST['action']) {
    case 'upload':
        $result = file_upload($_POST['desc'], $_FILES);
    break;
    case 'url':
        $result = file_url($_POST['desc'], $_POST['url']);
    break;
    case 'scan':
        echo file_scan($_POST['file_scan']);
		exit;
    break;
	case 'filedel':
		delete_file($_POST['file']);
    break;
    }
}

?>

<?php require_once('inc/header.php') ?>
<?php require_once('inc/hnav.php') ?>
<?php require_once('inc/lnav.php') ?>
        <!--main content start-->
        <section class="main-content-wrapper">
            <section id="main-content">            
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Current file</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                            <?php if (file_exist()) { ?>
                                <table id="fileslist" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Hash</th>
                                            <th>Size</th>
                                            <th>Timestamp</th>
                                            <th>Description</th>
											<th>AV</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php file_show() ?>
										<tr>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
										</tr>
                                    </tbody>
                                </table>
                            <?php } else { ?>
                                <p>You haven't upload any file yet.</p>
                            <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Upload File</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                                <div class="panel-body">
                                <form role="form" enctype="multipart/form-data" method="post" action="">
                                    <div class="form-group">
                                        <label for="desc">Description</label>
                                        <input type="text" class="form-control" id="desc" placeholder="Enter a brief description (not required)" name="desc">
                                    </div>
                                    <div class="form-group">
                                        <label for="file">File</label>
                                        <input type="file" id="file" name="file" required>
                                        <p class="help-block">If a file already exist it will be replaced.</p>
                                    </div>
                                    <input type="hidden" name="action" value="upload" />
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf'] ?>" />
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>

                            </div>
                                <div class="panel-body">
                                <form role="form" enctype="multipart/form-data" method="post" action="">
                                    <div class="form-group">
                                        <label for="desc">Description</label>
                                        <input type="text" class="form-control" id="desc" placeholder="Enter a brief description (not required)" name="desc">
                                    </div>
                                    <div class="form-group">
                                        <label for="file">File</label>
                                        <input type="text" class="form-control" id="url" placeholder="Enter a url of file" name="url">
                                        <p class="help-block">If a file already exist it will be replaced.</p>
                                    </div>
                                    <input type="hidden" name="action" value="url" />
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
                                <h3 class="panel-title">Scan File</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
      

                   <div class="panel-body">
                   <?php if (file_exist()) { ?>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script>

$(document).ready(function(){
    $("#button").click(function(){
		$('#button').addClass('disabled');
		$("#button").html('Scanning...');
        $("#area").html("<img src='load.gif' alt='description' />");
		$.post("",
			{
			file_scan: $('#scanfile').val(),
			action: 'scan',
			token: '<?php echo $_SESSION['csrf']; ?>'
			},
			function(data,status){
			$('#result').html(data);
			$("#area").remove();
			$('#button').removeClass('disabled');
			$("#button").html('Scan file');
			});
    });
});
</script>
									<?php echo generate_scan_dropdown(); ?>
                                    <button id="button" type="submit" class="btn btn-primary">Scan file</button>
                                
                                <?php } else { ?>
                                <p>You haven't uploaded any file yet.</p>
                                <?php } ?>
                            </div>
                            </div>
                        </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Report Scan File</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
					<div id="area" style="text-align:center;"></div>
					<div id="result" ></div>
                            </div>
                            </div>
                            </div>
							 <div class="col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h3 class="panel-title">Delete File</h3>
										<div class="actions pull-right">
											<i class="fa fa-chevron-down"></i>
											<i class="fa fa-times"></i>
										</div>
									</div>
									<div class="panel-body">
									<?php echo generate_del_file_dropdown(); ?>
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
    <script src="static/js/application.files.js"></script>
    <!--Load these page level functions-->
    <script>
    $(document).ready(function() {
    });
    </script>

</body>

</html>
