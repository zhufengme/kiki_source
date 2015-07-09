#!/usr/bin/php -q
<?php
//  #!/usr/bin/php -q            centos
//  #!/usr/bin/php-cgi -q		 ubuntu
set_time_limit (0);
include_once 'core/system/application.php';
application::console_start($argv,$argc);