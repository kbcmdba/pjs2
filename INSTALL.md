Please note that this file is incomplete and will be developed further as the
software matures. Please note - I do not expect to repeat common documentation on
how to install things like Apache, MySQL or PHP. You can find documentaiton on
those items on the Internet quite readily.

In order to use PHPJobSeeker2, I use the following;

* Apache httpd
* MySQL 5.6 or greater
* PHP 7.2 or greater

In your PHP configuration (/etc/php.ini on Unix), you'll need to set the timezone
in order for this to work correctly. If you're in the Central US timezone, setting

date.timezone = America/Chicago

may be appropriate for you. Check your documentation for more information.

The modules I had to load to make this work are included below:

...

After verifying the modules are properly installed, perform the following tasks:

1. Copy the file config_sample.xml to config.xml
    1. Update the new config.xml according to your needs


