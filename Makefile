PHP_VERSION ?= 8.4
VERSION ?= $$(git rev-parse --verify HEAD)
USER = $$(id -u)

# https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
.PHONY: help
.DEFAULT_GOAL := help

help: ## Display this help screen
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

## App

up: ## Run server
	USER=$(USER) docker compose --profile serve up -d --remove-orphans --force-recreate

stop: ## Stop server
	docker compose --profile serve stop

restart:
	USER=$(USER) docker compose --profile serve restart

down: stop
	docker compose down --remove-orphans

build:
	- USER=$(USER) docker compose build postgres
	- USER=$(USER) docker compose build mysql
	- USER=$(USER) docker compose build manticore

remove: down _image_remove _container_remove _volume_remove

composer-up: ## composer update
	docker run --init -it --rm -u $(USER) -v "$$(pwd)/src/php:/src/php" -w /src/php \
		composer:latest \
		composer update \
		--ignore-platform-reqs

composer-autoload:
	docker run --init -it --rm -u $(USER) -v "$$(pwd)/src/php:/src/php" -w /src/php \
		composer:latest \
		composer dump-autoload

app:
	- docker build --target app_devel -t app_cli .docker/php/cli
	- docker run --init -it --rm \
		--network search_search \
		--add-host=host.docker.internal:host-gateway \
		--env-file .docker/environment/base.env \
		-u $(USER) \
		-v "$$(pwd)/runtime:/runtime" \
		-v "$$(pwd)/source:/source" \
		-v "$$(pwd)/src/php:/src/php" \
		-w /src/php \
		app_cli sh
	- docker image rm -f app_cli

pgsql:
	VERSION=$(VERSION) USER=$(USER) docker compose run --rm -u $(USER) -w /src postgres sh

mysql:
	VERSION=$(VERSION) USER=$(USER) docker compose run --rm -u $(USER) -w /src mysql sh

manticore:
	VERSION=$(VERSION) USER=$(USER) docker compose run --rm -u $(USER) -w /etc/manticore manticore bash

manticore-rotate:
	VERSION=$(VERSION) USER=$(USER) docker compose exec manticore indexer --all --rotate

migrate-init:
	- docker build --target app_devel -t app_cli .docker/php/cli
	- docker run --init -it --rm \
		--network search_search \
		--add-host=host.docker.internal:host-gateway \
		--env-file .docker/environment/base.env \
		-u $(USER) \
		-v "$$(pwd)/runtime:/runtime" \
		-v "$$(pwd)/source:/source" \
		-v "$$(pwd)/src/php:/src/php" \
		-w /src/php/app \
		app_cli php migrator.php migrate:init
	- docker image rm -f app_cli

migrate-up:
	- docker build --target app_devel -t app_cli .docker/php/cli
	- docker run --init -it --rm \
		--network search_search \
		--add-host=host.docker.internal:host-gateway \
		--env-file .docker/environment/base.env \
		-u $(USER) \
		-v "$$(pwd)/runtime:/runtime" \
		-v "$$(pwd)/source:/source" \
		-v "$$(pwd)/src/php:/src/php" \
		-w /src/php/app \
		app_cli sh -c 'php migrator.php migrate:up && php migrator.php migrate:fixture'
	- docker image rm -f app_cli

migrate-redo:
	- docker build --target app_devel -t app_cli .docker/php/cli
	- docker run --init -it --rm \
		--network search_search \
		--add-host=host.docker.internal:host-gateway \
		--env-file .docker/environment/base.env \
		-u $(USER) \
		-v "$$(pwd)/runtime:/runtime" \
		-v "$$(pwd)/source:/source" \
		-v "$$(pwd)/src/php:/src/php" \
		-w /src/php/app \
		app_cli php migrator.php migrate:redo
	- docker image rm -f app_cli

_image_remove:
	docker image rm -f \
		search-postgres \
		search-mysql \
		search-manticore \
		search-opensearch \
		search-opensearch_dashboards

_container_remove:
	docker rm -f \
		search_postgres \
		search_mysql \
		search_manticore \
		search_opensearch \
		search_opensearch_dashboards

_volume_remove:
	docker volume rm -f \
		search_pg_data \
		search_mysql_data \
		search_manticore_data \
		search_opensearch_data
