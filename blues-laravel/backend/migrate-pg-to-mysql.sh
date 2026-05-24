#!/bin/bash
# =============================================================================
# Blues Marketplace — PostgreSQL to MySQL Migration Script
# =============================================================================
# Exports every table from the Replit PostgreSQL database and imports into MySQL.
# Prerequisites:
#   - MySQL must be running (see README.md Step 2)
#   - The blues_marketplace database must exist (see README.md Step 3)
# =============================================================================

set -e

MYSQL_SOCKET=/tmp/mysql.sock
MYSQL_USER=blues
MYSQL_PASS=blues_secret
MYSQL_DB=blues_marketplace

PG_HOST="${PGHOST:-helium}"
PG_PORT="${PGPORT:-5432}"
PG_DB="${PGDATABASE:-heliumdb}"
PG_USER="${PGUSER:-postgres}"
PG_PASS="${PGPASSWORD:-password}"

EXPORT_DIR=/tmp/blues_pg_export
mkdir -p "$EXPORT_DIR"

echo ""
echo "=========================================="
echo "  Blues Marketplace: PG → MySQL Migration"
echo "=========================================="
echo ""
echo "PostgreSQL: $PG_USER@$PG_HOST:$PG_PORT/$PG_DB"
echo "MySQL:      $MYSQL_USER@localhost/$MYSQL_DB"
echo ""

# Tables to export (in dependency order)
TABLES=(
    "listing_categories"
    "users"
    "profiles"
    "wallets"
    "admins_users"
    "listings"
    "purchases"
    "wallet_transactions"
    "wishlist"
    "support_tickets"
    "notifications"
    "virtual_number_orders"
    "settings"
    "announcements"
    "password_resets"
    "sessions"
    "migrations"
)

PGPASSWORD="$PG_PASS" psql -h "$PG_HOST" -p "$PG_PORT" -U "$PG_USER" -d "$PG_DB" \
    -c "\COPY migrations TO '$EXPORT_DIR/migrations.csv' WITH (FORMAT csv, HEADER true);" 2>/dev/null || true

echo "Step 1: Running Laravel migrations on MySQL to create tables..."
cd "$(dirname "$0")/../.."
php artisan migrate --force 2>&1 | tail -10
echo ""

echo "Step 2: Exporting PostgreSQL tables..."
for TABLE in "${TABLES[@]}"; do
    echo -n "  Exporting $TABLE ... "
    PGPASSWORD="$PG_PASS" psql -h "$PG_HOST" -p "$PG_PORT" -U "$PG_USER" -d "$PG_DB" \
        -c "\COPY $TABLE TO '$EXPORT_DIR/$TABLE.csv' WITH (FORMAT csv, HEADER true);" 2>/dev/null \
        && echo "OK" || echo "SKIPPED (table may not exist)"
done

echo ""
echo "Step 3: Importing into MySQL..."
for TABLE in "${TABLES[@]}"; do
    CSV_FILE="$EXPORT_DIR/$TABLE.csv"
    if [ ! -f "$CSV_FILE" ]; then
        echo "  Skipping $TABLE (no export file)"
        continue
    fi

    echo -n "  Importing $TABLE ... "
    mysql --socket="$MYSQL_SOCKET" -u "$MYSQL_USER" -p"$MYSQL_PASS" "$MYSQL_DB" \
        --local-infile=1 \
        -e "SET FOREIGN_KEY_CHECKS=0; LOAD DATA LOCAL INFILE '$CSV_FILE' INTO TABLE \`$TABLE\` FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 ROWS; SET FOREIGN_KEY_CHECKS=1;" 2>/dev/null \
        && echo "OK" || echo "FAILED"
done

echo ""
echo "Step 4: Updating .env to use MySQL..."
cd blues-laravel 2>/dev/null || true
ENV_FILE=".env"
if [ -f "$ENV_FILE" ]; then
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' "$ENV_FILE"
    sed -i 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' "$ENV_FILE"
    sed -i 's/^DB_PORT=.*/DB_PORT=3306/' "$ENV_FILE"
    sed -i 's/^DB_DATABASE=.*/DB_DATABASE=blues_marketplace/' "$ENV_FILE"
    sed -i 's/^DB_USERNAME=.*/DB_USERNAME=blues/' "$ENV_FILE"
    sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=blues_secret/' "$ENV_FILE"
    echo "  .env updated to MySQL"
else
    echo "  .env not found, please update manually"
fi

echo ""
echo "Step 5: Clearing Laravel cache..."
php artisan config:clear 2>/dev/null
php artisan cache:clear  2>/dev/null

echo ""
echo "=========================================="
echo "  Migration complete!"
echo "  Restart the app: php artisan serve --host=0.0.0.0 --port=5000"
echo ""
echo "  IMPORTANT: MySQL runs in /tmp/mysql-data which resets on"
echo "  Replit session restart. Re-run this script after each restart"
echo "  or use Replit PostgreSQL for persistent storage."
echo "=========================================="
