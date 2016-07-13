

![Flarepoint Logo](https://cloud.githubusercontent.com/assets/15610490/16813901/ebfd6d94-4933-11e6-9fee-655f6193f38e.png)
### Flarepoint CRM
Flarepoint is a new customer relationship management system (CRM) which purpose is to help you keep track of your customers, tasks etc. Flarepoint is a free, open-source and self-hosted platform based on Laravel PHP Framework.

![page_design](https://cloud.githubusercontent.com/assets/15610490/16659700/903393ac-446b-11e6-969c-831fcd698a06.PNG)


## Installation



**How to**

- Insert project into empty folder / git clone https://github.com/Bottelet/Flarepoint-crm.git
- Create a empty database table
- Insert database credentials in Config\Database.php
- Run the following commands
```
    composer install
    php artisan migrate -seed
    php artisan key:generate
```
- login in with these credentials  Mail: admin@admin.com Password: admin123 (Can be changed in the dashboard)
- DONE


## Features overview
- Tasks management
- Leads management
- Easy & simple time management for each task
- Role management (Create and update your own roles)
- Role & global settings
- Client overview (Keep easy track of open tasks for each client etc)
- Upload documents to each clients (easy track of contracts and more)
- Fast overview over your own open tasks, leads etc
- Global dashboard


### To-do

Flarepoint is still under heavy development, so there are a lot on my to-do list.

- Multiple integrations (Slack, e-conomic, Google Drive, dropbox etc.)
- Different Color schemes
- API
- Excel Import/export
- Change code style to a PSR-standard, and do a "clean-up"
- Better cache
- Even easier installation
- User tagging

And much more (in no particular order)

### Packages
The packages used are the following...

- [LaravelCollective](https://github.com/LaravelCollective/html)
- [laravel-datatables](https://github.com/yajra/laravel-datatables)
- [laravel-rbac](https://github.com/phpzen/laravel-rbac)
- [Notifynder](https://github.com/fenos/Notifynder)
- [Laravel-Excel](https://github.com/Maatwebsite/Laravel-Excel)

### License

Flarepoint is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
