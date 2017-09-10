# CRUD Example
Design: Christian Albert

How to setup this

1. Go to your virtual host file of your apache vhost config. You can find this file in most cases here:

```
/etc/apache2/2.4/extra/httpd-vhosts.conf
/usr/local/etc/apache2/2.4/extra/httpd-vhosts.conf
```

2. Insert a vhost scheme for your localhost in apaches vhost config. Don't forget to replace the path to the project root of your local machine.

```
<VirtualHost *:80>
    DocumentRoot "/Library/WebServer/Documents/crud"
    ServerName crud.dev
</VirtualHost>
```

3. Add this line to your hosts file. You can find this file in most cases here: /etc/hosts

```
127.0.0.1       crud.dev
```

4. Make sure, that apaches mod_rewrite is activated in your apache config: /etc/apache2/2.4/httpd.conf

```
LoadModule rewrite_module libexec/mod_rewrite.so
```

5. Restart your apache

```
sudo apachectl restart
```

6. Execute crud.sql in your local database

7. Create/Update credentials.ini file: /php/inc/credentials.ini

```
[crud]
host = "localhost"
username = "root";
pass = "";
db_name = "crud";
```

8. composer install (Check required stuff in composer.json)