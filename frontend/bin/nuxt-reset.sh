#!/usr/bin/env bash
# Recover from Docker/WSL Nuxt blank-page state (duplicate router plugins / bloated .nuxt).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CONTAINER="${NUXT_CONTAINER:-airconcierge-nuxt}"

echo "Stopping ${CONTAINER}..."
docker stop "${CONTAINER}" >/dev/null

echo "Clearing .nuxt / .output..."
docker run --rm -u root -v "${ROOT}:/app" node:22-bookworm \
  bash -lc 'rm -rf /app/.nuxt /app/.output; mkdir -p /app/.nuxt; chown -R 1000:1000 /app/.nuxt || true'

echo "Starting ${CONTAINER}..."
docker start "${CONTAINER}" >/dev/null

echo "Waiting for single router plugin..."
for i in $(seq 1 45); do
  code=$(curl -s -o /dev/null -w '%{http_code}' http://localhost:3000/ || echo 000)
  plugins=$(curl -s "http://localhost:3000/_nuxt/@id/virtual:nuxt:/app/.nuxt/plugins.client.mjs" 2>/dev/null || true)
  routers=$(printf '%s' "$plugins" | grep -c "pages/runtime/plugins/router" || true)
  if [ "$code" = "200" ] && [ "${routers:-0}" = "1" ] && [ -n "$plugins" ]; then
    echo "Healthy after ${i}s (HTTP ${code}, routers=${routers})"
    exit 0
  fi
  echo "t=${i}s code=${code} routers=${routers:-0}"
  sleep 2
done

echo "Nuxt did not become healthy in time." >&2
exit 1
