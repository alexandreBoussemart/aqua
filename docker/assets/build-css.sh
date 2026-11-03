#!/bin/sh
# Compile assets/src/scss/custom.scss -> public/build/css/custom.css + custom.min.css
#         assets/src/scss/reef.scss   -> public/build/css/reef.min.css (thème récif, chargé après custom)
# (Dart Sass + Autoprefixer, remplace l'ancienne chaîne gulp 3 / Ruby Sass)
# Usage : docker compose run --rm assets            (compilation unique)
#         docker compose run --rm assets --watch    (recompile à chaque modification)
set -e
cd /app
SRC=assets/src/scss/custom.scss
OUT=public/build/css
SASS="npx --yes sass@1.83.4"
PREFIX="npx --yes -p postcss-cli@11.0.0 -p autoprefixer@10.4.20 postcss --use autoprefixer --no-map -r"

build() {
    $SASS --no-source-map --style=expanded "$SRC" "$OUT/custom.css"
    $SASS --no-source-map --style=compressed "$SRC" "$OUT/custom.min.css"
    $SASS --no-source-map --style=compressed assets/src/scss/reef.scss "$OUT/reef.min.css"
    $PREFIX "$OUT/custom.css" "$OUT/custom.min.css" "$OUT/reef.min.css"
    echo "$(date '+%H:%M:%S') CSS compilé : $OUT/custom.css, $OUT/custom.min.css, $OUT/reef.min.css (thème récif)"
}

build
if [ "$1" = "--watch" ]; then
    echo "Surveillance de assets/src/scss (Ctrl+C pour arrêter)…"
    last=""
    while true; do
        sum=$(find assets/src/scss -name '*.scss' -exec md5sum {} + | md5sum)
        if [ -n "$last" ] && [ "$sum" != "$last" ]; then build || true; fi
        last=$sum
        sleep 1
    done
fi
