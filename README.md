# ShortURL

ShortURL is a URL shortener API. It is built in PHP.


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

Run the docker containers to start the server.
```bash
docker compose up -d
```

ShortURL has 2 API endpoints:

### /shorten?url=<url>
Create a short URL from a long one

#### Example request:

```
GET /shorten?url=google.com HTTP/1.1
```

#### Example response:

```
HTTP/1.1 200 OK
Content-Type: text/plain
http://<hostname>/a7F15gaw
```

### /<shortURL>
Redirect to a standard URL from a previously created short code.

#### Example request:

```
GET /a7F15gaw HTTP/1.1
```

#### Example response:

```
HTTP/1.1 302 Found
Location: http://google.com
```