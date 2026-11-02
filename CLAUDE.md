# AQUA — Supervision d'aquarium récifal

Application PHP (sans framework) qui pilote et supervise un aquarium récifal depuis un Raspberry Pi :
températures (eau, air, CPU du Pi), osmolateur, écumeur, réacteur, bailling, relais, analyses d'eau, budget, logs, crons.
Langue du projet et des échanges : **français**.

## Architecture

- `web/` — DocumentRoot. Pages (`index.php`, `configuration.php`, `analyse.php`, `schema.php`, `logs.php`, `monitoring.php`, `budget.php`, `relais.php`, `crons.php`). `.htaccess` réécrit `/page` → `/page.php`.
- `web/controller/` — endpoints AJAX/POST (`save*.php`, `getCurrent*.php`, `runCron.php`, `execRelais.php`).
- `app/code/helper/` — `app.php` (≈1800 lignes, toute la logique métier), `bdd.php` (lit `config.json` à la racine, `mysqli_report(MYSQLI_REPORT_OFF)` pour garder le comportement PHP 7, ouvre `$link` mysqli et `$transport` Symfony Mailer via `createMailerTransport()`), `const.php` (noms des tables et des contrôles).
- `app/code/templates/` — fragments PHP inclus par les pages.
- `app/code/cron/` — scripts PHP lancés par la crontab du Pi (temperature, osmolateur, ecumeur, reacteur, bailling, mail, dump…).
- `scripts/` — scripts Python 3 (UTF-8) qui pilotent les GPIO (`RPi.GPIO`) et accèdent à la BDD (`mysql.connector`). Chemins relatifs au dépôt (`BASE_DIR`), plus de `/home/pi/...` en dur. Appelés par PHP via `exec("python3 …")`.
- Mails PHP : `symfony/mailer` (remplace Swiftmailer, abandonné). DSN = `mailer_dsn` de `config.json` s'il est renseigné, sinon construit depuis `gmail` (`port_php` 465 → `smtps`). Mails Python : `smtplib` + STARTTLS sur `gmail.port`.
- Dump BDD (`cron/dump.php` → `dumpDatabaseToZip()`) : `mariadb-dump`/`mysqldump` + `ZipArchive` (remplace `phelium/mysql-backup`, abandonné). Nécessite l'extension PHP `zip` et le client MariaDB.
- `config/`, `state/`, `statusRelais/` — fichiers « drapeaux » vides utilisés comme état (créés et supprimés via `exec("touch …")` / `rm`). Ignorés par git.
- `var/log`, `var/backup` — logs et dumps SQL automatiques. Ignorés par git.
- Base MariaDB `aqua` (17 tables : `core_config_*`, `data_*`, `log`, `log_mail`, `timer`, `last_activity`).
- `config.json` (ignoré par git) : identifiants SMTP Gmail, BDD, `base_url`, chemins des sondes 1-Wire.

## Production (Raspberry Pi)

- Raspbian Buster, Apache 2.4.38, PHP 7.4 (mod_php), MariaDB. Appli servie sur `http://192.168.1.16/aqua/web/`.
- Projet sur le Pi : `/home/pi/Desktop/www/aqua` (DocumentRoot `/home/pi/Desktop/www`, vhost `000-default.conf` avec `AllowOverride All` → les `.htaccess` sont actifs). phpMyAdmin sur `/phpmyadmin` (symlink vers `/usr/share/phpmyadmin`), protégé depuis le 2026-11-02 par une auth HTTP Basic : `/etc/apache2/conf-available/phpmyadmin-restrict.conf` (activée via `a2enconf`), utilisateur `admin`, fichier `/etc/apache2/.htpasswd-phpmyadmin` (mot de passe à changer avec `sudo htpasswd -B /etc/apache2/.htpasswd-phpmyadmin admin`). Localhost exempté.
- SSH : `ssh pi@192.168.1.16` par clé (`~/.ssh/id_ed25519`, ajoutée le 2026-11-02). `sudo` sans mot de passe. L'IP `.18` notée dans `raspberry.txt` est obsolète.
- Sondes 1-Wire : au 2026-11-02, aucun capteur `28-*` n'est détecté sur le Pi (débranché ou HS) → « fichier w1_slave n'existe pas ».
- Le dossier `http://192.168.1.16/aqua/` est listable (directory index). C'est là qu'on récupère `config.json`, `config/`, `state/`, `statusRelais/` et `var/backup/dump_aqua_*.zip`.
- Sondes : DS18B20 sur `/sys/bus/w1/devices/28-*/w1_slave`. Température du CPU sur `/sys/class/thermal/thermal_zone0/temp` (en dur dans `cron/temperature_rpi.php`).
- MariaDB 10.3 sur le Pi : un seul compte `root@localhost` (mysql_native_password). Son mot de passe a été changé le 2026-11-02 et n'est stocké que dans `config.json` sur le Pi (l'appli PHP, les crons et les scripts Python le lisent tous dans ce fichier). `config.json` est en 640 pi:www-data ; sauvegarde de l'ancien : `config.json.bak-20261102` (ancien mot de passe, désormais invalide).
- ⚠️ `raspberry.txt` et `config.json` contiennent des mots de passe en clair : ne jamais les recopier dans le dépôt ni dans ce fichier.

## Environnement local (Docker)

```bash
docker compose up -d --build      # démarrer
docker compose down               # arrêter
docker compose down -v            # arrêter ET supprimer la BDD (réimport du dump au prochain up)
docker exec aqua-web php app/code/cron/<script>.php   # lancer un cron à la main
```

- App : http://localhost:8080/aqua/web/ · phpMyAdmin 5.2 : http://localhost:8081 · Mailpit (mails capturés) : http://localhost:8025 · MariaDB 11.8 : `localhost:3307` (root/root, base `aqua`).
- `docker/php/Dockerfile` (contexte `docker/`) — `php:8.5-apache` (Debian Trixie) + mysqli, pdo_mysql, zip, client MariaDB, Python 3.13 + `mysql-connector-python`, Composer, mod_rewrite/headers, `AllowOverride All`, `display_errors` + `E_ALL`.
- Composer : `docker compose run --rm --no-deps -e COMPOSER_ALLOW_SUPERUSER=1 web composer <cmd>` (`vendor/` est versionné).
- Le dépôt est monté sur `/var/www/html/aqua`. `docker/config.docker.json` est monté **par-dessus** `config.json` (host `db`, `base_url` local, sondes factices, SMTP → Mailpit). Aucun `config.json` local n'est nécessaire.
- `docker/db/01-dump.sql` — dump importé automatiquement au **premier** démarrage (volume `db_data` vide). Fichier ignoré par git.
  Actuellement : `dump_aqua_20220214-00h00` (récupéré sur le Pi, un jour plus récent que `../dump_aqua_20220213-00h00.zip`).
- `docker/python/RPi/GPIO.py` — faux module `RPi.GPIO` (`PYTHONPATH`) : les scripts Python tournent vraiment (BDD, fichiers d'état, mails), seules les GPIO sont simulées. Sorties tracées dans `var/log/gpio-fake.log` ; entrées lues dans `docker/gpio/<pin BCM>` (0/1, défaut 1).
- `docker/sensors/w1_slave_eau|air` — fausses sondes 1-Wire (25,6 °C / 21,9 °C) ; il suffit de modifier `t=` pour simuler une autre température.
- Limites connues : `cron/temperature_rpi.php` lit `/sys/class/thermal/...` en dur (indisponible dans Docker) ; le débit réacteur vaut 0 (pas d'impulsions simulées).

## Sécurité (.htaccess)

- `.htaccess` à la racine : `Options -Indexes` + `Require all denied` → seul `web/` est public (protège `config.json`, `raspberry.txt`, `config/`, `state/`, `var/backup/`, `scripts/`, `vendor/`…).
- `web/.htaccess` : `Require all granted`, `Options -Indexes`, blocage de `package*.json`, `gulpfile.js`, `.sass-cache/`, `*.map|scss|lock|md|sql|zip|bak|log`.
- Nécessite `AllowOverride All` (ou au minimum `AuthConfig Options FileInfo`) sur le vhost du Pi.
- Déployé sur le Pi le 2026-11-02 (sauvegarde : `web/.htaccess.bak-20261102`). Vérifié : 403 sur tous les fichiers sensibles, 200 sur toutes les pages.

## Conventions

- PHP procédural, fonctions globales dans `app.php`, `$link` (mysqli) et `$data` (config) en variables globales.
- **SQL** : toujours passer par `dbQuery($link, $sql, $params)` / `dbFetchOne()` (requêtes préparées, `mysqli::execute_query`, PHP ≥ 8.2). Jamais de concaténation de valeurs. Seuls les noms de tables (constantes `TABLE_*`) sont concaténés. Python : `functions.execute()` / `query_one()` avec des paramètres `%s`.
- **Affichage** : échapper toute donnée venant de la BDD ou de l'utilisateur avec `e()` dans les templates. Exception volontaire : le corps HTML des mails dans `logs/logs_mail.php`.
- **Contrôleurs** : terminer par `redirect($data, 'page')` (header + exit). `runCron.php` n'accepte qu'un nom de fichier présent dans `app/code/cron/` (liste blanche). `execRelais.php` utilise un tableau bouton → script.
- Ne pas modifier le code applicatif pour l'adapter au local : passer par `docker/` (config montée, stubs).
- Pistes non traitées : l'appli n'a aucune authentification (n'importe qui sur le réseau local peut piloter les relais) → à protéger par une auth HTTP Basic sur `/aqua/web` comme pour phpMyAdmin ; pas de protection CSRF ; Twig (échappement automatique) pourrait remplacer les templates PHP.
- Test des formulaires : script `form_test.py` (soumet tous les formulaires POST et cherche les erreurs PHP) — à recréer si besoin.

## Journal des échanges

- **2026-11-02** — Création de l'environnement Docker local (web PHP 7.3 + MariaDB 10.6 + phpMyAdmin), récupération des fichiers manquants depuis le Pi (`config/`, `state/`, `statusRelais/`, dernier dump), stub Python/GPIO, fausses sondes, création de ce CLAUDE.md. Vérifié : toutes les pages répondent en 200 sans erreur PHP, lecture de la température OK, le cron osmolateur tourne.
- **2026-11-02** — Constat : sur le Pi, `http://192.168.1.16/aqua/` affiche la liste des fichiers et sert `config.json` et `raspberry.txt` (mots de passe en clair). Ajout de `.htaccess` (racine) et durcissement de `web/.htaccess` ; validé en local (403 sur les fichiers sensibles, 200 sur toutes les pages). Reste à les copier sur le Pi puis à changer le mot de passe Gmail de `raspberrypi.recifal`.
- **2026-11-02** — Accès SSH par clé au Pi (il a fallu corriger les permissions : `/home/pi` était en 777, StrictModes refusait la clé). `.htaccess` déployés en SSH et vérifiés sur le Pi. Docker passé en PHP 7.4 (version réelle du Pi). **À faire** : changer le mot de passe Gmail (exposé publiquement jusqu'ici), vérifier les sondes 1-Wire.
- **2026-11-02** — phpMyAdmin du Pi restreint par une auth HTTP Basic (Apache, `<Location /phpmyadmin>`). Vérifié : 401 sans identifiants ou avec un mauvais mot de passe, 200 avec le bon ; appli aqua non impactée. Le mot de passe n'est pas stocké dans le dépôt. Reste : le compte MariaDB root/root est faible.
- **2026-11-02** — Mot de passe root MariaDB du Pi changé (n'est plus `root`), `config.json` du Pi mis à jour. Vérifié : ancien mot de passe refusé, nouveau OK, pages web en 200 sans erreur BDD, cron `timer.php` OK, connexion Python `mysql.connector` OK, crons en tâche de fond toujours actifs. Le Docker local garde root/root (indépendant).
- **2026-11-02** — Montée de version dans Docker : PHP 8.5, MariaDB 11.8, phpMyAdmin 5.2, Python 3.13 (+ Mailpit). Swiftmailer → symfony/mailer ; phelium/mysql-backup → mariadb-dump + ZipArchive ; scripts Python 2 → 3 (UTF-8, chemins relatifs) ; `python` → `python3` dans le PHP ; `size(?array)` ; `mysqli_report(OFF)`. Validé : 9 pages + contrôleurs AJAX sans erreur ni avertissement (E_ALL), tous les crons PHP, tous les scripts Python (vraie BDD + faux GPIO), mails PHP et Python reçus dans Mailpit, dump zip OK, soumission de tous les formulaires OK. **Pi pas encore migré** (Buster 32 bits ne propose pas PHP 8.5 → changement d'OS nécessaire).
- **2026-11-02** — Amélioration du code (carte blanche) : requêtes préparées partout où des données entrent en SQL (PHP `dbQuery`, Python `%s`) ; `runCron.php` vulnérable à une injection de commande (`exec("php " . $_POST['file'])`) → liste blanche ; échappement XSS (`e()`) dans les templates ; contrôleurs simplifiés (`execRelais`, `saveStatus`, `saveTimer`, `save`) avec `redirect()` + exit ; suppression des appels à `scripts/osmolateur/on|off.py` (inexistants) ; bug Python `force_log` (str == int) corrigé ; `functions.py` refactoré (connexion fermée proprement, plus de boucle mail ↔ getstatus) ; validation de la date des dépenses ; `getStateRelais` sans retour corrigé. `node_modules/`, `web/node_modules/` et `.sass-cache/` retirés de git (≈ 14 000 fichiers, toujours sur le disque). Tout est revalidé dans Docker (pages, formulaires, crons, scripts Python, tentatives d'injection et de XSS bloquées).
