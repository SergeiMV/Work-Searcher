# Work-Searcher

## Install dependencies

```
docker run --rm --interactive --tty --volume $(pwd):/app composer install
```

## Run code sniffer

```
docker run --rm -v $(pwd):/data cytopia/phpcs --standard=PSR12 index.php src

```

## How to use this bot

```
1) Write in .env your telegram bot token;
2) Start a chat with your bot;
3) Use the following commands:
- '/set_position parameter1 parameter2...' to set your position;
- '/set_parameters parameter1 parameter2...' to set additional parameters;
- '/set_work_ua_loc parameter' to set your location, must contain of english names of cities, or 'remote'.
- '/set_rabota_ua_loc parameter' to set your location, must contain of east-slavik names of cities.
- '/begin' to start searching;
- '/stop' to stop searching;
- '/delete' to delete all the parameters;
```
