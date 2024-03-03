UID := $(shell id -u)
GID := $(shell id -g)

export UID
export GID

up:
	docker compose up -d
up-build:
	docker compose up -d --build

stop:
	@echo "Stoping containers... Let the magic work!"
	docker compose stop

db: drop-db
	docker compose exec php php bin/console doctrine:database:create
db-schema: db
	docker compose exec php php bin/console doctrine:schema:update --force
drop-db:
	-docker compose exec php php bin/console doctrine:database:drop --force
fixtures: db-schema
	docker compose exec php php bin/console doctrine:fixtures:load -n
cache-clear:
	docker compose exec php php bin/console cache:clear
cc: cache-clear
fixcs:
	docker compose exec php tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src --allow-risky=yes --using-cache=no

theme-watch:
	docker compose exec node npm run watch
theme-build:
	docker compose exec node npm run build

reset-rights:
ifdef SUDO_USER
	chown -R $(SUDO_USER):$(SUDO_USER) ./
else
	chown -R $(USER):$(USER) ./
endif