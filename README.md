## Backend for TrailerTrader

This is the backend for the new TrailerTrader project. It will run Laravel and be consumed by wide arrange of different frontends.

### Installing the project for Development

Requirements
--------------------------------------
- Docker
- Docker compose
- Laravel Nova

Setup
--------------------------------------
Clone the repository:

```bash
git clone git@bitbucket.org:tcentral/backend.git
cd backend
```

Bring the containers up:

```bash
./bin/setup
```

Tooling
--------------------------------------

Start the containers:

```bash
docker-compose start
```

Start the serve (multiples ways):

```bash
./bin/serve
```
```bash
./bin/cli php artisan serve --host 0.0.0.0
```
```bash
./bin/php artisan serve --host 0.0.0.0
```

Get into the PHP container:

```bash
./bin/cli /bin/bash
```

Using the PHP container (examples): `./bin/cli <args>`

```bash
./bin/cli ./artisan tinker
./bin/cli php artisan tinker
./bin/cli ls
./bin/cli uname -a
```

The PHP wrapper (examples): `./bin/php <args>`

```bash
./bin/php -v
./bin/php -m
./bin/php artisan tinker
```

The Postgres wrapper (examples): `./bin/psql <args>`

```bash
./bin/psql --version
./bin/psql trailercenral
```

Apply code styles:

```bash
./bin/fix-style-all
```

*pro-tip: to be able using the local bins add the follows to `.zshrc` or `.profile`*

```
# Options
unsetopt cdablevars

PATH="./bin:./vendor/bin:$PATH"
```

and you could use any binary on this way:

```bash
php artisan tinker
```
```bash
cli /bin/bash
```
```bash
serve
```

Testing 
--------------------------------------

For normal testing 
```bash
./bin/php artisan test --env=testing
```

For parallel testing
```bash
./bin/php artisan test -p --env=testing
```

Test User access
```
tc@trailercentral.com
squadron*RAF99
```

Seeding manually
--------------------------------------
In case it is necessary to seed some data that is thought to be seeded under demand by the tests,
you could use the follows seeders:

```bash
php artisan db:seed --class=Database\\Seeders\\Inventory\\AverageStockSeeder
```
```bash
php artisan db:seed --class=Database\\Seeders\\Inventory\\AveragePriceSeeder
```
```bash
php artisan db:seed --class=Database\\Seeders\\Leads\\LeadsAverageSeeder
```

How data logs works
--------------------------------------

Given the volume of data that is being stored in TrailerCentral database (inventory and website leads), 
we decide to use Postgres because:

 1) To isolate any potential bottlenecks due MySQL
 2) To avoid pulling all the time data from TrailerCentral production database
 3) Prevent any slow query from being executed

Basically, we use the Postgres materialized views and JSONB indexing feature to boost the performance of the queries, 
so to be able maintaining them up-to-date, we need to do the following:

a) Populate inventory data log (`inventory_logs`) with most recent changes
   a.1) how it is determined the most recent changes: 
        - by using the `sync_processes` table, we pull the latest finished sync process for "inventory" (column `name`)
        - then we pull the inventory data from TrailerCentral database that is newer than the last sync process.
   a.2) what table(s)/fields are involved in the above process: 
        - it is only being pulled data form `inventory` table 
        - it is being filtered using `updated_at_auto` column, those records which are greater than or equal 
          than the last sync process `finished_at` 
        - for each new record, we are recording `inventory_id`, `vin`, `brand`, `manufacturer` without any transformation, 
          only `manufacturer` is set up as "na" when it is empty. 
        - `inventory_id` is being recorded as `trailercentral_id`
        - also we are storing the whole record as json in `meta` column (except `geolocation` column), 
          in there is the property `category` is a key field for subsequent use.
   a.3) how to determine if the record is new or an update:
        - if the inventory has a previous record in `inventory_logs`, then it will be treated as an update.
        - if the inventory has not a previous record in `inventory_logs`, then it will be treated as new record
   a.4) how to determine the kind of event:
        - if the inventory is a new inventory, the event is "created"
        - if the inventory is not new inventory, then if the price is equal to the previous one, then the event is "updated",
          otherwise the event is "price-changed"
   a.5) how to determine the inventory status:
        - if the inventory status is any of [2, 3, 4, 5, 6], then its status is "sold"
        - if the inventory status is not any of above-mentioned statuses, then its status is "available"
b) Populate leads data log (`lead_logs`) with most recent changes
    b.1) how it is determined the most recent changes:
       - by using the `sync_processes` table, we pull the latest finished sync process for "leads" (column `name`)
       - then we pull the leads data from TrailerCentral database that is newer than the last sync process.
    b.2) what table(s)/fields are involved in the above process:
       - it is being pulled data form `website_lead` joined with `inventory` table to be able to pull the `category`, 
         `brand`, `manufacturer`, and `vin` of that lead
       - it is being filtered using `date_submitted` column, those records which are greater than or equal
         than the last sync process `finished_at`, also we are filtering the leads by `is_spam` != 0, `inventory_id` != 0
         and `lead_type` equal to "inventory"
       - for each new record, we are recording `inventory_id`, `vin`, `first_name`, `last_name`, `email_address`, 
         `brand`, `manufacturer`, and `vin`, without any transformation
       - `inventory_id` is being recorded as `trailercentral_id`
       - also we are storing the whole record as json in `meta` column (except `metadata` column),
         in there is the property `category` is a key field for subsequent use.
c) Run the view materializer command (`php artisan db:refresh-views`) every day in a time frame that is not affecting 
   the performance of the TrailerCentral database
