# **Laravel-TianXingBot**

- PHP7.3+
- Composer
- MySQL5.5+
- Redis
- Laravel

## 1.安装PHP环境
```
apt -y update && apt -y install curl wget git unzip nginx mariadb-server redis-server supervisor vim
```

```
apt -y install php7.3-common php7.3-cli php7.3-fpm \
php7.3-gd php7.3-mysql php7.3-mbstring php7.3-curl \
php7.3-xml php7.3-xmlrpc php7.3-zip php7.3-intl \
php7.3-bz2 php7.3-bcmath php-redis php7.3-fileinfo php-gmp
```

如果系统没有php包 运行以下命令安装 然后运行安装PHP 安装完成后会出现多个版本需要设置默认版本
```
apt -y install apt-transport-https lsb-release ca-certificates curl wget && wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg && sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list' && apt update
```

多个php版本的情况查看php版本
```
update-alternatives --config php
```

设置默认版本
```
update-alternatives --set php /usr/bin/php7.3
```

## 2.设置启动
```
systemctl enable --now nginx mariadb redis-server php7.3-fpm
systemctl restart php7.3-fpm
```

## 3.mysql初始化
```
mysql_secure_installation
```

## 4.创建mysql用户
账号：tgBot     # 自己修改为自己的  
密码：tgBot$3.. # 自己修改为自己的
```
mysql -u root -p
CREATE DATABASE tgBot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON tgBot.* TO tgBot@localhost IDENTIFIED BY 'tgBot$3..';
FLUSH PRIVILEGES;
quit
```

## 5.安装依赖工具
```
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/bin/composer
```

## 6.安装应用
```
cd /var/www
git clone https://github.com/hhttco/TianXingBot.git
cd TianXingBot
composer install
cp .env.example .env
php artisan key:generate
```

## 7.修改BOT配置文件
1) 修改基础配置  
2) 修改数据库连接  
3) 修改BOT配置
4) 修改权限
5) 初始化数据库  
```
vim .env
cat /var/www/TianXingBot/config/telegram.php
rm /var/www/TianXingBot/config/telegram.php
vim /var/www/TianXingBot/config/telegram.php

chown -R www-data:www-data /var/www/TianXingBot
chmod -R 755 /var/www/TianXingBot
php artisan migrate
```

## 8.修改nginx配置文件
```
vim /etc/nginx/conf.d/txbot.conf
server {
    server_name 域名;
    root        /var/www/TianXingBot/public;
    index       index.php;
    client_max_body_size 0;

    location /downloads {
    }

    location / {
        try_files $uri $uri/ /index.php$is_args$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.3-fpm.sock;
    }
}
```

## 9.配置消息队列
```
vim /etc/supervisor/conf.d/quess.conf
```
```
[program:quess]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/TianXingBot/artisan queue:work --queue=telegram_delete_message
numprocs=1
user=www-data
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/www/TianXingBot/storage/logs/queue.log
```

```
supervisorctl update
supervisorctl status
```


## 10.安装证书

## 11.启动BOT
```
curl -X POST https://域名/telegram/set/webhook
```
