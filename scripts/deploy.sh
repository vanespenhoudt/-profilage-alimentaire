#!/bin/bash
# Deploy : tests → commit → push dev → push main
# Usage : bash scripts/deploy.sh "message de commit"
set -e

MSG="${1:-deploy}"

echo "==> Tests..."
php artisan test --stop-on-failure

echo "==> Commit..."
git add -A
git commit -m "$MSG

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>" 2>/dev/null || echo "    (rien à committer)"

echo "==> Push dev..."
git push origin dev

echo "==> Push main..."
git checkout main
git merge dev --no-edit
git push origin main
git checkout dev

echo ""
echo "Déployé — dev + main."
