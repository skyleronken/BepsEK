<!--sidebar left start-->
        <nav class="sidebar sidebar-left">
            <h5 class="sidebar-header">Navigation</h5>
            <ul class="nav nav-pills nav-stacked">
                <li<?php if ($script == 'admin.php') { echo ' class="active"'; }?>>
                    <a href="admin.php" title="Dashboard">
                        <i class="icon-speedometer"></i> Dashboard
                    </a>
                </li>
                <li<?php if ($script == 'users.php') { echo ' class="active"'; }?>>
                    <a href="users.php" title="Users">
                        <i class="icon-users"></i> Users
                    </a>
                </li>
                <li<?php if ($script == 'domains.php') { echo ' class="active"'; }?>>
                    <a href="domains.php" title="Domains">
                        <i class="fa fa-link"></i> Domains
                    </a>
                </li>
				<li<?php if ($script == 'vds.php') { echo ' class="active"'; }?>>
                    <a href="vds.php" title="Proxy Server">
                        <i class="fa fa-cubes"></i> Proxy Server
                    </a>
                </li>
            </ul>
        </nav>
        <!--sidebar left end-->