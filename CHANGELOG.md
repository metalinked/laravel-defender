# Changelog

## [1.9.2](https://github.com/metalinked/laravel-defender/compare/v1.9.1...v1.9.2) (2025-06-11)


### Miscellaneous Chores

* add license field to composer.json ([5fc4b82](https://github.com/metalinked/laravel-defender/commit/5fc4b82fe4c099658f31c19482c241370e31ada1))
* **docs:** remove unused Usage section from README ([7951f21](https://github.com/metalinked/laravel-defender/commit/7951f2172e010da93dc3c463edebc8c2e996b042))

## [1.9.1](https://github.com/metalinked/laravel-defender/compare/v1.9.0...v1.9.1) (2025-06-11)


### Bug Fixes

* resolve honeypot template issue, translation loading, and update README with middleware instructions ([c1bb48f](https://github.com/metalinked/laravel-defender/commit/c1bb48f3dc4169ada8a0ca049000a116d753e6c5))

## [1.9.0](https://github.com/metalinked/laravel-defender/compare/v1.8.0...v1.9.0) (2025-06-10)


### Features

* **i18n:** add multi-language support and translations for Catalan and Spanish ([94ecef8](https://github.com/metalinked/laravel-defender/commit/94ecef8bdd38401618bacdc51db429cbc9790f58))

## [1.8.0](https://github.com/metalinked/laravel-defender/compare/v1.7.0...v1.8.0) (2025-06-09)


### Features

* **export-logs:** add Artisan command to export logs with advanced filters and tests ([9f17107](https://github.com/metalinked/laravel-defender/commit/9f17107441c8742007eceaf862ca11606e5bbd81))

## [1.7.0](https://github.com/metalinked/laravel-defender/compare/v1.6.0...v1.7.0) (2025-06-09)


### Features

* **advanced-detection:** add advanced risk pattern detection and country/IP access control ([0e2bd7c](https://github.com/metalinked/laravel-defender/commit/0e2bd7cdcc37ed00386fad85b3ca775a92d642a7))

## [1.6.0](https://github.com/metalinked/laravel-defender/compare/v1.5.0...v1.6.0) (2025-06-09)


### Features

* add local alert system and security audit Artisan command ([9cbb68b](https://github.com/metalinked/laravel-defender/commit/9cbb68b7bf28c0abbf31fe75088506d8c3837ab0))

## [1.5.0](https://github.com/metalinked/laravel-defender/compare/v1.4.0...v1.5.0) (2025-06-04)


### Features

* **honeypot:** add configurable honeypot protection with Blade directive and auto middleware ([4238bf0](https://github.com/metalinked/laravel-defender/commit/4238bf0b9e1aedcc03c7bcddf2fb3a6e0d431c5c))
* initial package structure and configuration ([4359bf1](https://github.com/metalinked/laravel-defender/commit/4359bf1b7d6561da5107f639c77e81a0d8f35934))
* **ip-logger:** add suspicious IP detection, blocking, and alert channels ([f91425e](https://github.com/metalinked/laravel-defender/commit/f91425eea6d98d0b40cfdf3e793cf6d099280d5a))
* **ip-logging:** add IP logging middleware and alerting foundation ([1900b38](https://github.com/metalinked/laravel-defender/commit/1900b38e6173a391149361af9cbbdc569757ea10))
* **ip-logging:** improve suspicious login detection, fix route logging, and add Artisan log query command ([0af4d90](https://github.com/metalinked/laravel-defender/commit/0af4d90b99ce646e71c3c5e1a3b880b68b1ee18a))
* **saas:** add basic SaaS connection and Artisan check command ([3cd7339](https://github.com/metalinked/laravel-defender/commit/3cd7339946b597db734d31aeee7d902789d07864))


### Bug Fixes

* **middleware:** correct namespace for HoneypotAutoMiddleware ([8ffeca2](https://github.com/metalinked/laravel-defender/commit/8ffeca2b2b183ba3af5a94d94d4436ca80296051))


### Miscellaneous Chores

* **main:** release 1.0.0 ([7acccb9](https://github.com/metalinked/laravel-defender/commit/7acccb9f2655013dce3a1130f5ec494ddfb92979))
* **main:** release 1.1.0 ([f19fc78](https://github.com/metalinked/laravel-defender/commit/f19fc78ac4eba30d07be19a56d1eb0317770db93))
* **main:** release 1.2.0 ([27792fc](https://github.com/metalinked/laravel-defender/commit/27792fcc4408c1b676aefdcb26f8c81249265b00))
* **main:** release 1.3.0 ([3e2c72d](https://github.com/metalinked/laravel-defender/commit/3e2c72d98a3734a3c7aec8a08700c1ac038a695d))
* **main:** release 1.4.0 ([0d2c718](https://github.com/metalinked/laravel-defender/commit/0d2c71831504b3bb6e62cda18917723f79652842))

## [1.4.0](https://github.com/metalinked/laravel-defender/compare/v1.3.0...v1.4.0) (2025-06-04)


### Features

* **saas:** add basic SaaS connection and Artisan check command ([3cd7339](https://github.com/metalinked/laravel-defender/commit/3cd7339946b597db734d31aeee7d902789d07864))


### Bug Fixes

* **middleware:** correct namespace for HoneypotAutoMiddleware ([8ffeca2](https://github.com/metalinked/laravel-defender/commit/8ffeca2b2b183ba3af5a94d94d4436ca80296051))

## [1.3.0](https://github.com/metalinked/laravel-defender/compare/v1.2.0...v1.3.0) (2025-06-04)


### Features

* **honeypot:** add configurable honeypot protection with Blade directive and auto middleware ([4238bf0](https://github.com/metalinked/laravel-defender/commit/4238bf0b9e1aedcc03c7bcddf2fb3a6e0d431c5c))
* initial package structure and configuration ([4359bf1](https://github.com/metalinked/laravel-defender/commit/4359bf1b7d6561da5107f639c77e81a0d8f35934))
* **ip-logger:** add suspicious IP detection, blocking, and alert channels ([f91425e](https://github.com/metalinked/laravel-defender/commit/f91425eea6d98d0b40cfdf3e793cf6d099280d5a))
* **ip-logging:** add IP logging middleware and alerting foundation ([1900b38](https://github.com/metalinked/laravel-defender/commit/1900b38e6173a391149361af9cbbdc569757ea10))
* **ip-logging:** improve suspicious login detection, fix route logging, and add Artisan log query command ([0af4d90](https://github.com/metalinked/laravel-defender/commit/0af4d90b99ce646e71c3c5e1a3b880b68b1ee18a))


### Miscellaneous Chores

* **main:** release 1.0.0 ([7acccb9](https://github.com/metalinked/laravel-defender/commit/7acccb9f2655013dce3a1130f5ec494ddfb92979))
* **main:** release 1.1.0 ([f19fc78](https://github.com/metalinked/laravel-defender/commit/f19fc78ac4eba30d07be19a56d1eb0317770db93))
* **main:** release 1.2.0 ([27792fc](https://github.com/metalinked/laravel-defender/commit/27792fcc4408c1b676aefdcb26f8c81249265b00))

## [1.2.0](https://github.com/metalinked/laravel-defender/compare/v1.1.0...v1.2.0) (2025-06-04)


### Features

* **ip-logger:** add suspicious IP detection, blocking, and alert channels ([f91425e](https://github.com/metalinked/laravel-defender/commit/f91425eea6d98d0b40cfdf3e793cf6d099280d5a))
* **ip-logging:** add IP logging middleware and alerting foundation ([1900b38](https://github.com/metalinked/laravel-defender/commit/1900b38e6173a391149361af9cbbdc569757ea10))
* **ip-logging:** improve suspicious login detection, fix route logging, and add Artisan log query command ([0af4d90](https://github.com/metalinked/laravel-defender/commit/0af4d90b99ce646e71c3c5e1a3b880b68b1ee18a))

## [1.1.0](https://github.com/metalinked/laravel-defender/compare/v1.0.0...v1.1.0) (2025-06-01)


### Features

* **honeypot:** add configurable honeypot protection with Blade directive and auto middleware ([4238bf0](https://github.com/metalinked/laravel-defender/commit/4238bf0b9e1aedcc03c7bcddf2fb3a6e0d431c5c))

## 1.0.0 (2025-05-31)


### Features

* initial package structure and configuration ([4359bf1](https://github.com/metalinked/laravel-defender/commit/4359bf1b7d6561da5107f639c77e81a0d8f35934))
