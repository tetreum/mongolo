## Mongo.lo

BETA|EARLY ACCESS|WIP

My RockMongo replacement.

![mongolo](https://raw.githubusercontent.com/tetreum/mongolo/master/screenshots/1.jpg)

## Features
- For queries: field autocomplete
- Asks for confirmation before any delete action
- Supports all mongo index types
- Saves latest executed query
- Uses the newest mongo driver for php (mongodb)

## Requirements
- PHP >= 5.6
- PHP mongodb extension (http://php.net/manual/en/mongodb.installation.pecl.php)

# Setup

1. Open https://github.com/tetreum/mongolo/releases/latest and copy mongolo.tar.gz url. 
    
    Ex:  ```wget https://github.com/tetreum/mongolo/releases/download/v1/mongolo.tar.gz```
2. Uncompress somewhere: ```mkdir /var/www/mongolo && tar -zxvf mongolo.tar.gz -C /var/www/mongolo```
3. Make it accessible on your webserver [Point all queries to htdocs/index.php, and static file queries to htdocs/].
 
Nginx example:
```
server {
    listen   80;
    server_name  mongo.dev;
    access_log  /var/log/nginx/mongolo.access.log;
    error_log   /var/log/nginx/mongolo.error.log;

    charset utf-8;

    sendfile off;

    ## Images and static content
    location ~* ^.+\.(jpg|jpeg|gif|css|png|js|ico|xml|html|htm|txt|json|eot|woff|ttf|svg)$ {
      access_log        off;
      expires           30d;
      root /var/www/mongolo/htdocs;
    }


    ## The "application" requests should be processed by Slim
    location / {
        include fastcgi_params;
        fastcgi_pass backend;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME /var/www/mongolo/htdocs/index.php;
        fastcgi_param SCRIPT_NAME /index.php;
        fastcgi_param REQUEST_URI $request_uri;
        fastcgi_intercept_errors on;
        fastcgi_ignore_client_abort off;
        fastcgi_connect_timeout 60;
        fastcgi_send_timeout 180;
        fastcgi_read_timeout 180;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
    }
    ## Disable viewing .htaccess & .htpassword
    location ~ /\.ht {
        deny  all;
    }
}
```

Apache example:
```
<VirtualHost *:80>
        DocumentRoot /var/www/mongolo/htdocs
        ServerName mongo.dev
        CustomLog /var/log/apache2/mongolo.access.log Combined
        ErrorLog /var/log/apache2/monoglo.error.log
        DirectoryIndex index.php
        <Directory />
                Options FollowSymLinks
                AllowOverride All
        </Directory>
        <Directory /var/www/mongolo/htdocs>
                Options Indexes FollowSymLinks MultiViews
                AllowOverride All
                Order allow,deny
                allow from all
        </Directory>
</VirtualHost>
```
 
4. Move conf.sample.php to conf.php: ```cd /var/www/mongolo && mv conf.sample.php conf.php```
5. Edit conf.php

# Conf.php FAQ

Mongo.lo can use mongodb users for authentication or use a local system.

If your mongo doesn't have authentication, setup a local one:

1. Add the following code in conf.php
```php
 "local_auth" => [
    "salt" => "PUT_A_RANDOM_SALT_HERE",
    "users" => [
            "demo" => "HASH" // MD5(salt.password)
    ],
],
```

If you only have access to a single db:

1. In conf.php -> "mongo" add:
```php
 "db" => "ALLOWED_DB",
```

Example:
```php
    "mongo" => [
        "db" => "salmon",
        "ip" => "127.0.0.1"
    .....
```

# ToDo
- Check if it works served as: [domain.com/mongolo | mongolo.domain.com | mongolo.dev]
- Finish indexes section
- Results pagination
- Import/Export methods
- Simple installer

# ¿Do you wanna some feature?
¡That's easy!

1. Fork the repo
2. Make it
3. Send the PR

# ¿Found a bug?
¡My bad!

1. Fork the repo
2. Fix it
3. Send the PR

# ¿How i build this repo for development?
Check .travis.yml file which has the required steps.

## Special thanks

- MLab (https://mlab.com): Free MongoDB server used to build this project.
- Codemirror devs
- hjson devs
- All devs who created the packages that i'm using in composer.json
