#!/usr/bin/env bash
#
# Fails the deploy unless the application is genuinely serving traffic and
# reporting the exact release we just deployed.
#
# Usage: health-gate.sh <base-url> <expected-release> [timeout-seconds]

set -euo pipefail

BASE_URL="${1:?base url required}"
EXPECTED_RELEASE="${2:?expected release required}"
TIMEOUT="${3:-90}"

deadline=$(( $(date +%s) + TIMEOUT ))
attempt=0

echo "Health gate: ${BASE_URL}/health must report release ${EXPECTED_RELEASE} within ${TIMEOUT}s"

while [ "$(date +%s)" -lt "$deadline" ]; do
    attempt=$((attempt + 1))

    if body=$(curl -fsS --max-time 5 "${BASE_URL}/health" 2>/dev/null); then
        status=$(printf '%s' "$body" | jq -r '.status')
        release=$(printf '%s' "$body" | jq -r '.release')

        if [ "$status" = "ok" ] && [ "$release" = "$EXPECTED_RELEASE" ]; then
            echo "Healthy after ${attempt} attempt(s): ${body}"
            exit 0
        fi

        echo "Attempt ${attempt}: status=${status} release=${release} (want ok/${EXPECTED_RELEASE})"
    else
        echo "Attempt ${attempt}: no response yet"
    fi

    sleep 3
done

echo "Health gate FAILED after ${TIMEOUT}s. Last response: ${body:-<none>}"
echo "The deploy is not considered successful. Roll back."
exit 1
