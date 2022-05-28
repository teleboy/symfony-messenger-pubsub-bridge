# Google Cloud Pub/Sub bridge changelog

## 0.6.0 - 2022-05-28
* Applying PHP CS Fixer

## 0.5.1 - 2022-03-21
* Fix PHP CS errors

## 0.5.0 - 2022-03-21
* Move code to [./src](./src) directory
* Update `teleboy/web.dev` to `^6.0`

## 0.4.1 - 2021-07-08
* Allow `symfony/event-dispatcher-contracts` `^1.1`

## 0.4.0 - 2021-07-08
* Do not instantiate objects inside classes, inject them instead. Mainly to improve testability
* Abstract DSN & Pub/Sub config into classes
* Declare missing Composer dependencies

## 0.3.0 - 2021-06-14
* Fix missing `PubSubReceivedStamp` subscription when using pull delivery
* Correctly read message IDs of published message
* Refactoring

## 0.2.0 - 2021-06-11
* Add `getSerializer()` to `PubSubTransport`

## 0.1.0 - 2021-06-11
* Initial bundle
