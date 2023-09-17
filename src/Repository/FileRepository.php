<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Repository;

class FileRepository
{
    public function getNginxTemplate(): string
    {
        return
'server {
    listen 80 default_server;
    listen [::]:80 default_server;

    server_name localhost;

    root {{root_directory}};
    index index.php index.html;

    location / {
        try_files $uri $uri/ =404;
        if (!-e $request_filename){
            rewrite ^/(.*) /index.php?r=$1 last;
        }
    }

    location ~* \.php$ {
        fastcgi_pass {{php_fpm}}:9000;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param SCRIPT_NAME $fastcgi_script_name;
    }
}';
    }
}
