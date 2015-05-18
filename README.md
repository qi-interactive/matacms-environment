MATA CMS Environment
==========================================

![MATA CMS Module](https://s3-eu-west-1.amazonaws.com/qi-interactive/assets/mata-cms/gear-mata-logo%402x.png)


Environment module manages environment (DRAFT, LIVE) for entities.


Installation
------------

- Add the module using composer: 

```json
"matacms/matacms-environment": "~1.0.0"
```

-  Run migrations
```
php yii migrate/up --migrationPath=@vendor/matacms/matacms-environment/migrations
```


Changelog
---------

## 1.0.0-alpha, May 18, 2015

- Initial release.
