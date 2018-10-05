# Rinvex Statistics

**Rinvex Statistics** is a lightweight, yet detailed package for tracking and recording user visits across your Laravel application. With only one simple query per request, important data is being stored, and later a cronjob crush numbers to extract meaningful stories from within the haystack.

Unlike other tracking packages that seriously damage your project's performance (yes, I mean that package you know 😅), our package takes a different approach by just executing only one query at the end of each request after the response is being served to the user, through the `terminate` method of an automatically attached middleware, and then later on it uses the raw data previously inserted in the database to extract meaningfull numbers. This is done based on a random lottery request, or through a scheduled job (recommended) that could be queued to offload the heavy crunching work.

**Rinvex Statistics** tracks each -valid- request, meaning only requests that goes through routing pipeline, which also means that any wrong URL that results in `NotFoundHttpException` will not be tracked. If requested page has uncaught exceptions, it won't be tracked as well. It track user's logged in account (if any), session of all users and guests (if any), device (family, model, brand), platform (family, version), browser (agent, kind, family, version), path, route (action, middleware, parameters), host, protocol, ip address, language, status codes, and many more, and still we've plenty of awesome features planned for the future.

With such a huge collected data, the `statistics_requests` database table will noticeably increase in size specially if you've a lot of visits, that's why it's recommended to clean it periodically. Other important data will stay still in their respective tables, normalized and without any performance issues, so only this table need to be cleaned. By default that will be done automatically every month.

The default implementation of **Rinvex Statistics** comes with zero configuration out-of-the-box, which means it just works once installed. But it's recommended to change the defaults and disable the "Statistics Crunching Lottery" from config file, and replace it with a [Scheduled Tasks](https://laravel.com/docs/master/scheduling) for even better performance if you've large number of visits. See [Usage](#usage) for details.

[![Packagist](https://img.shields.io/packagist/v/rinvex/laravel-statistics.svg?label=Packagist&style=flat-square)](https://packagist.org/packages/rinvex/laravel-statistics)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/rinvex/laravel-statistics.svg?label=Scrutinizer&style=flat-square)](https://scrutinizer-ci.com/g/rinvex/laravel-statistics/)
[![Code Climate](https://img.shields.io/codeclimate/github/rinvex/laravel-statistics.svg?label=CodeClimate&style=flat-square)](https://codeclimate.com/github/rinvex/laravel-statistics)
[![Travis](https://img.shields.io/travis/rinvex/laravel-statistics.svg?label=TravisCI&style=flat-square)](https://travis-ci.org/rinvex/laravel-statistics)
[![StyleCI](https://styleci.io/repos/118045101/shield)](https://styleci.io/repos/118045101)
[![License](https://img.shields.io/packagist/l/rinvex/laravel-statistics.svg?label=License&style=flat-square)](https://github.com/rinvex/laravel-statistics/blob/develop/LICENSE)


## Installation

1. Install the package via composer:
    ```shell
    composer require rinvex/laravel-statistics
    ```

2. Execute migrations via the following command:
    ```
    php artisan rinvex:migrate:statistics
    ```

3. Done!


## Usage

Well, this is the fun part! **Rinvex Statistics** has no usage instructions, because it just works! You install it and you are done! Seriously!!

Anyway, as a recommended performance tweak go ahead and do the following (optionally):

1. Publish config file via the following command:
    ```
    php artisan rinvex:publish:statistics
    ```

2. Disable the "Statistics Crunching Lottery" from config file.

3. Follow the default Laravel documentation about [Scheduled Tasks](https://laravel.com/docs/master/scheduling), then schedule both `\Rinvex\Statistics\Jobs\CrunchStatistics` and `\Rinvex\Statistics\Jobs\CleanStatisticsRequests` jobs at whatever intervals you see appropriate.

4. Enjoy!

> **Note:** **Rinvex Statistics** has a `\Rinvex\Statistics\Http\Middleware\TrackStatistics` middleware that attach itself automatically to the `web` middleware group, that's how it works out-of-the-box with zero configuration.

### Data retrieval

You may need to build your own frontend interface to browse statistics, and for that you can utilize any of the included eloquent models as you normally do with [Laravel Eloquent](https://laravel.com/docs/master/eloquent).

All eloquent models are self explainatory:

- `\Rinvex\Statistics\Models\Agent` browser agent model
- `\Rinvex\Statistics\Models\Datum` raw statistics data (to be crunched)
- `\Rinvex\Statistics\Models\Device` user device model
- `\Rinvex\Statistics\Models\Path` request path model
- `\Rinvex\Statistics\Models\Platform` user platform model
- `\Rinvex\Statistics\Models\Request` request data model (to be cleaned periodically)
- `\Rinvex\Statistics\Models\Route` request route details model

All models are bound to the [Service Container](https://laravel.com/docs/master/container) so you can swap easily from anywhere in your application. In addition to the default normal way of using these models explicitely, you can use their respective service names as in the following example:

```php
// Find first browser agent (any of these methods are valid and equivalent)
app('rinvex.statistics.agent')->first();
new \Rinvex\Statistics\Models\Agent::first();
app(\Rinvex\Statistics\Contracts\AgentContract::class)->first();
```

Same for all other eloquent models.

### Counts that matters

All agent, device, path, platform, route models have a `count` attribute, which gets updated automatically whenever a new request has been tracked.

This `count` attribute reflects number of hits. To make it clear let's explain through data samples:

#### Agents

| id | kind | family | version | count | name |
| --- | --- | --- | --- | --- | --- |
| 1 | desktop | Chrome | 63.0.3239 | 734 | Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36 | 

This means there's 734 visit to our project through **Chrome** browser, version **63.0.3239**, with agent (**Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36**)

#### Devices

| id | family | model | brand | count |
| --- | --- | --- | --- | --- |
| 1 | iPhone | iPhone | Apple | 83 | 

This means there's 83 visits to our project through **iPhone** device.

#### Platforms

| id | family | version | count |
| --- | --- | --- | --- |
| 1 | Mac OS X | 10.12.6 | 615 |

This means there's 615 visits to our project through **Mac OS X** operating system, with version **10.12.6**.

#### Paths

| id | host | locale | path | parameters | count |
| --- | --- | --- | --- | --- | --- |
| 1 | test.homestead.local | en | en/adminarea/roles/admin | {"role": "admin", "locale": "en"} | 12 |

This means there's 12 visits to the admin dashboard roles management of the **test.homestead.local** host (in case you have multiple hosts or wildcard subdomains enabled on the same project, you can track all of them correctly here). The english interface was used, and the accessed route had two parameters, one for locale (english in this case), and updated role record (admin in this case).

This table could be used as a visit counter for all your pages. To retrieve and display page views you can use the following code for example:

```php
$pageViews = app('rinvex.statistics.path')->where('path', request()->decodedPath())->first()->count;
```

And simply use the `$pageViews` variable anywhere in your views or controllers, or anywhere else. That way you have automatic visit counter for all your project's pages, very useful and performant, ready at your fingertips. You can add `host` contraint in case you have wildcard subdomains enabled.

#### Routes

| id | name | path | action | middleware | parameters | count |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | adminarea.roles.edit | {locale}/adminarea/roles/{role} | App\Http\Controllers\Adminarea\RolesController@form | ["web","nohttpcache","can:access-adminarea","auth","can:update-roles,roles"] | {"role": "[a-z0-9-]+", "locale": "[a-z]{2}"} | 41 |

This means there's 41 visits to the `adminarea.roles.edit` route, which has the `{locale}/adminarea/roles/{role}` raw path, and served through the `App\Http\Controllers\Adminarea\RolesController@form` controller action, and has the following middleware applied `["web","nohttpcache","can:access-adminarea","auth","can:update-roles,roles"]`, knowing the route accepts two parameters with the following regex requirements `{"role": "[a-z0-9-]+", "locale": "[a-z]{2}"}`.

As you can see, this `statistics_routes` table beside the `statistics_paths` table are both complimentary, and could be used together to track which paths and routs are being accessed, how many times, and what controller actions serve it, and what parameters are required, with the actual parameter replacements used to access it. Think of routes as your raw links blueprint map, and of paths as the executed and actually used links by users.

#### Geoips

| id | client_ip | latitude | longitude | country_code | client_ips | is_from_trusted_proxy | division_code | postal_code | timezone | city | count |  
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 127.0.0.0 | 41.31 | -72.92 | US | NULL | 0 | CT | 06510 | America/New_York | New Haven | 57 |

This means there's 57 visits to the project from IP address `127.0.0.0` with the latitude, longitude and timezone mentioned above coming from `New Haven` city, `Connecticut` state.

#### Requests

| id | route_id | agent_id | device_id | platform_id | path_id | geoip_id | user_id | user_type | session_id | method | status_code | protocol_version | referer | language | is_no_cache | wants_json | is_secure | is_json | is_ajax | is_pjax | created_at |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 123 | 123 | 123 | 123 | 123 | 123 | 123 | user | MU22QcrzDIdj0gY27yJmUPJHNFy9Hlqvkel1KBZ1 | GET|POST | 200 | HTTP/1.1 | https://google.com | en_US | 0 | 0 | 1 | 0 | 0 | 0 | 2018-01-10 09:42:39 |

This is the most comprehensive table that records every single request made to the project, with access details as seen in the sample above. Through `session_id`, `user_id` and `user_type` you can track guests (logged out) and users (logged in) and extract unique visits/visitors with the criteria you see appropriate for you.

> **Notes:**
> - As a final note, this package is a data hord, and it doesn't actually do much of the math that could be done on such a valuable gathered data, so it's up to your imagination to utilize it however you see fits your goals. Implementation details is up to you.
> - We didn't explain the `statistics_data` table since it's used for temporary raw data storage until it's being crunched and processed by the package, so you should **NOT** care or mess with that table. It's used internally by the package and has no real end-user usage.
> - The `\Rinvex\Statistics\Models\Request` model has relationships to all related data such as `agent`, `device`, `path`, `platform`, and `route`. So once you grab a request instance you can access any of it's relationships as you normaly do with [Eloquent Relationships](https://laravel.com/docs/master/eloquent-relationships) like so: `$statisticsRequest->agent->version` or `$statisticsRequest->platform->family`.


## Changelog

Refer to the [Changelog](CHANGELOG.md) for a full history of the project.


## Support

The following support channels are available at your fingertips:

- [Chat on Slack](http://chat.rinvex.com)
- [Help on Email](mailto:help@rinvex.com)
- [Follow on Twitter](https://twitter.com/rinvex)


## Contributing & Protocols

Thank you for considering contributing to this project! The contribution guide can be found in [CONTRIBUTING.md](CONTRIBUTING.md).

Bug reports, feature requests, and pull requests are very welcome.

- [Versioning](CONTRIBUTING.md#versioning)
- [Pull Requests](CONTRIBUTING.md#pull-requests)
- [Coding Standards](CONTRIBUTING.md#coding-standards)
- [Feature Requests](CONTRIBUTING.md#feature-requests)
- [Git Flow](CONTRIBUTING.md#git-flow)


## Security Vulnerabilities

If you discover a security vulnerability within this project, please send an e-mail to [help@rinvex.com](help@rinvex.com). All security vulnerabilities will be promptly impressioned.


## About Rinvex

Rinvex is a software solutions startup, specialized in integrated enterprise solutions for SMEs established in Alexandria, Egypt since June 2016. We believe that our drive The Value, The Reach, and The Impact is what differentiates us and unleash the endless possibilities of our philosophy through the power of software. We like to call it Innovation At The Speed Of Life. That’s how we do our share of advancing humanity.


## License

This software is released under [The MIT License (MIT)](LICENSE).

(c) 2016-2018 Rinvex LLC, Some rights reserved.
