#!/usr/bin/env bash
set -e

npm run wp:start

npm run wp:cli -- plugin activate eventeule

npm run wp:cli -- option update blogname "EventEule Local"

npm run wp:cli -- post create \
  --post_type=page \
  --post_status=publish \
  --post_title="EventEule Test" \
  --post_content="[eventeule_events]"