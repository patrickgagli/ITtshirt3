#!/usr/bin/env bash
set -euo pipefail

hot_file="public/hot"

# If no Vite hot file exists, there is nothing to guard.
if [[ ! -f "$hot_file" ]]; then
  exit 0
fi

hot_url="$(cat "$hot_file" 2>/dev/null || true)"
if [[ -z "$hot_url" ]]; then
  rm -f "$hot_file"
  echo "[vite-guard] Removed empty $hot_file"
  exit 0
fi

# Extract host:port from URLs like http://127.0.0.1:5173 or http://[::1]:5173.
host_port="${hot_url#http://}"
host_port="${host_port#https://}"
host_port="${host_port%%/*}"

# Strip brackets around IPv6 host if present.
host_port="${host_port#[}"
host_port="${host_port%]}"

host="${host_port%:*}"
port="${host_port##*:}"

# Validate parsed host/port. If invalid, remove stale marker.
if [[ -z "$host" || -z "$port" || "$host" == "$port" ]]; then
  rm -f "$hot_file"
  echo "[vite-guard] Removed malformed $hot_file ($hot_url)"
  exit 0
fi

# Check if the Vite endpoint is reachable with a short timeout.
if ! curl -fsS --max-time 1 "$hot_url/@vite/client" >/dev/null 2>&1; then
  rm -f "$hot_file"
  echo "[vite-guard] Removed stale $hot_file ($hot_url not reachable)"
fi
