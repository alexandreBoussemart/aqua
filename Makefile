# Raccourcis Docker du projet Aqua — `make` ou `make help` pour la liste.
# Local : docker-compose.yml · Raspberry : make pi-<cible> (docker-compose.pi.yml)

DC      := docker compose
DC_PI   := docker compose -f docker-compose.pi.yml
TTY     := $(shell [ -t 0 ] && echo -t)   # -t seulement dans un terminal interactif
EXEC    := docker exec -i $(TTY) aqua-web
EXEC_T  := docker exec aqua-web
CONSOLE := $(EXEC) php bin/console
URL     ?= http://localhost:8080/aqua

.DEFAULT_GOAL := help
.PHONY: help up start down stop restart reset build ps logs logs-web shell console migrate status-sync \
        cron crons gpio logs-gpio composer cc test test-php test-python e2e css css-watch db pma mail \
        pi-up pi-down pi-logs pi-migrate pi-shell pi-gpio

help: ## Affiche cette aide
	@awk 'BEGIN {FS = ":.*## "} /^##@/ {printf "\n\033[1m%s\033[0m\n", substr($$0, 5)} /^[a-zA-Z0-9_-]+:.*## / {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)
	@echo ""
	@echo "  App : $(URL)/ (admin / admin) · phpMyAdmin : http://localhost:8081 · Mailpit : http://localhost:8025"

##@ Conteneurs
up: ## Construit et démarre tout (web, scheduler, gpio, db, phpmyadmin, mailpit)
	$(DC) up -d --build
start: up
down: ## Arrête les conteneurs
	$(DC) down
stop: down
restart: ## Redémarre web + scheduler
	$(DC) restart web scheduler
reset: ## ⚠ Supprime la BDD locale et repart du dump (+ migrations)
	$(DC) down -v
	$(DC) up -d --build
	@echo "Attente de la BDD…"; until docker exec aqua-db healthcheck.sh --connect --innodb_initialized >/dev/null 2>&1; do sleep 2; done
	$(EXEC_T) php bin/console app:db:migrate
build: ## Reconstruit les images sans cache
	$(DC) build --no-cache
ps: ## État des conteneurs
	$(DC) ps
logs: ## Suit les logs du planificateur (crons)
	docker logs -f aqua-scheduler
logs-web: ## Suit les logs Apache/PHP
	docker logs -f aqua-web
logs-gpio: ## Suit les logs du service GPIO (relais, coupures de sécurité)
	docker logs -f aqua-gpio
shell: ## Shell dans le conteneur web
	$(EXEC) bash

##@ Symfony
console: ## Console Symfony : make console c="debug:router"
	$(CONSOLE) $(c)
migrate: ## Applique les migrations SQL (migrations/*.sql)
	$(CONSOLE) app:db:migrate
status-sync: ## Resynchronise var/status avec la BDD
	$(CONSOLE) app:status:sync
cron: ## Lance un cron à la main : make cron n=osmolateur
	$(CONSOLE) app:cron:$(n)
crons: ## Liste les crons disponibles
	$(CONSOLE) list app:cron
gpio: ## Service GPIO : état (make gpio) ou pilotage (make gpio a="on refuge_eclairage")
	$(CONSOLE) app:gpio $(a)
composer: ## Composer : make composer c="require foo/bar"
	docker exec -i $(TTY) -e COMPOSER_ALLOW_SUPERUSER=1 aqua-web composer $(c)
cc: ## Vide le cache Symfony
	$(CONSOLE) cache:clear

##@ Qualité
test: test-php test-python ## Tous les tests unitaires (PHP + Python)
test-php: ## Tests PHPUnit
	$(EXEC_T) php bin/phpunit
test-python: ## Tests du service GPIO (faux RPi.GPIO)
	$(EXEC_T) python3 -m unittest discover -s tests/Python
E2E_TABLES := data_parametres_eau data_changement_eau data_clean_refuge data_clean_ecumeur data_clean_pompes
e2e: ## Test de bout en bout (pages, formulaires, CSRF…) puis suppression des mesures qu'il a créées : make e2e [URL=…]
	@since=$$(docker exec aqua-db date '+%F %T'); status=0; \
	python3 tests/E2E/smoke.py $(URL) || status=$$?; \
	for t in $(E2E_TABLES); do docker exec aqua-db mariadb -uroot -proot aqua -e "DELETE FROM $$t WHERE created_at >= '$$since'" 2>/dev/null; done; \
	echo "Données de test supprimées (créées depuis $$since)."; exit $$status

##@ Front
css: ## Compile le SCSS (public/build/css)
	$(DC) run --rm assets
css-watch: ## Recompile le SCSS à chaque modification
	$(DC) run --rm assets --watch

##@ Base de données
db: ## Client MariaDB dans le conteneur (base aqua)
	docker exec -i $(TTY) aqua-db mariadb -uroot -proot aqua
pma: ## Ouvre phpMyAdmin
	open http://localhost:8081
mail: ## Ouvre Mailpit
	open http://localhost:8025

##@ Raspberry (docker-compose.pi.yml, à lancer sur le Pi)
pi-up: ## Construit et démarre la prod
	$(DC_PI) up -d --build
pi-down: ## Arrête la prod
	$(DC_PI) down
pi-logs: ## Suit les logs du planificateur
	$(DC_PI) logs -f scheduler
pi-migrate: ## Applique les migrations
	$(DC_PI) exec web php bin/console app:db:migrate
pi-shell: ## Shell dans le conteneur web
	$(DC_PI) exec web bash
pi-gpio: ## État des relais et capteurs (vrais GPIO)
	$(DC_PI) exec web php bin/console app:gpio $(a)
