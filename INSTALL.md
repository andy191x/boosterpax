
## Hosting the boosterpax.com application

Requirements:

* A LEMP application server.
** For quick installation, most cloud vendors have one-click LEMP servers.
* PHP 7.1 or newer with "mbstring" extension installed.

Instructions:

* Obtain a copy of Isotope ( https://github.com/metafizzy/isotope/blob/v3.0.6/dist/isotope.pkgd.min.js ) and install it to ( "www/vendor/isotope/isotope.pkgd.min.js" ). Boosterpax's license is incompatbile with Isotope's, so you'd need to obtain a commercial license for Isotope if you're going to promote your Boosterpax instance. More info in "LICENSE.md".
* Upload the source code to your application server.
* Copy the "conf/conf.example.php" to "conf/conf.php".
* Open "conf/conf.php", uncomment all lines and replace them with any values that you'd prefer.
* Copy the "conf/nginx-vhost-example.conf" example file.
* In "nginx-vhost-example.conf", replace "/YOUR/PATH/TO/boosterpax.com" to where you uploaded the source code.
* In "nginx-vhost-example.conf", check "fastcgi_pass" with your server's php-fpm configuration. You may need to change this to a unix socket (e.g: "unix:/run/php/php7.4-fpm.sock").
* Include the vhost file in your main nginx.conf.
* Restart Nginx.
