<VirtualHost *:80>
     ServerName zf.com
     DocumentRoot C:/wamp64/www/zf
     SetEnv APPLICATION_ENV "development"
     <Directory C:/wamp64/www/zf>
         DirectoryIndex index.php
         AllowOverride All
         Require all granted
     </Directory>
 </VirtualHost>