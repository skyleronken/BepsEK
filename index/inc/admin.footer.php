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
        $('#userlist').dataTable();
        $('#hitslist').dataTable();
        $("#expiration").mask("99/99/9999");
        $("#newexpiration").mask("99/99/9999");
        app.morrisPie();
    });
    </script>

</body>

</html>
