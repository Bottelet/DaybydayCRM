![Flarepoint Logo](https://cloud.githubusercontent.com/assets/15610490/16813901/ebfd6d94-4933-11e6-9fee-655f6193f38e.png)

### Flarepoint CRM [![Build Status](https://travis-ci.org/Bottelet/Flarepoint-crm.svg?branch=develop)](https://travis-ci.org/Bottelet/Flarepoint-crm)

Flarepoint is a customer relationship management system (CRM) whose purpose is to help you keep track of your customers, contacts, leads, tasks etc. Flarepoint is a free, open-source and self-hosted platform based on Laravel 5.4 PHP Framework and PHP 7.0+.

![page_design](https://cloud.githubusercontent.com/assets/15610490/16659700/903393ac-446b-11e6-969c-831fcd698a06.PNG)

## hosted solution
![DaybydayLogo](https://user-images.githubusercontent.com/15610490/69175894-ed771300-0b04-11ea-9ecd-a5ad6e3d8877.png)

If you are looking for a hosted CRM solution, i will recommend taking a look at [daybydaycrm.com](https://daybydaycrm.com) It's an upgraded version of Flarepoint, with a new nice design, and many new features.


## Get started

I would like to refer to the wiki, for help on getting started

* [Installation](https://github.com/Bottelet/Flarepoint-crm/wiki/Install)
* [Upgrading](https://github.com/Bottelet/Flarepoint-crm/wiki/Upgrading)
* [Installation with Docker](https://github.com/Bottelet/Flarepoint-crm/wiki/Install-using-Docker)
* [Insertion of dummy data](https://github.com/Bottelet/Flarepoint-crm/wiki/Insertion-of-dummy-data)


## Features overview

- Tasks management
- Leads management
- Simple invoice management
- Easy & simple time management for each task
- Role management (Create and update your own roles)
- Easy configurable settings
- Client overview (Keep easy track of open tasks for each client etc)
- Contacts management (associated with a Client)
- Upload documents to each clients (easy track of contracts and more)
- Fast overview over your own open tasks, leads etc
- Global dashboard


### To-do

Flarepoint is still under development, so there are a lot on my to-do list.

- Multiple integrations (Slack, e-conomic, Google Drive, dropbox etc.)
- Different Color schemes
- API
- Excel Import/export
- Better cache
- Even easier installation

And much more (in no particular order)


### Contribution Guide

Flarepoint CRM follows [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) + Symfony coding standard.

All test should pass on Travis, or the failed test should be rewritten to fit new changes 

Translation... You can help translating Flarepoint-crm into other languages, by copying the resources/lang/en folder into for example resources/lang/de and translate the files, found inside the folder.


### Packages

The packages used are the following...

- [LaravelCollective](https://github.com/LaravelCollective/html)
- [laravel-datatables](https://github.com/yajra/laravel-datatables)
- [Entrust](https://github.com/Zizaco/entrust)
- [Debugbar](https://github.com/barryvdh/laravel-debugbar)
- [Doctrine Database Adstraction Layer](https://github.com/doctrine/dbal)


### License

Flarepoint is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
