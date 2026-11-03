# AQUA — Supervision d'aquarium récifal

Application **Symfony 7.4 / PHP 8.5** qui pilote et supervise un aquarium récifal depuis un Raspberry Pi 4 :
température (eau, air, CPU), osmolateur, écumeur, bailling, éclairage du refuge, analyses d'eau, logs, alertes mail.
Les entrées/sorties matérielles (GPIO) passent par un **service Python 3 permanent** (`scripts/gpio_service.py`, conteneur `gpio`) que PHP interroge en TCP ; toute la logique métier est en PHP.
Langue du projet et des échanges : **français**.

## Architecture

```
bin/console                  commandes (crons app:cron:*, planificateur, migrations)
config/                      config Symfony (security.yaml, services.yaml…)
config.json                  config de l'appli (BDD, SMTP, sondes, utilisateur) — ignoré par git
config/gpio.json             brochage GPIO (entrées, relais, logique active haut/bas, watchdog) partagé PHP + service GPIO
migrations/*.sql             migrations SQL maison (app:db:migrate, table app_migration)
public/                      seul dossier exposé (index.php, build/css|js|fonts, favicon)
src/
  Command/Cron/*Command.php  un cron = une commande app:cron:* (+ CronTools : services communs)
  Command/                   app:scheduler:work, app:db:migrate, app:status:sync, app:gpio (état / pilotage manuel)
  Scheduler/Scheduler.php    planificateur unique (expressions cron en BDD, table cron_task)
  Controller/                Dashboard, Configuration, Analyse, Action (pauses, changements d'eau), Page (logs, monitoring, schéma, câblage GPIO), Hardware (relais, crons), Security
  Repository/                accès BDD Doctrine DBAL (requêtes préparées) : Config, Status, State, Log, Activity, Timer, Measure, CronTask
  Gpio/GpioClient.php        client du service GPIO (read, readStable = double lecture anti-rebond, setRelay, relays)
  Service/                   Relay (via GpioClient), SensorReader (1-Wire), Reminders, WaterParams, Charts, Backup, Dates
  Mail/                      Notifier (Symfony Mailer), AlertMailer (mails d'état, rappels, rapport 8h, anti-spam)
  Domain/Equipment.php       constantes métier (équipements, contrôles, relais, calendrier de nourrissage)
  Domain/Levels.php          interprétation des capteurs de niveau (osmolateur, écumeur, bailling) → états
  Config/AppConfig.php       lecture de config.json ; Database/ConnectionFactory ; Security/ConfigUserProvider
  EventSubscriber/CsrfGuardSubscriber.php   jeton CSRF obligatoire sur tout POST
templates/                   Twig (base + un dossier par page, _partials/)
scripts/gpio_service.py      service GPIO permanent (seul processus qui touche aux broches ; pas de BDD ni de mail)
tests/                       Unit/ (PHPUnit ; Unit/Cron : crons sur SQLite en mémoire + FakeGpioClient), Python/ (service GPIO, faux RPi.GPIO), E2E/smoke.py
.github/workflows/ci.yml     CI GitHub : PHPUnit, PHPStan (niveau 8), lints Symfony, tests Python — sans BDD ni matériel
assets/src/                  sources front (scss/custom.scss, scss/reef.scss, js/custom.js)
var/                         state/ (cache d'état), status/ (interrupteurs), backup/, cache/, log/
docker/                      Dockerfile (cibles dev/prod), conf Apache, entrypoint, faux GPIO, sondes factices, build CSS
```

### Câblage GPIO (page `/aqua/cablage-gpio`)
- Brochage utilisé : `config/gpio.json` (noms d'entrées / relais → GPIO BCM). Schéma de la page : `src/Domain/GpioWiring.php` (clé `gpio` = nom dans gpio.json). `tests/Unit/GpioWiringTest.php` vérifie la cohérence des deux, l'absence de broche en double et le watchdog de la pompe : **toute modification de câblage = mettre à jour gpio.json ET GpioWiring**.
- Broches (BCM → physique) : DS18B20 eau + air 1-Wire GPIO4 → 7 (3,3 V broche 1, pull-up 4,7 kΩ) · écumeur godet GPIO18 → 12 · osmolateur trop haut GPIO23 → 16 · osmolateur niveau OK GPIO24 → 18 · bailling 1/2/3 GPIO8 → 24 / GPIO13 → 33 / GPIO22 → 15 · relais pompe osmolateur GPIO21 → 40 · relais alimentation écumeur GPIO11 → 23 (logique inversée) · relais éclairage refuge GPIO6 → 31 · carte relais 5 V broche 2, GND 6/39.
- Relais pompe et refuge actifs à l'état bas (LOW = allumé), écumeur actif à l'état haut (`active_low: false`). Capteurs de niveau en entrée pull-up (contact vers GND).

### Fonctionnement
- **Interrupteurs** (`core_config_status`) : valeur en BDD + fichier `var/status/<nom>` (lecture rapide par les crons). `app:status:sync` recrée les fichiers depuis la BDD.
- **États** (`core_config_state`) : `StateRepository::set()` n'écrit/logue que si l'état change (cache `var/state/<path>-<value>`).
- **Service GPIO** (`scripts/gpio_service.py`, conteneur `gpio`, port 7070 interne, `GPIO_SERVICE` dans `.env`) : une requête JSON par ligne (`ping`, `read`, `set`, `status`), entrées et relais désignés par leur nom. Garde les broches réservées (les relais conservent leur état). Tous les relais coupés au démarrage et à l'arrêt. **Watchdog** : la pompe de l'osmolateur est coupée si aucune commande « on » ne la confirme depuis 15 s (cron planté). Service injoignable ⇒ crons en erreur GPIO (osmolateur `state_6`, écumeur `state_3`, bailling `state_9`), mail via le cron mail.
- **Crons d'équipement** : double lecture anti-rebond (`GpioClient::readStable` : osmolateur 0,5 s, écumeur 2 s, bailling 20 s), interprétation par `Domain/Levels`. Osmolateur : pompe coupée **en premier** en cas d'erreur ; appoint après niveau atteint (`temps_mini_pompe_osmolateur`) en surveillant le flotteur trop haut ; durée max. de remplissage (`state_8`). Réglages `temps_coupure_pompe_osmolateur` (5–600 s, défaut 25) et `temps_mini_pompe_osmolateur` (0–60 s) : bornes dans `ConfigRepository::BOUNDS`, validées à l'enregistrement et appliquées par `getBounded()` (valeur hors bornes ⇒ défaut, la sécurité n'est jamais désactivée). Logique d'un passage dans `OsmolateurCommand::tick()`, couverte par `tests/Unit/Cron/OsmolateurCommandTest.php` (dont l'ordre « couper la pompe puis enregistrer l'erreur », vérifié par un trigger SQLite).
- **Relais** (`Domain/Equipment::RELAYS`) : `Relay::switch()` → service GPIO ; état réel lu auprès du service (page Relais : « état inconnu » si injoignable). Relais restants : `ecumeur` (« Relais alimentation écumeur »), `pompe_osmolateur`, `refuge_eclairage`.
- **Crons** : un seul processus `app:scheduler:work` (conteneur `scheduler`) lance chaque minute les commandes `app:cron:*` dues selon `cron_task.expression` (modifiable sur `/aqua/crons`, avec activation, prochaine exécution, dernier résultat et bouton Run). Pas de chevauchement : une tâche encore en cours à son échéance est relancée dès sa fin dans la même minute, sinon sautée. Les commandes qui bouclent (osmolateur, écumeur, mail) s'arrêtent à :57.
- **Surveillance** : healthchecks Docker sur `web` (page de login), `scheduler` (fichier `var/scheduler.heartbeat` touché à chaque tour de boucle) et `gpio` (ping). Docker ne relance pas un conteneur « unhealthy », donc auto-arrêt : le planificateur réarme `pcntl_alarm(120)` à chaque tour (SIGALRM remis à l'action par défaut, Symfony Console installant sinon un gestionnaire PHP qui n'agit qu'après déblocage) et le service GPIO s'auto-interroge toutes les 30 s (3 échecs ⇒ relais coupés + `os._exit`). `init: true` (tini en PID 1) indispensable : le noyau ignore les signaux par défaut envoyés au PID 1. Vérifié en figeant la BDD (`docker pause aqua-db`) : planificateur tué à 120 s (code 142) puis relancé.
- **Sécurité** : connexion obligatoire (form_login, utilisateur unique `auth` de config.json, hash bcrypt via `bin/console security:hash-password`), CSRF sur tous les POST (champ `_token` ou en-tête `X-CSRF-TOKEN` ajouté par `$.ajaxSetup`), Twig échappe tout, SQL uniquement via DBAL avec paramètres, noms de tables en liste blanche. `login_throttling` : 5 échecs par utilisateur+IP en 15 min (symfony/rate-limiter, message traduit sur la page de connexion).
- **Mails** : `Notifier::send()` respecte l'interrupteur `mail` (sinon seulement `log_mail`). DSN = `mailer_dsn` de config.json ou construit depuis le bloc `gmail`. Anti-spam : plus de 10 mails en 2 min ⇒ interrupteur `mail` coupé.

## Thème graphique « Récif »
- Base Gentelella (`library.min.css`, `custom.min.css`) + surcouche `assets/src/scss/reef.scss` → `public/build/css/reef.min.css` (chargée en dernier via `templates/_partials/head_assets.html.twig`).
- Palette : abysse `#031D33`, océan `#0B5C80`, lagon `#0096C7`, aqua `#48CAE4`, corail `#FF6F61`, anémone `#F77F00`, sable `#F6E7CB`, algue `#2A9D8F`. Polices Google : Fredoka (titres), Nunito (texte).
- Illustrations SVG maison dans `public/build/img/` : `clownfish.svg` (poisson-clown), `bluetang.svg` (chirurgien bleu), `yellowtang.svg` (chirurgien jaune, *Zebrasoma flavescens*), `chelmon.svg` (*Chelmon rostratus*), `lysmata.svg` (crevette *Lysmata debelius*, marche sur le sable), `reef-bottom.svg` (barrière de corail), `sidebar-coral.svg`. Décor animé : `templates/_partials/reef_decor.html.twig`, désactivé si `prefers-reduced-motion`. Poissons orientés vers la droite dans les SVG ; classe `.reverse` = nage vers la gauche (retournement via la propriété CSS `scale`, cumulable avec l'animation `transform`).
- Barre du haut (`_partials/top_nav.html.twig`) : horloge en pastille (date en toutes lettres, heure, secondes), soleil le jour / lune la nuit (plage d’éclairage du refuge), mise à jour par `date_time()` dans `custom.js` (sources + build + min). La barre `.nav_menu` (fixe, 55 px) est ancrée entre le menu latéral et le bord droit.
- Menu latéral réduit (☰, préférence mémorisée en `localStorage` `aqua-nav`) : pastilles d'icônes + libellé court sous l'icône (`short` dans `_partials/menu.html.twig`) + bulle avec le libellé complet au survol.
- Poisson-clown redessiné (*Amphiprion ocellaris* : queue arrondie, nageoires bordées de noir, bande médiane en pointe vers l'avant).
- Graphiques : **Chart.js 4.4.7 + chartjs-plugin-annotation 3.1.0** (servis localement, `public/build/js/vendor/`), rendu par `public/build/js/reef-charts.js` à partir de `App\Service\ChartBuilder` (limites min/idéal/vigilance/max par graphique dans `Service/Charts.php` et `WaterParams::PARAMS`). Zone de confort verte, seuils étiquetés, points/segments hors limites en corail (au-dessus) ou bleu foncé (en dessous), pastille « N valeurs hors limites », axe élargi automatiquement (pas arrondi, jamais négatif pour des grandeurs positives), courbes monotones, année affichée si la période dépasse 10 mois, changements d'eau en traits verticaux sur les 7 graphiques de l'analyse (un repère par jour, à l'heure réelle, depuis la 1re mesure affichée ; un changement postérieur à la dernière analyse prolonge l'axe X). Morris.js n'est plus utilisé.
- Loader : aquarium avec poisson-clown qui nage (`.loading-mask` / `.reef-loader` dans `base.html.twig`), affiché par `showLoader()` (AJAX des interrupteurs) et par `reef-ui.js` à l'envoi de tout formulaire POST (attribut `data-no-loader` pour l'éviter).
- Bulle d'aide : `{{ include('_partials/info.html.twig', {text: …, title: …}) }}` (icône « i », bulle océan avec poisson-clown et bulles au survol/focus). Icône placée à droite du champ (`.input-with-info`) ou de l'interrupteur (`.switch-with-info`). Textes des réglages et interrupteurs : colonne `description` de `core_config_value` / `core_config_status` (migration 006).
- Mise en page : à partir de 768 px, chaque `.row` du contenu passe en flex : les blocs côte à côte ont la même hauteur (le `.x_panel` remplit sa colonne). Une colonne = un panneau.
- Tuiles d'état : vert algue = OK, corail pulsant = erreur. Couleurs des graphiques : palette `C` de `public/build/js/reef-charts.js`.

## Environnement local (Docker)

Toutes les commandes passent par le **Makefile** (`make` ou `make help` affiche la liste) :

```bash
make up                   # construire et démarrer (web + scheduler + gpio + db + phpmyadmin + mailpit)
make down                 # arrêter
make reset                # ⚠ supprimer la BDD locale, réimporter le dump et appliquer les migrations
make restart | ps | shell # redémarrer web + scheduler / état / shell dans aqua-web
make logs | logs-web      # suivre le planificateur / Apache
make migrate              # appliquer les migrations SQL
make cron n=osmolateur    # lancer un cron à la main (ou bouton Run sur /aqua/crons) ; make crons = liste
make gpio                 # état des relais et capteurs ; make gpio a="on refuge_eclairage" ; make logs-gpio
make console c="debug:router"   # n'importe quelle commande Symfony ; make cc = vider le cache
make composer c="require foo/bar"
make test                 # tests unitaires PHP + Python (make test-php / test-python)
make stan                 # PHPStan niveau 8 (phpstan.dist.neon) ; make lint = Twig + YAML + conteneur
make e2e                  # test de bout en bout (tests/E2E/smoke.py) puis suppression des mesures qu'il a créées
make css | css-watch      # compiler le SCSS (assets/src/scss → public/build/css) / recompiler à chaque modification
make db | pma | mail      # client MariaDB / phpMyAdmin / Mailpit
make pi-up | pi-down | pi-logs | pi-migrate | pi-shell | pi-gpio   # sur le Raspberry (docker-compose.pi.yml)
```

Équivalents bruts : `docker compose up -d --build`, `docker compose down [-v]`, `docker exec aqua-web php bin/console …`, `docker compose run --rm assets [--watch]`.

- App : **https://reef-pi.app.local/** ou http://localhost:8080/aqua/ (**admin / admin**) · phpMyAdmin : http://localhost:8081 · Mailpit : http://localhost:8025 · MariaDB 11.8 : `localhost:3307` (root/root, base `aqua`).
- Image `docker/php/Dockerfile`, cible `dev` (contexte = racine du dépôt) : PHP 8.5 + Apache (`docker/php/aqua.conf` : `Alias /aqua → public/`), pdo_mysql, zip, pcntl, client MariaDB, Python 3.13 (service GPIO, sans dépendance), faux `RPi.GPIO` (`docker/python/RPi/GPIO.py`, monté dans le conteneur `gpio` : entrées dans `docker/gpio/<pin>` — défaut 1, sauf 23 « trop haut » à 0 —, sorties tracées dans `var/log/gpio-fake.log`). Simuler un capteur : `echo 0 > docker/gpio/24` ; voir l'état : `make gpio`.
- HTTPS local : le Traefik partagé de la machine (`~/Workspaces/OUTILS/TRAEFIKV2`, réseau Docker externe `traefik`, ports 80/443) relaie le TLS de `reef-pi.app.local` **sans le déchiffrer** (routeur TCP `HostSNI` + `tls.passthrough`, labels du service `web`) ; Apache le termine (`docker/php/aqua-ssl.conf`, `mod_ssl` dans la cible dev) avec le certificat mkcert `docker/certs/reef-pi.app.local*.pem` (ignoré par git, `make certs` pour le régénérer). Aucune modification de la config Traefik partagée. Sur ce vhost l'appli est servie **à la racine** (`DocumentRoot public/`, `/aqua/...` redirigé en 301) ; en HTTP (port 8080, Pi) elle reste sous `/aqua` (`aqua.conf`, redirection `/` → `/aqua/` conditionnée à `%{HTTPS} != 'on'`). Aucun chemin `/aqua` en dur : Symfony déduit le préfixe. `aqua.conf` est copié dans l'image : `make up` après modification. `/etc/hosts` : `127.0.0.1 reef-pi.app.local` + `::1 reef-pi.app.local` (la ligne IPv6 évite le délai de résolution mDNS des `.local` sur macOS). Le port 8080 reste disponible.
- Le dépôt est monté dans les conteneurs ; `docker/config.docker.json` est monté par-dessus `config.json` (BDD `db`, Mailpit, sondes factices `docker/sensors/w1_slave_*`, utilisateur admin/admin).
- `docker/db/01-dump.sql` (ignoré par git) : dump importé au premier démarrage, puis `app:db:migrate`.
- Compilation CSS : service `assets` (profil `tools`, `node:22-alpine`, `docker/assets/build-css.sh`) = Dart Sass + Autoprefixer (`.browserslistrc` : `last 2 versions, > 5%`). Remplace l'ancienne chaîne gulp 3 / Ruby Sass. `library.min.css` et `custom.min.js` sont figés.
- Test de bout en bout : `tests/E2E/smoke.py` (Python 3 sur l'hôte, sans dépendance : login, toutes les pages, tous les formulaires, AJAX, CSRF, XSS). Il enregistre des mesures factices (valeurs 1, changements d'eau de 12 L) : toujours le lancer via `make e2e`, qui les supprime ensuite.

## Production cible : Docker sur le Raspberry

- Décision (2026-11-02) : l'appli tournera **dans Docker sur le Pi** (Raspberry Pi OS **64 bits** sur une carte SD neuve ; l'image MariaDB n'existe pas en 32 bits).
- `docker-compose.pi.yml` : `web` (port 80, http://<pi>/aqua/), `scheduler`, `gpio`, `db` (MariaDB 11.8), `phpmyadmin` (8081). Image `prod` construite sur le Pi (arm64) : code copié, `composer install --no-dev`, OPcache, **rpi-lgpio** (bibliothèque `lg` 0.2.2 compilée). Seul le conteneur `gpio` a `devices: /dev/gpiochip0` (l'entrypoint ajoute www-data au groupe du périphérique ; service lancé sous www-data). La connexion BDD est `lazy` (le préchauffage du cache au build n'a pas besoin de config.json). `/sys` (sondes 1-Wire, CPU) est lisible dans le conteneur. Image testée en build arm64 sur le Mac.
- Fichiers à créer sur le Pi : `config.json` (host `db`, `auth`), `docker/pi/pi.env` (cf. `pi.env.sample` : APP_SECRET, MARIADB_ROOT_PASSWORD), dump dans `docker/db/`.
- `RPi.GPIO` ne gère plus la détection de fronts sur les noyaux ≥ 6.6 → `rpi-lgpio` (même API).

## Raspberry actuel (ancienne appli, jusqu'à la migration)

- Pi 4 Model B, Raspbian 10 Buster **32 bits**, Wi-Fi uniquement, IP fixe `192.168.1.16` (dhcpcd). SSH `pi@192.168.1.16` par clé (`~/.ssh/id_ed25519`), `sudo` sans mot de passe. L'IP `.18` de `raspberry.txt` est obsolète.
- Ancienne appli PHP 7.4 dans `/home/pi/Desktop/www/aqua` (vhost `AllowOverride All`), crons dans la crontab **root** (suspendus le 2026-11-02, lignes préfixées `#PAUSE-UPGRADE#`). L'appli du Pi ne pilote pas un aquarium réel actuellement.
- 2026-11-02 : mise à jour sur place Buster → Bullseye lancée (`/root/dist-upgrade.sh`, log `/root/upgrade-bullseye.log`, miroir `ftp.halifax.rwth-aachen.de`). **Ne pas poursuivre** vers Bookworm/Trixie : la cible est une carte SD neuve 64 bits + Docker.
- Sauvegardes du Pi (BDD complète, /etc, projet, crontab, config.txt) : `/Users/alex/Workspaces/AQUA/backup-pi-20261102/`.
- Sécurité appliquée sur l'ancien Pi : `.htaccess` (seul `web/` public), phpMyAdmin derrière une auth HTTP Basic (`/etc/apache2/conf-available/phpmyadmin-restrict.conf`, utilisateur `admin`), mot de passe root MariaDB changé (stocké uniquement dans `config.json` du Pi).
- Sondes 1-Wire : aucun capteur `28-*` détecté sur le Pi au 2026-11-02 (débranché ou HS).
- ⚠️ `raspberry.txt` et `config.json` contiennent des mots de passe : ne jamais les recopier dans le dépôt ni ici. Le mot de passe Gmail `raspberrypi.recifal` a été exposé : **à changer**.

## Vocabulaire
- **Refuge** (ex-« réacteur », renommé le 2026-11-02, migration 009) : nettoyage (`data_clean_refuge`, `check_clean_refuge`), éclairage en plage horaire (`refuge_eclairage`, `eclairage_refuge_debut|fin`, commande `app:cron:refuge-eclairage`, scripts `scripts/refuge_eclairage/`, relais GPIO6). Ne plus utiliser « réacteur » pour ces fonctions.

## Fonctionnalités supprimées (2026-11-02, migrations dans `migrations/`)
- Débit du réacteur (capteur, `reacteur.py`, table `data_reacteur`, seuil, pause réacteur, contrôle `controle_reacteur`).
- Ventilateurs du réacteur la nuit (`reacteur_ventilateur`) — l’éclairage (devenu celui du refuge) est conservé.
- Cycles d'éclairage du réacteur (1 h allumé / 10 min de pause) : remplacés par une plage horaire simple, réglages `eclairage_refuge_debut` / `eclairage_refuge_fin` (type `time`, défaut 22:30 → 10:00, peut passer minuit). L'horloge jour/nuit de la barre du haut suit cette plage (`lighting_period()` Twig + `data-start`/`data-end`).
- Page Budget (`/budget`, table `data_depense`).
- Relais ventilateur aquarium (pilotage selon la température, `config_temperature_declenchement`, `force_turn_off_ventilateur`).
- Paramètres obsolètes : `timer_reacteur`, `log_in_files`.

## Conventions
- Code et messages en français. PSR-4 `App\`, services autowirés, contrôleurs à attributs `#[Route]`.
- Toute modification de schéma/données = nouveau fichier `migrations/AAAA_MM_JJ_NNN_description.sql` (ne jamais modifier une migration déjà appliquée). Une requête se termine par `;` **en fin de ligne** (les `;` dans un texte sont autorisés) ; pas de procédures stockées.
- Toute nouvelle commande `app:cron:*` est ajoutée automatiquement à `cron_task` (planifiée chaque minute) au démarrage du planificateur ou à l'ouverture de `/crons`.
- Ne pas adapter le code au local : passer par `docker/` (config montée, faux GPIO, sondes factices).
- Pied de page : « v4.0.0 - Copyright © 2027 ».

## Journal des échanges
- **2026-11-02** — Docker local (PHP 7.3 puis 7.4) ; récupération des fichiers du Pi ; création de ce fichier.
- **2026-11-02** — Pi : `.htaccess` (le dossier `/aqua/` était listable, `config.json` et `raspberry.txt` lisibles), clé SSH (`/home/pi` était en 777), phpMyAdmin protégé, mot de passe root MariaDB changé.
- **2026-11-02** — Montée de version : PHP 8.5, MariaDB 11.8, phpMyAdmin 5.2, Python 3 ; Swiftmailer → symfony/mailer ; dump via mariadb-dump ; sécurisation (requêtes préparées, XSS, injection de commande dans runCron). Commit `7870be72` sur la branche `upgrade-php85`.
- **2026-11-02** — Pi : sauvegardes, crons suspendus, mise à jour Buster → Bullseye lancée (1re tentative échouée sur des miroirs injoignables, relancée avec un miroir fixe).
- **2026-11-02** — Refonte **Symfony 7.4** (branche `symfony`) : Twig, Doctrine DBAL, Security (login + CSRF), commandes console, migrations SQL ; suppression de l'ancien code `app/` et `web/`. Commande de compilation CSS (Dart Sass). Suppressions : débit réacteur, ventilateurs réacteur, budget, ventilateur aquarium. Planificateur unique avec planification éditable sur `/crons` (remplace la crontab, en local comme sur le Pi). Image `prod` arm64 avec rpi-lgpio et `docker-compose.pi.yml`. Relais écumeur renommé « Relais alimentation écumeur ». Thème graphique « Récif » (poissons, coraux, bulles, page de connexion sous-marine), vérifié par captures Playwright (desktop + mobile). Menu réduit retravaillé (icônes + petit libellé + bulle). Page « Câblage GPIO » (brochage du Pi 4 + tableau, cohérence avec les scripts vérifiée par test). Graphiques Chart.js avec gestion des dépassements. Descriptions de tous les réglages en bulle d'aide récifale. Libellés des réglages réécrits (migration 007), bulle d'aide positionnée en JS (`public/build/js/reef-ui.js`, au-dessus ou en dessous selon la place, rattachée au body), icône « i » en bout de libellé. Éclairage du réacteur : plage horaire configurable sans pauses (migration 008), réglages de type `time` validés à l'enregistrement. Icônes d'aide à droite des champs ; loader poisson-clown à chaque enregistrement. Bloc « Actions » de la configuration en cartes (nettoyages avec jauge jusqu'au rappel, relecture des sondes sans popup, purge isolée en corail) ; températures affichées à la française (« 25,63 °C »). Le réacteur devient le **refuge** partout (code, scripts, BDD, interface). `app:status:sync` supprime les fichiers d'état orphelins. Messages flash avec marges et icône. Blocs côte à côte de même hauteur sur toutes les pages.
- **2026-11-03** — README.md à la racine ; commit de la refonte Symfony sur la branche `symfony`.
- **2026-11-03** — Analyse : changements d'eau vérifiés sur les 7 graphiques ; corrigé le repère du jour de la dernière mesure (placé à midi, il sortait de l'axe) et les changements faits après la dernière analyse (ignorés). **Makefile** pour toutes les commandes Docker ; test e2e versionné (`tests/E2E/smoke.py`, `make e2e` nettoie ses données) ; données de test parasites supprimées de la BDD locale.
- **2026-11-03** — Merge de `symfony` dans `master` (fast-forward, push GitHub refusé : la clé SSH du Mac est celle du compte `aboussemart`). Effet de bord : l'ancien `master` versionnait `vendor/` → `vendor/autoload.php` supprimé au checkout, appli locale cassée ~20 min (08:12-08:35), réparé par `composer install` ; logs parasites supprimés.
- **2026-11-03** — Scripts Python remplacés par un **service GPIO permanent** (`scripts/gpio_service.py`, conteneur `gpio`, brochage `config/gpio.json`) ; logique des niveaux portée en PHP (`Domain/Levels`, `GpioClient`). Corrigé : pompe non coupée si la BDD tombait, récursion infinie mail/log quand la BDD était en panne, flotteur trop haut non surveillé pendant l'appoint, ~240 processus Python/min, relais libérés à la fin de chaque script (rpi-lgpio), mails SMTP envoyés depuis la boucle osmolateur, `test.py` faux. Ajouts : watchdog pompe 15 s, `app:gpio` / `make gpio`, tests Python + PHP (Levels, GpioClient, cohérence gpio.json). Scénarios vérifiés en local : remplissage, appoint, trop haut pendant l'appoint, durée max., planificateur tué (pompe coupée à 15 s), service GPIO arrêté/redémarré. Connexion BDD `lazy` (le build prod échouait au préchauffage du cache).
- **2026-11-03** — URL locale **https://reef-pi.app.local/** (à la racine, sans `/aqua`) via le Traefik existant en TLS passthrough (certificat mkcert dans le projet, Apache termine le TLS) ; `make certs`.
- **2026-11-03** — Healthchecks + auto-redémarrage du planificateur (alarme 120 s, `init: true`) et du service GPIO ; tests automatiques de l'osmolateur (SQLite en mémoire, faux GPIO, vérifiés par mutations) ; durée max. de remplissage en secondes bornées (migration 010) ; limitation des tentatives de connexion ; PHPStan niveau 8 (53 erreurs corrigées) ; CI GitHub Actions ; suppression de `assets/vendors` (94 Mo) et de l'ancienne chaîne gulp ; connexion BDD figée pendant le test → bug Symfony/PID 1 trouvé et corrigé. Page Analyse : bloc de saisie en cartes (symbole, cible, valeur + état, jauge, tendance, champ avec unité). Branches Dependabot distantes : suppression refusée par les permissions, à faire par l'utilisateur.
