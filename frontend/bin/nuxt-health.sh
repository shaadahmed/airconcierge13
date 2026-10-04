#!/usr/bin/env bash
# Check Nuxt blank-page health; auto-reset when unhealthy.
# Healthy = HTTP 200 + exactly one router plugin + non-empty plugins.client.mjs
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
RESET="${ROOT}/bin/nuxt-reset.sh"
AUTO_RESET="${NUXT_AUTO_RESET:-1}"
URL="${NUXT_HEALTH_URL:-http://localhost:3000/}"
PLUGINS_URL="${NUXT_PLUGINS_URL:-http://localhost:3000/_nuxt/@id/virtual:nuxt:/app/.nuxt/plugins.client.mjs}"

probe() {
  local code plugins routers empty
  code=$(curl -s -m 8 -o /dev/null -w '%{http_code}' "${URL}" || echo 000)
  plugins=$(curl -s -m 8 "${PLUGINS_URL}" 2>/dev/null || true)
  routers=$(printf '%s' "${plugins}" | grep -c "pages/runtime/plugins/router" || true)
  empty=0
  if [ -z "${plugins}" ] || printf '%s' "${plugins}" | grep -q '^export default \[\]'; then
    empty=1
  fi

  printf 'http=%s routers=%s empty=%s\n' "${code}" "${routers:-0}" "${empty}"

  [ "${code}" = "200" ] && [ "${routers:-0}" = "1" ] && [ "${empty}" = "0" ]
}

if probe; then
  echo "Nuxt healthy."
  exit 0
fi

echo "Nuxt unhealthy (blank-page risk)." >&2

if [ "${AUTO_RESET}" != "1" ]; then
  echo "Set NUXT_AUTO_RESET=1 or run: ${RESET}" >&2
  exit 1
fi

echo "Auto-resetting via nuxt-reset.sh..."
bash "${RESET}"
probe
echo "Nuxt healthy after reset."
