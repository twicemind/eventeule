#!/usr/bin/env bash
set -euo pipefail

PLUGIN_SLUG="eventeule"
BASE_URL="http://localhost:8888"

echo "== Checking WordPress CLI =="
npm run wp:cli -- core is-installed

echo "== Checking Plugin Status =="
npm run wp:cli -- plugin is-active "$PLUGIN_SLUG"

echo "== Checking REST Endpoint =="
REST_RESPONSE=$(curl -s "$BASE_URL/wp-json/eventeule/v1/ping")
echo "$REST_RESPONSE"

echo "$REST_RESPONSE" | grep '"success":true' >/dev/null || {
  echo "REST endpoint does not return success=true"
  exit 1
}

echo "== Checking Admin Page =="
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/wp-admin/")
if [ "$HTTP_STATUS" -ne 302 ] && [ "$HTTP_STATUS" -ne 200 ]; then
  echo "Unexpected HTTP status for /wp-admin/: $HTTP_STATUS"
  exit 1
fi

echo "== Smoke test successful =="