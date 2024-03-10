<?php
/* 
main nextcloud config file
  on host >> podman unshare vi ~/.local/share/containers/storage/volumes/ncapp/_data/config/config.php 
  in NextcloudContainer >> cat /var/www/html/config/config.php
*/
$CONFIG = array (
  'htaccess.RewriteBase' => '/',
  'memcache.local' => '\OC\Memcache\APCu',
  'memcache.locking' => '\OC\Memcache\Redis',
  'memcache.distributed' => '\OC\Memcache\Redis',
  'redis' =>
  array (
    'host'         => 'localhost',
    'port'         => 7777,
    'user'         => '',
    'password'     => 'strongpassword',
  ),
  'apps_paths' => 
  array (
    0 => 
    array (
      'path' => '/var/www/html/apps',
      'url' => '/apps',
      'writable' => false,
    ),
    1 => 
    array (
      'path' => '/var/www/html/custom_apps',
      'url' => '/custom_apps',
      'writable' => true,
    ),
  ),
  'upgrade.disable-web' => true,
  'passwordsalt' => 'saltedstrongpassword',
  'secret' => 'strongpasswordsecret',
  'trusted_domains' => 
  array (
    0 => 'localhost',
    1 => '10.8.0.2',
    2 => '192.168.1.2',
  ),
  'datadirectory' => '/var/www/html/data',
  'dbtype' => 'mysql',
  'version' => '28.0.3.2',
  'overwrite.cli.url' => 'http://localhost',
  'dbname' => 'nextcloud',
  'dbhost' => '127.0.0.1:3306',
  'dbport' => '',
  'dbtableprefix' => 'oc_',
  'mysql.utf8mb4' => true,
  'dbuser' => 'ncuser',
  'dbpassword' => 'strongpassword',
  'installed' => true,
  'default_phone_region' => 'CH',
  'instanceid' => 'ocep5p2mw7tc',
  'mail_smtpmode' => 'smtp',
  'mail_smtpauth' => 1,
  'mail_sendmailmode' => 'smtp',
  'mail_from_address' => 'somegmail.cron',
  'mail_domain' => 'gmail.com',
  'mail_smtphost' => 'smtp.gmail.com',
  'mail_smtpport' => '587',
  'mail_smtpname' => 'somegmail.cron@gmail.com',
  'mail_smtppassword' => 'tokenpassword',
  'log_type' => 'file',
  'logfile' => 'nextcloud.log',
  'loglevel' => 2,
  'maintenance' => false,
  'maintenance_window_start' => 3,
);
