## Backend for TrailerTrader

This is the backend for the new TrailerTrader project. It will run Laravel and be consumed by wide arrange of different frontends.

### Installing the project for Development

Requirements
--------------------------------------
- Docker
- Docker compose

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

Daily work
--------------------------------------

Start the containers:

```bash
docker-compose start
```

Start the server:

```bash
./bin/server
```
