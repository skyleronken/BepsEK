
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
<?php
require_once('inc/functions/user.php');
include('inc/functions/RC4URL.php');
$enc = new URL_Encryption;
$title = 'Threads';
$exp = subscription_expire();
?>

<?php
if (is_post() && check_token($_POST['token'])) {
    switch ($_POST['action']) {
    case 'fileupdate':
        $result = flow_file_change($_POST['fid'],$_POST['file']);
	break;
	case 'flowdel':
        $result = flow_remove_stats($_POST['fid']);
    break;
    }
}

?>

<?php
require_once('inc/header.php');
?>
<?php
require_once('inc/hnav.php');
?>
<?php
require_once('inc/lnav.php');
?>
        <!--main content start-->
        <section class="main-content-wrapper">
            <section id="main-content">
			<?php
				if(isset($result)){
					echo ffile_result_parser($_POST['action'],$result);
				}
			?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Threads:</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table id="" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>File</th>
                                            <th>Options</th>
					    
                                        </tr>
                                    </thead>

                                    <tbody>
                                      
									<?php threads_show(); ?>
								
                                    </tbody>
                                </table>
                            
                            </div>
                        </div>
                    </div>
                </div>
                
                         
            </section>
        </section>
    
</div>
    <!--main content end-->
	<?php generate_thread_modals($enc); ?>
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
