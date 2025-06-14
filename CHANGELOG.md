# Changelog

## [1.15.0](https://github.com/metalinked/laravel-defender/compare/v1.14.0...v1.15.0) (2025-06-14)


### Features

* **stats:** add defender:stats Artisan command and improve documentation ([e395499](https://github.com/metalinked/laravel-defender/commit/e39549910158584f10dff11cdd04aded168271e1))

## [1.14.0](https://github.com/metalinked/laravel-defender/compare/v1.13.2...v1.14.0) (2025-06-14)


### Features

* **detection:** add path traversal and fuzzing pattern detection to AdvancedDetectionMiddleware ([484cc4d](https://github.com/metalinked/laravel-defender/commit/484cc4d41638138454bb766303825b9815450ee4))

## [1.13.2](https://github.com/metalinked/laravel-defender/compare/v1.13.1...v1.13.2) (2025-06-13)


### Miscellaneous Chores

* **composer:** update keywords and improve package ([2aa067f](https://github.com/metalinked/laravel-defender/commit/2aa067f60499efc901e7daa56381cbf38897622b))

## [1.13.1](https://github.com/metalinked/laravel-defender/compare/v1.13.0...v1.13.1) (2025-06-13)


### Bug Fixes

* **middleware:** always save specific reason for alerts in database ([067b26f](https://github.com/metalinked/laravel-defender/commit/067b26fa5bf2916d64a715a7b8a76ebbd4fffb4d))

## [1.13.0](https://github.com/metalinked/laravel-defender/compare/v1.12.1...v1.13.0) (2025-06-13)


### Features

* **commands:** add prune-logs Artisan command for cleaning old logs ([467e9ea](https://github.com/metalinked/laravel-defender/commit/467e9ea156696af31873e1de821f73cd9788da51))

## [1.12.1](https://github.com/metalinked/laravel-defender/compare/v1.12.0...v1.12.1) (2025-06-13)


### Bug Fixes

* **middleware:** ensure all suspicious access logs are saved to database ([5ccfc51](https://github.com/metalinked/laravel-defender/commit/5ccfc51d830105c9aa14631eb91a9433881a6685))

## [1.12.0](https://github.com/metalinked/laravel-defender/compare/v1.11.0...v1.12.0) (2025-06-13)


### Features

* add local alert system and security audit Artisan command ([9cbb68b](https://github.com/metalinked/laravel-defender/commit/9cbb68b7bf28c0abbf31fe75088506d8c3837ab0))
* **advanced-detection:** add advanced risk pattern detection and country/IP access control ([0e2bd7c](https://github.com/metalinked/laravel-defender/commit/0e2bd7cdcc37ed00386fad85b3ca775a92d642a7))
* **config:** centralize all alerts via AlertManager and make database logging optional ([b365777](https://github.com/metalinked/laravel-defender/commit/b365777fd1867a84a1be2a1d36f262e015689ac9))
* **config:** centralize all alerts via AlertManager and make database logging optional ([2602799](https://github.com/metalinked/laravel-defender/commit/2602799e4cc98a1e5faccdd9580a118365b84d5e))
* **export-logs:** add Artisan command to export logs with advanced filters and tests ([9f17107](https://github.com/metalinked/laravel-defender/commit/9f17107441c8742007eceaf862ca11606e5bbd81))
* **honeypot:** add configurable honeypot protection with Blade directive and auto middleware ([4238bf0](https://github.com/metalinked/laravel-defender/commit/4238bf0b9e1aedcc03c7bcddf2fb3a6e0d431c5c))
* **i18n:** add multi-language support and translations for Catalan and Spanish ([94ecef8](https://github.com/metalinked/laravel-defender/commit/94ecef8bdd38401618bacdc51db429cbc9790f58))
* initial package structure and configuration ([4359bf1](https://github.com/metalinked/laravel-defender/commit/4359bf1b7d6561da5107f639c77e81a0d8f35934))
* **ip-logger:** add suspicious IP detection, blocking, and alert channels ([f91425e](https://github.com/metalinked/laravel-defender/commit/f91425eea6d98d0b40cfdf3e793cf6d099280d5a))
* **ip-logging:** add IP logging middleware and alerting foundation ([1900b38](https://github.com/metalinked/laravel-defender/commit/1900b38e6173a391149361af9cbbdc569757ea10))
* **ip-logging:** improve suspicious login detection, fix route logging, and add Artisan log query command ([0af4d90](https://github.com/metalinked/laravel-defender/commit/0af4d90b99ce646e71c3c5e1a3b880b68b1ee18a))
* **ip-logs:** add user_agent, referer, country_code and headers_hash fields to logs ([03c9087](https://github.com/metalinked/laravel-defender/commit/03c9087bac74129d26683c0f81af9faba86ca9b0))
* **saas:** add basic SaaS connection and Artisan check command ([3cd7339](https://github.com/metalinked/laravel-defender/commit/3cd7339946b597db734d31aeee7d902789d07864))
* update documentation and configuration for centralized alert system ([755beec](https://github.com/metalinked/laravel-defender/commit/755beeccd18edf58eed75766465f47500fd1cfb3))


### Bug Fixes

* **middleware:** correct namespace for HoneypotAutoMiddleware ([8ffeca2](https://github.com/metalinked/laravel-defender/commit/8ffeca2b2b183ba3af5a94d94d4436ca80296051))
* **migration:** rename migration file to create_defender_ip_logs_table.php for correct publishing ([e444f23](https://github.com/metalinked/laravel-defender/commit/e444f239369cafd8de5a213bf430fe458aff5338))
* resolve honeypot template issue, translation loading, and update README with middleware instructions ([c1bb48f](https://github.com/metalinked/laravel-defender/commit/c1bb48f3dc4169ada8a0ca049000a116d753e6c5))


### Miscellaneous Chores

* add license field to composer.json ([5fc4b82](https://github.com/metalinked/laravel-defender/commit/5fc4b82fe4c099658f31c19482c241370e31ada1))
* **docs:** remove unused Usage section from README ([7951f21](https://github.com/metalinked/laravel-defender/commit/7951f2172e010da93dc3c463edebc8c2e996b042))
* **main:** release 1.0.0 ([7acccb9](https://github.com/metalinked/laravel-defender/commit/7acccb9f2655013dce3a1130f5ec494ddfb92979))
* **main:** release 1.1.0 ([f19fc78](https://github.com/metalinked/laravel-defender/commit/f19fc78ac4eba30d07be19a56d1eb0317770db93))
* **main:** release 1.10.0 ([#14](https://github.com/metalinked/laravel-defender/issues/14)) ([1f03d0c](https://github.com/metalinked/laravel-defender/commit/1f03d0c2994130ffa0b603879e34d2fe87769b0b))
* **main:** release 1.11.0 ([#15](https://github.com/metalinked/laravel-defender/issues/15)) ([530a804](https://github.com/metalinked/laravel-defender/commit/530a80420801862ec0ee21e5c0741ac5dc549d59))
* **main:** release 1.2.0 ([27792fc](https://github.com/metalinked/laravel-defender/commit/27792fcc4408c1b676aefdcb26f8c81249265b00))
* **main:** release 1.3.0 ([3e2c72d](https://github.com/metalinked/laravel-defender/commit/3e2c72d98a3734a3c7aec8a08700c1ac038a695d))
* **main:** release 1.4.0 ([0d2c718](https://github.com/metalinked/laravel-defender/commit/0d2c71831504b3bb6e62cda18917723f79652842))
* **main:** release 1.5.0 ([0c00871](https://github.com/metalinked/laravel-defender/commit/0c0087150ff0fa9ce5a1fc073a12928edaf5f01d))
* **main:** release 1.6.0 ([5af5d0a](https://github.com/metalinked/laravel-defender/commit/5af5d0ab7942471fa297a6b6db14e27de6c53be0))
* **main:** release 1.7.0 ([ce50fb8](https://github.com/metalinked/laravel-defender/commit/ce50fb8dd39f7614a88b2819a8b17b985c21cf54))
* **main:** release 1.8.0 ([cce4532](https://github.com/metalinked/laravel-defender/commit/cce453287a64729d4fb1a4b1d7b8f6a289eee3d8))
* **main:** release 1.9.0 ([394bb32](https://github.com/metalinked/laravel-defender/commit/394bb32097ef346b07078bb720d2a042b7a9ab0f))
* **main:** release 1.9.1 ([a12e3d4](https://github.com/metalinked/laravel-defender/commit/a12e3d430b24802da1bf8e305b4cc380d72c498d))
* **main:** release 1.9.2 ([#12](https://github.com/metalinked/laravel-defender/issues/12)) ([4430ae3](https://github.com/metalinked/laravel-defender/commit/4430ae3ba9e64a9e39708d5a100805cbe70252c9))
* **main:** release 1.9.3 ([#13](https://github.com/metalinked/laravel-defender/issues/13)) ([81b3bd6](https://github.com/metalinked/laravel-defender/commit/81b3bd6fc9c5c14f39b91a9d4fa9d9df50058d55))

## [1.11.0](https://github.com/metalinked/laravel-defender/compare/v1.10.0...v1.11.0) (2025-06-12)


### Features

* update documentation and configuration for centralized alert system ([755beec](https://github.com/metalinked/laravel-defender/commit/755beeccd18edf58eed75766465f47500fd1cfb3))

## [1.10.0](https://github.com/metalinked/laravel-defender/compare/v1.9.3...v1.10.0) (2025-06-12)


### Features

* **ip-logs:** add user_agent, referer, country_code and headers_hash fields to logs ([03c9087](https://github.com/metalinked/laravel-defender/commit/03c9087bac74129d26683c0f81af9faba86ca9b0))

## [1.9.3](https://github.com/metalinked/laravel-defender/compare/v1.9.2...v1.9.3) (2025-06-12)


### Bug Fixes

* **migration:** rename migration file to create_defender_ip_logs_table.php for correct publishing ([e444f23](https://github.com/metalinked/laravel-defender/commit/e444f239369cafd8de5a213bf430fe458aff5338))

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
