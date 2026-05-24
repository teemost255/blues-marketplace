#!/bin/bash
# Start Adminer on port 8080
# Supports PostgreSQL (current), MySQL (after migration), and SQLite

echo "==================================="
echo "  Blues Marketplace — DB Admin"
echo "  Adminer running on port 8080"
echo "==================================="
echo ""
echo "Open: https://\$REPL_SLUG.\$REPL_OWNER.repl.co:8080"
echo "  OR click the port 8080 tab in Replit"
echo ""
echo "PostgreSQL connection:"
echo "  Driver:   PostgreSQL"
echo "  Server:   \$PGHOST:\$PGPORT (from env)"
echo "  Database: \$PGDATABASE"
echo "  Username: \$PGUSER"
echo ""

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
php -S 0.0.0.0:8080 "$SCRIPT_DIR/index.php"
