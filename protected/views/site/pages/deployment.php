<?php
/* @var $this SiteController */

$this->pageTitle = 'Инструкция по развёртыванию';
$this->breadcrumbs = array(
    'Развёртывание',
);
?>

<p> Для работы фреймворка Yii требуется минимум PHP 5.1. </p>
<u>Установка</u>
1. 
<?php
        echo CHtml::link('Скачать Yii','',array('target='=>'_blank')); 
?>
 и распаковать в папку на вебсервер.</br> 
2. 
<?php
        echo CHtml::link('Cкачать исходные коды', '',array('target='=>'_blank'));
 ?>
  программы и поместить их рядом с распакованным фреймворком.</br>

3. Создать новую таблицу в БД под названием sender со следующим содержанием:</br>
<?php
        echo CHtml::textArea('sqldump',"
-- Дамп структуры базы данных sender
CREATE DATABASE IF NOT EXISTS `sender` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `sender`;


-- Дамп структуры для таблица sender.message
CREATE TABLE IF NOT EXISTS `message` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `subject` tinytext,
  `header` text,
  `bodyheader` tinytext,
  `body` text,
  `bodyfooter` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=utf8;

-- Дамп структуры для таблица sender.process
CREATE TABLE IF NOT EXISTS `process` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cookies` varchar(128) NOT NULL,
  `id_message` int(10) DEFAULT NULL,
  `percent` float DEFAULT '0',
  `currentel` int(10) DEFAULT '0',
  `maxel` int(10) DEFAULT '0',
  `timeexec` int(10) DEFAULT '0',
  `remtime` int(10) DEFAULT '0',
  `sectoken` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=60 DEFAULT CHARSET=utf8;
",array('rows'=>15, 'cols'=>75));
?>

4. Отредактировать файл конфигурации скрипта расположенный в protected/config/main.php указав host,dbname,username,password данные:</br>
<?php
        echo CHtml::textArea('sqldump',"
'db'=>array(
        'connectionString' => 'mysql:host=localhost;dbname=sender',
        'emulatePrepare' => true,
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
),
",array('rows'=>15, 'cols'=>75));
?>
    

<h1>Настройка ngnix, php-fpm</h1>

<ol type="1">
    <li>Настройка конфигурации nginx.</li>
    <li>Настройка php</li>
</ol>

    <p> Создать файл в директории sites-available со следующим содержанием:</p>

<?php
        echo CHtml::textArea('nginxconf','
server {
	listen		80;
	server_name	sender.my-ch.me;

	charset		utf8;
	access_log	/var/www/my-ch.me/log/sender/access.log main;
	error_log	/var/www/my-ch.me/log/sender/error.log;
	root		/var/www/my-ch.me/httpdoc/sender;

	# Для тестов selenium ОБЯЗАТЕЛЬНО нужно отключить gzip, потому что они не будут работать
	# fastcgi_intercept_errors для отладки Debug 
	gzip off;
	client_max_body_size		1m;
	fastcgi_intercept_errors	off;

	# Это значение стоит увеличить при активном использовании xdebug, иначе скрипт отвалится. Также нужно соответственно изменить в php5-fpm параметр request_terminate_timeout.
	fastcgi_read_timeout		600;
	set				$yii_bootstrap "index.php";
	
	location / {
		index		index.html $yii_bootstrap;
		set		$original_uri $uri?$args;  
		try_files $uri $uri/ /$yii_bootstrap$original_uri;
	}

	location ~ ^/(protected|framework|themes/\w+/views) {
		deny  all;
	}

	# отключаем обработку запросов фреймворком к несуществующим статичным файлам
	location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
		try_files $uri =404;
	}
	
	location ~* \.(js|css|png|jpg|jpeg|gif|ico)$ {
        	expires 24h;
		log_not_found off;
	}

	# передаем PHP-скрипт серверу FastCGI
	location ~ \.php {
		try_files $fastcgi_script_name =404;
		fastcgi_split_path_info  ^(.+\.php)(.*)$;

		# позволяем yii перехватывать запросы к несуществующим PHP-файлам
		set $fsn /$yii_bootstrap;
		if (-f $document_root$fastcgi_script_name){
			set $fsn $fastcgi_script_name;
		}

		fastcgi_pass    unix:/var/run/php5-fpm.sock;
		include		fastcgi_params;
		fastcgi_param	SCRIPT_FILENAME  $document_root$fsn;
		fastcgi_param	REQUEST_URI    $original_uri;
		fastcgi_index	index.php;

		# PATH_INFO и PATH_TRANSLATED могут быть опущены, но стандарт RFC 3875 определяет для CGI
		fastcgi_param	PATH_INFO	$fastcgi_path_info;
		fastcgi_param	PATH_TRANSLATED	$document_root$fsn;
	}

	# не позволять nginx отдавать файлы, начинающиеся с точки (.htaccess, .svn, .git и прочие)
	location ~ /\. {
		deny all;
		access_log off;
		log_not_found off;
	}
}
        ',array('rows'=>15, 'cols'=>75));
?>
<p>Создать символическую ссылку на файл sites-available</p>
<p> Проверить конфигурацию php.ini (/etc/php5/fpm/php.ini) на наличие строки: default_charset = "utf-8" </p>

<h1>Настройка Apach</h1>
<p> Yii готов к работе с настроенным по умолчанию Apache.</p>
?>