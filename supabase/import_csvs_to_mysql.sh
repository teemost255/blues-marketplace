#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
COMPOSE_FILE="$ROOT_DIR/../backend/docker-compose.yml"
DB_CONTAINER=db
DB_NAME=laravel
DB_USER=root
DB_PASS=secret

if [[ ! -f "$COMPOSE_FILE" ]]; then
  echo "Cannot find docker-compose file at $COMPOSE_FILE"
  exit 1
fi

TABLES=(
  users
  profiles
  user_roles
  listings
  purchases
  listing_categories
  wallets
  wallet_transactions
  wishlists
  notifications
  support_tickets
  site_settings
  admin_audit_log
  activity_log
  admins_users
)

for table in "${TABLES[@]}"; do
  csv="$ROOT_DIR/csv-templates/${table}.csv"
  if [[ ! -f "$csv" ]]; then
    echo "Skipping $table: no CSV file found."
    continue
  fi

  line_count=$(wc -l < "$csv" | tr -d '[:space:]')
  if [[ $line_count -le 1 ]]; then
    echo "Skipping $table: CSV contains only headers or is empty."
    continue
  fi

  echo "Importing $table from $csv..."
  docker compose -f "$COMPOSE_FILE" exec -T "$DB_CONTAINER" bash -lc "cat > /tmp/${table}.csv" < "$csv"
  docker compose -f "$COMPOSE_FILE" exec -T "$DB_CONTAINER" bash -lc "cat > /tmp/${table}.sql <<'EOF'
SET FOREIGN_KEY_CHECKS=0;
LOAD DATA INFILE '/tmp/${table}.csv' INTO TABLE ${table}
FIELDS TERMINATED BY ','
ENCLOSED BY '\"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES;
SET FOREIGN_KEY_CHECKS=1;
EOF
mysql --local-infile=1 -u${DB_USER} -p${DB_PASS} ${DB_NAME} < /tmp/${table}.sql"
  echo "Imported $table."
done

echo "CSV import complete."
