Руководство по установке:
Для работы приложения необходимы php 5.4 и драйвер php-mysql.
Также необходимо установить mysql.
Сперва склонируйте к себе проект.

Installation guide:
Install mysql and php-mysql extension;
Run "composer install" in root directory;
Create database with InnoDb storage engine that matches parameters from app/config/parameters.yml;
Create db user that matches parameters from app/config/parameters.ym;
Run "./app/console doctrine:schema:create" in root directory for creating db schema.

For updating/importing rates run "./app/console rates:update". It will update data once in 24 hours after last data update or if new currency is added. (Optional) Put on cron once in a minute this command.
For adding rates open "src/UiBundle/Resources/Config/services.xml" and add rate to service "uiBundle.available_rates".
After new rate is added - run command for updating rated "./app/console rates:update";