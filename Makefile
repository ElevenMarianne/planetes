# URL locale : http://localhost:8081
.PHONY: up down rm build install migrate fixtures cc cc-hard logs shell test tailwind e2e e2e-report e2e-serve e2e-ui create-user

up:
	docker compose up -d

down:
	docker compose down

rm:
	docker compose down --rmi all --volumes --remove-orphans

build:
	docker compose build --no-cache

install:
	docker compose exec php composer install

migrate:
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
	docker compose exec php php bin/console messenger:setup-transports

fixtures:
	docker compose exec php php bin/console doctrine:fixtures:load --no-interaction

cc:
	docker compose exec php php bin/console cache:clear

cc-hard:
	docker compose exec php rm -rf var/cache/*
	docker compose exec php php bin/console cache:warmup

logs:
	docker compose logs -f

shell:
	docker compose exec php bash

test:
	docker compose exec php php bin/phpunit

tailwind:
	docker compose exec php php bin/console tailwind:build

tailwind-watch:
	docker compose exec php php bin/console tailwind:build --watch

console:
	docker compose exec php php bin/console $(cmd)

# Créer un astronaute : make create-user firstName=Prénom lastName=Nom email=mail@example.com
# Options : planet=raccoons-of-asgard (défaut: asteroide) | admin=1 (ROLE_ADMIN)
create-user:
	@test -n "$(firstName)" || (echo "❌  Paramètre manquant : firstName=..." && exit 1)
	@test -n "$(lastName)"  || (echo "❌  Paramètre manquant : lastName=..."  && exit 1)
	@test -n "$(email)"     || (echo "❌  Paramètre manquant : email=..."     && exit 1)
	docker compose exec php php bin/console app:astronaut:create \
		"$(firstName)" "$(lastName)" "$(email)" \
		$(if $(planet),--planet=$(planet)) \
		$(if $(admin),--admin)

# Tests E2E Playwright
e2e:
	npx playwright test $(FILE)

e2e-report:
	npx playwright test $(FILE)
	npx playwright show-report --port 9324 --host 0.0.0.0

e2e-serve:
	npx playwright show-report --port 9324 --host 0.0.0.0

e2e-ui:
	npx playwright test --ui
