# Aqua — Reef Pi

Application de supervision et de pilotage d'un aquarium récifal sur Raspberry Pi 4.

- Lecture des sondes : température de l'eau et de l'air (1-Wire DS18B20), CPU du Pi, niveaux de l'osmolateur, du godet d'écumeur et du bailling.
- Pilotage des relais : alimentation de l'écumeur, pompe de l'osmolateur, éclairage du refuge.
- Alertes par mail, rappels d'entretien, suivi des analyses d'eau avec graphiques.

**Stack** : Symfony 7.4 · PHP 8.5 · MariaDB 11.8 · Doctrine DBAL · Twig · Python 3.13 (scripts GPIO) · Chart.js · Docker.

## Démarrage rapide (local)

Prérequis : Docker Desktop, `make`, Python 3 (pour le test de bout en bout uniquement).

```bash
# dump de la base (optionnel) : fichier .sql à placer dans docker/db/ (ignoré par git)
make up            # construit et démarre tous les conteneurs
make migrate       # applique les migrations SQL
```

| Service | URL |
|---|---|
| Application | http://localhost:8080/aqua/ — identifiants **admin / admin** |
| phpMyAdmin | http://localhost:8081 |
| Mailpit (mails interceptés) | http://localhost:8025 |
| MariaDB | `localhost:3307` (root / root, base `aqua`) |

En local, le GPIO est simulé : un faux `RPi.GPIO` lit les entrées dans `docker/gpio/<pin>` et trace les sorties dans `var/log/gpio-fake.log`. Les sondes de température sont factices (`docker/sensors/`).

## Commandes

`make` affiche la liste complète.

| Commande | Rôle |
|---|---|
| `make up` / `make down` / `make restart` | démarrer / arrêter / redémarrer web + planificateur |
| `make reset` | ⚠ supprime la base locale, réimporte le dump et applique les migrations |
| `make logs` / `make logs-web` | logs du planificateur (crons) / d'Apache |
| `make shell` | shell dans le conteneur web |
| `make migrate` | applique les migrations `migrations/*.sql` |
| `make cron n=osmolateur` / `make crons` | lance un cron à la main / liste des crons |
| `make console c="debug:router"` | commande Symfony quelconque |
| `make composer c="require foo/bar"` | Composer |
| `make cc` | vide le cache Symfony |
| `make test` | tests unitaires (PHPUnit) |
| `make e2e` | test de bout en bout, puis suppression des données qu'il a créées |
| `make css` / `make css-watch` | compile le SCSS (Dart Sass + Autoprefixer) |
| `make db` / `make pma` / `make mail` | client MariaDB / phpMyAdmin / Mailpit |

## Organisation

```
config/          configuration Symfony (security, services…)
src/
  Command/       commandes console : crons (app:cron:*), migrations, planificateur
  Controller/    pages (dashboard, configuration, analyse, logs, monitoring, relais, crons, GPIO…)
  Domain/        équipements, relais, câblage GPIO
  Repository/    accès BDD (Doctrine DBAL, sans ORM)
  Scheduler/     planificateur unique (remplace la crontab)
  Service/       sondes, relais, rappels, graphiques, sauvegarde…
templates/       vues Twig
scripts/         scripts Python (GPIO) appelés par les crons
migrations/      migrations SQL (appliquées par app:db:migrate)
assets/src/scss/ thème « Récif » → public/build/css
docker/          image PHP (dev/prod), config locale, faux GPIO, sondes factices
tests/           tests unitaires (Unit/) et de bout en bout (E2E/)
```

## Planification des tâches

Un seul processus, le service `scheduler` (`app:scheduler:work`), exécute toutes les commandes `app:cron:*`. Leur fréquence (expression cron) se règle depuis la page **Liste cron** (`/aqua/crons`). Une tâche peut aussi y être lancée à la main.

## Production sur le Raspberry Pi

Cible : Raspberry Pi OS **64 bits** + Docker. L'accès au GPIO passe par `rpi-lgpio` et `/dev/gpiochip0`.

```bash
cp config.json.sample config.json                  # BDD (host "db"), SMTP, utilisateur
cp docker/pi/pi.env.sample docker/pi/pi.env        # APP_SECRET, mot de passe root MariaDB
make pi-up                                         # docker compose -f docker-compose.pi.yml up -d --build
make pi-migrate
```

Le mot de passe de connexion se génère avec `bin/console security:hash-password`. Le câblage des capteurs et des relais est consultable sur la page **Câblage GPIO**.

> `config.json`, `docker/pi/pi.env` et les dumps SQL contiennent des secrets : ils sont ignorés par git et ne doivent jamais être commités.

## Documentation détaillée

`CLAUDE.md` contient l'architecture, les conventions, les choix techniques et l'historique des évolutions.
