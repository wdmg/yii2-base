Changelog
=========

## 1.3.2 (2021-02-12)
 * Fixed base models and registerTranslations()
 
## 1.3.1 (2021-01-16)
 * Fixed UrlManager init, extended module dashboardNavItems() method
 
## 1.3.0 (2020-10-06)
 * Added `runConsole()` method for BaseModule
 * Added `getPrev()` and `getNext()` methods for base ActiveRecord model
 * Added short module alias, like `@sitemap` instead of long `@wdmg/sitemap`
 
## 1.2.4 (2020-06-07)
 * Added new method `isRestAPI()`
 * UrlManager rules fixed
 
## 1.2.3 (2020-05-29)
 * Rollback
 * Fixed UrlManager (strict parsing off), added bootstrap autoload in composer
 
## 1.2.2 (2020-05-11)
 * Methods compatibility for DynamicModel
 
## 1.2.1 (2020-05-02)
 * Added `formName()`, `setFormName($name)` methods for DynamicModel
 * Added SluggableBehavior(), implement for base ActiveRecord

## 1.2.0 (2020-04-14)
 * Added `getModule()` for base ActiveRecord model
 * Removing DynamicModel to models
 * Fixed ActiveRecordML
 * Added base ActiveRecord and ActiveRecordML models for multi-language implementation
 
## 1.1.8 (2020-03-31)
 * Log activity of modules and user actions
 
## 1.1.7 (2020-03-25)
 * Up to date dependencies

## 1.1.6 (2020-01-04)
 * Added `isBackend()`, `isConsole()` methods 

## 1.1.5 (2019-12-31)
 * Extended DynamicModel with `setAttributeLabel()` and `setAttributeLabels()` methods
 
## 1.1.4 (2019-12-10)
 * Fixed deprecated class declaration
 
## 1.1.3 (2019-12-02)
 * Fix loading options during module installation
 
## 1.1.2 (2019-10-28)
 * Fix detect of loading modules
 
## 1.1.1 (2019-10-15)
 * Fix options syntax
 
## 1.1.0 (2019-09-07)
 * Added getOption() method
 * Change order by call setMetaData() function
 
## 1.0.5 (2019-07-26)
 * Normalize route prefix refactoring

## 1.0.4 (2019-07-20)
 * Added methods of install/uninstall modules
 
## 1.0.3 (2019-07-16)
 * Added extra options to composer.json (for Butterfly.CMS implementation)
 
## 1.0.2 (2019-07-11)
 * Refactoring method dashboardNavItems()
 
## 1.0.1 (2019-06-07)
 * Module refactoring
 
## 1.0.0 (2019-06-04)
 * Added base module interface