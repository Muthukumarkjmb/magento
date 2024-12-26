rm -f var/cache/* -R
rm -f var/page_cache/* -R
rm -f var/view_preprocessed/*  -R
rm -f generated/* -R
rm -f pub/static/* -R
php -d memory_limit=-1 bin/magento setup:upgrade
php -d memory_limit=-1 bin/magento setup:di:compile
php -d memory_limit=-1 bin/magento setup:static-content:deploy -f
chmod 0777 var/* -R
chmod 0777 pub/static/* -R
chmod 0777 generated/* -R