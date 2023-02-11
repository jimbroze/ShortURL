# ShortURL

ShortURL will be a URL shortener service accessed through an API. It is built in PHP.

ShortURL is not fully implemented yet. Outstanding features are:

* Database connection & queries
* API: Add URL
* API: Retrieve URL
* URL Shortening functions.

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

## Usage

Run the docker containers.
```bash
docker compose up -d
```