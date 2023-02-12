# ShortURL

ShortURL will be a URL shortener service accessed through an API. It is built in PHP.

ShortURL is not fully implemented yet. Outstanding features are:

* URL Shortening functions.
* Create index page View

## Installation

ShortURL runs using Docker.

First, save the repository locally and add the required environment variables to an .env file.

".env.example" contains the required variables.

```bash
cp .env.example .env
```

Build the required docker containers:

```bash
docker compose build
```

Setup the database using mysql cli within the mysql docker container. (Ensure the docker containers are running - See Usage)

```bash
sudo docker exec -it mysql /bin/bash
```

```bash
mysql -uroot -p
```

```sql
CREATE USER 'api_user'@'%'
	identified by 'api_password';
GRANT ALL
	ON *.*
    TO 'api_user'@'%';

CREATE USER 'api_user'@'localhost'
	identified by 'api_password';
GRANT ALL
	ON *.*
    TO 'api_user'@'localhost';

CREATE DATABASE shorturl
USE shorturl;
CREATE TABLE urls (
    short_url_code CHAR(8) NOT NULL,
    long_url VARCHAR(2048) NOT NULL,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (short_url_code)
) ENGINE=INNODB;

CREATE DATABASE test_shorturl
USE test_shorturl;
CREATE TABLE urls (
    short_url_code CHAR(8) NOT NULL,
    long_url VARCHAR(2048) NOT NULL,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (short_url_code)
) ENGINE=INNODB;

quit
```

### Testing
Run unit & integration tests inside the php-apache container with:

```bash
sudo docker exec -it php-apache /bin/bash
```

```bash
vendor/bin/phpunit tests
```

## Usage

Run the docker containers.
```bash
docker compose up -d
```