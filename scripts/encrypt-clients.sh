#!/usr/bin/env bash
# ──────────────────────────────────────────────────────────────
# encrypt-clients.sh — Chiffre les données clients existantes
# Usage :
#   ./scripts/encrypt-clients.sh          → dev + prod
#   ./scripts/encrypt-clients.sh dev      → dev seulement
#   ./scripts/encrypt-clients.sh prod     → prod seulement
# ──────────────────────────────────────────────────────────────

set -euo pipefail

PROD_HOST="ssh.c9nzzvn7q.service.one"
PROD_USER="c9nzzvn7q_ssh"
PROD_PORT="22"
PROD_PATH="/customers/b/8/f/c9nzzvn7q/webroots/a44183bb/laravel_app"

TARGET="${1:-both}"

run_dev() {
    echo ""
    echo "▶ [DEV] Chiffrement en local…"
    php artisan clients:encrypt-existing
    echo "✓ [DEV] Terminé"
}

run_prod() {
    echo ""
    echo "▶ [PROD] Connexion SSH → ${PROD_HOST}…"
    ssh -p "${PROD_PORT}" "${PROD_USER}@${PROD_HOST}" \
        "cd ${PROD_PATH} && php artisan clients:encrypt-existing"
    echo "✓ [PROD] Terminé"
}

case "${TARGET}" in
    dev)   run_dev  ;;
    prod)  run_prod ;;
    both)  run_dev; run_prod ;;
    *)
        echo "Usage : $0 [dev|prod|both]"
        exit 1
        ;;
esac
