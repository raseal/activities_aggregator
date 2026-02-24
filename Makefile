all: help
.PHONY: help status build composer-install build-container start stop down destroy shell test hooks run-tests run-tests-unit run-tests-integration outbox-relay

current-dir := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

help: Makefile
	@sed -n 's/^##//p' $<

## status:	Show containers status
status:
	@docker compose ps

## build:		Start container and install packages
build: build-container start hooks composer-install load-mysql-schema setup-rabbitmq-queues setup-opensearch

## build-container:Rebuild a container
build-container:
	@docker compose up --build --force-recreate --no-deps -d

## start:		Start container
start:
	@docker compose up -d

## stop:		Stop containers
stop:
	@docker compose stop

## down:		Stop containers and remove stopped containers and any network created
down:
	@docker compose down

## destroy:	Stop containers and remove its volumes (all information inside volumes will be lost)
destroy:
	@docker compose down -v

## shell:		Interactive shell inside docker
shell:
	@docker compose exec -w /app/apps/SymfonyClient php_container sh

## install:	Install packages
composer-install:
	docker compose exec php_container composer install

## test:		Run all tests inside docker
test:
	@docker compose exec php_container make run-tests

## test-unit:	Run only unit tests inside docker
test-unit:
	@docker compose exec php_container make run-tests-unit

## test-integration: Run only integration tests inside docker
test-integration:
	@docker compose exec php_container make run-tests-integration

## run-tests:	Run all tests
run-tests:
	XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-text --exclude-group='disabled'

## run-tests-unit: Run only unit tests
run-tests-unit:
	XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-text --testsuite=Unit --exclude-group='disabled'

## run-tests-integration: Run only integration tests
run-tests-integration:
	XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-text --testsuite=Integration --exclude-group='disabled'
## load-mysql-schema: Load MySQL schema (waits for MySQL to be ready)
load-mysql-schema:
	@./etc/infrastructure/scripts/wait-for-mysql.sh
	@echo "🚀 Running migrations..."
	@docker compose exec php_container /app/apps/SymfonyClient/bin/console doctrine:migrations:migrate --no-interaction
	@echo "✅ Migrations completed!"

## setup-rabbitmq-queues: Create RabbitMQ queues for all Domain Events
setup-rabbitmq-queues:
	@echo "🐰 Setting up RabbitMQ queues..."
	@./etc/infrastructure/scripts/wait-for-rabbitmq.sh
	@docker compose exec php_container /app/apps/SymfonyClient/bin/console rabbitmq:setup-queues || echo "⚠️  RabbitMQ setup failed, continuing..."
	@echo "✅ RabbitMQ setup completed!"

## setup-opensearch: Create opensearch indexes
setup-opensearch:
	@echo "🗂️ Setting up OpenSearch indexes..."
	@./etc/infrastructure/scripts/wait-for-opensearch.sh
	@docker compose exec php_container /app/apps/SymfonyClient/bin/console opensearch:init || echo "⚠️  OpenSearch setup failed, continuing..."
	@echo "✅ OpenSearch setup completed!"

## outbox-relay:	Publish pending Domain Events from outbox table to RabbitMQ
outbox-relay:
	@docker compose exec php_container /app/apps/SymfonyClient/bin/console outbox:relay

hooks:
	rm -rf .git/hooks
	ln -s ../docs/git/hooks-docker .git/hooks
