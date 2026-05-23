# Supabase -> MySQL CSV Import Templates

These files are CSV templates for the Supabase tables used by the frontend app.

## Recommended path

The best recommended workflow is:

1. Export Supabase data from the Supabase project using Studio (table browser) or SQL editor.
2. Save each table as a CSV with the exact header names in these templates.
3. Use Adminer at `http://localhost:8080` to import each CSV into MySQL.

This is the recommended approach because Adminer provides a quick GUI import and the backend is already configured to use MySQL via Docker.

## Exporting from Supabase

If you can access the Supabase project, export rows from these tables:

- `listings`
- `profiles`
- `user_roles`
- `purchases`
- `listing_categories`
- `wallets`
- `wallet_transactions`
- `wishlists`
- `notifications`
- `support_tickets`
- `site_settings`
- `admin_audit_log`
- `activity_log`
- `admins_users`
- `auth.users` (if you want to preserve user login data for Laravel)

Use the Supabase Table Editor or SQL editor with a query like:

```sql
select * from public.listings;
```

Then export the results as CSV.

## Importing into MySQL via Adminer

Open Adminer at `http://localhost:8080` and connect with:

- System: `MySQL`
- Server: `db`
- Username: `laravel`
- Password: `secret`
- Database: `laravel`

### Recommended steps

1. Run Laravel migrations in the backend if the tables do not already exist:

```bash
docker compose -f backend/docker-compose.yml exec app bash -lc "cd /var/www/html && php artisan migrate --force"
```

2. In Adminer, select the target table.
3. Click `Import`.
4. Choose the exported CSV file.
5. Set `Fields terminated by` to `,` and leave `Columns enclosed with` empty.
6. Check `First line contains column names`.

### Automated import alternative

If you want to avoid the Adminer UI, you can import all CSVs from this folder directly into MySQL using the helper script:

```bash
cd supabase
./import_csvs_to_mysql.sh
```

The script imports any non-empty CSVs from `supabase/csv-templates/` into the matching MySQL tables.

The Adminer workflow is still the best visual option, but this script automates the same import process when you have the exported CSVs ready.

## Template files

- `users.csv`
- `profiles.csv`
- `user_roles.csv`
- `listings.csv`
- `purchases.csv`
- `listing_categories.csv`
- `wallets.csv`
- `wallet_transactions.csv`
- `wishlists.csv`
- `notifications.csv`
- `support_tickets.csv`
- `site_settings.csv`
- `admin_audit_log.csv`
- `activity_log.csv`
- `admins_users.csv`

## Notes

- The Supabase `auth.users` table is separate from Laravel `users`; if you want authentication parity, export and map those fields into `users.csv`.
- Keep UUID values as plain text.
- For JSON fields like `meta`, use valid JSON text in the CSV cell.
