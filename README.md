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

## 1.0.6.4-alpha, September 7, 2016

- Added migration (alter DocumentId from 64 to 128 characters)

## 1.0.6.3-alpha, May 13, 2016

- Updates for matacms\environment\helpers\Html

## 1.0.6.2-alpha, November 30, 2015

- Bug fix

## 1.0.6.1-alpha, October 8, 2015

- Changed ActiveQuery::EVENT_BEFORE_PREPARE_STATEMENT to run only for models with EnvironmentBehavior  

## 1.0.6-alpha, August 21, 2015

- Added "Submit for review" button if user doesn't have any roles (rbac) assigned
- Added sending notification email to "reviewer"

## 1.0.5.1-alpha, June 20, 2015

- Fixed dependency on matacms-base ~1.1-alpha

## 1.0.5-alpha, June 20, 2015

- Added dependency on matacms-base ~1.0-alpha

## 1.0.4-alpha, June 8, 2015

- Updates for console application

## 1.0.3-alpha, June 3, 2015

- Bug fixes


## 1.0.2-alpha, May 28, 2015

- Added check for console application in Bootstrap
- Added handling of logged-in users to get latest versions of records

## 1.0.1-alpha, May 26, 2015

- Added dependency on matacms-base ~1.0.7-alpha where [[ActiveQuery::EVENT_BEFORE_PREPARE_STATEMENT]] was added
- Completely rewrote getting live versions, from iterating through fetched models to injecting conditions in SQL command
- Code cleanup, especially in [[Bootstrap]]
- Added dummy unit test

## 1.0.0-alpha, May 18, 2015

- Initial release.
