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
php artisan db:seed --class=Database\\Seeders\\Leads\\AverageSeeder
```

How data logs works
--------------------------------------

Please read/maintain the [Confluence](https://operatebeyond.atlassian.net/wiki/spaces/TN/pages/1923219457/How+data+logs+works) page up to date:
