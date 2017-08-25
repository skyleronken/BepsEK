<!--sidebar left start-->
        <nav class="sidebar sidebar-left">
            <h5 class="sidebar-header">Navigation</h5>
            <ul class="nav nav-pills nav-stacked">
                <li<?php if ($script == 'index.php') { echo ' class="active"'; }?>>
                    <a href="index.php" title="Dashboard">
                        <i class="icon-speedometer"></i> Dashboard
                    </a>
                </li>
                <li<?php if ($script == 'files.php') { echo ' class="active"'; }?>>
                    <a href="files.php" title="Files">
                        <i class="fa fa-files-o"></i> Files
                    </a>
                </li>
                <li<?php if ($script == 'threads.php') { echo ' class="active"'; }?>>
                    <a href="threads.php" title="Threads">
                        <i class="fa fa-tasks"></i> Threads
                    </a>
                </li>
				<li>
                    <a style="pointer-events: none; cursor: default;" href="" title="Experation">
                        <h4><span class="label label-default"> Experation: <?php echo $exp; ?></span></h4>
                    </a>
                </li>
            </ul>
        </nav>
        <!--sidebar left end-->