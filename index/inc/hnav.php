<header id="header">
            <!--logo start-->
            <div class="brand">
                <a href="index.php" class="logo"><span><?php echo $config['main']['header1'] ?></span><?php echo $config['main']['header2'] ?></a>
            </div>
            <!--logo end-->
            <div class="toggle-navigation toggle-left">
                <button type="button" class="btn btn-default" id="toggle-left" data-toggle="tooltip" data-placement="right" title="Toggle Navigation">
                    <i class="fa fa-bars"></i>
                </button>
            </div>
            <div class="user-nav">
                <ul>
                    <li class="dropdown messages">
                        <?php if (!empty($_SESSION['notifications'])) {
                            echo '<span class="badge badge-danager animated bounceIn" id="new-messages">'.count($_SESSION['notifications']).'</span>';
                        }
                        ?>
                        <button type="button" class="btn btn-default dropdown-toggle options" id="toggle-mail" data-toggle="dropdown">
                            <i class="fa fa-envelope"></i>
                        </button>
                        <ul class="dropdown-menu alert animated fadeInDown">
                            <li>
                                <h1>You have <strong><?php if (!empty($_SESSION['notifications'])) { echo count($_SESSION['notifications']); } else { echo '0'; } ?></strong> new notifications</h1>
                            </li>
                            <?php show_notifications() ?>
                        </ul>

                    </li>
                    <li class="dropdown settings">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <?php echo $_SESSION['name'] ?> <i class="fa fa-angle-down"></i>
                    </a>
                        <ul class="dropdown-menu animated fadeInDown">
                            <li>
                                <a href="logout.php?v=<?php echo $_SESSION['csrf'] ?>"><i class="fa fa-power-off"></i> Logout</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </header>
