<?php
$output=shell_exec('service mysql restart');
$output2=shell_exec('service nginx restart');
$output3=shell_exec('apt-get autoclean');
$output4=shell_exec('apt-get clean');
$output5=shell_exec('find /var/log -type f -delete');
$output6=shell_exec('chmod -R 777 /var/www/html/index/tmp');
echo $output;
echo $output2;
echo $output3;
echo $output4;
echo $output5;
echo $output6;
exit;
?>


