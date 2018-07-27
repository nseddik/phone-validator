# South african mobile numbers validator

Laravel based PHP application to test, attempt to fix, and reject South african mobile numbers. 

It exposes an API : 
- import and test phone numbers from a CSV file
- display the report result of imported numbers

It also offers a user form to test a single number.

## Requirements

- docker 18.06.0
- docker-compose 1.22.0

## Install

Install the composer

```
docker-compose run composer install
```

Build the application

```
docker-compose build app
``` 

## Run

Launch Laravel artisan server

```
docker-compose up app
```

Upload a CSV file

```
curl -F 'phoneFile=@/path/to/file.csv' http://127.0.0.1:8000/api/phone
```

Note: current implementation does not allow to store twice the same number. 
As a workaround, you can use the following command to replay the migrations and truncate tables

```
docker-compose run app php artisan migrate:refresh --seed
```  

Display the report result of imported numbers

```
curl http://127.0.0.1:8000/api/phone | jq
```

`status` query parameter can be used to filter by status (`valid`, `fixed`, `rejected`)

```
curl http://127.0.0.1:8000/api/phone?status=valid  | jq
```

Test a single number in a browser

```
open http://127.0.0.1/phone
```

## Test

```
docker-compose run app ./vendor/bin/phpunit
```

## Todo

- `UPSERT` support to be able to import several times the same number
- `importId`: create a unique import identifier associated with all numbers imported in that batch