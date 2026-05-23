# BluesMarketplace

A digital accounts marketplace where users can buy verified Facebook, Instagram, TikTok accounts, and 2nd numbers. Features a wallet system, Paystack checkout, admin panel, user dashboards, and support tickets.

## Tech Stack

- **Frontend + SSR**: React 19 + TanStack Start (TanStack Router, file-based routing)
- **Styling**: Tailwind CSS v4 + Radix UI components (shadcn/ui style)
- **Database**: Supabase PostgreSQL (via `@supabase/supabase-js`)
- **Auth**: Supabase Auth (email/password + Google OAuth)
- **Admin Auth**: Custom `admins_users` table in Supabase with bcrypt password hashing
- **Dev server**: Vite on port 5000

## Project Structure

- `src/routes/` — File-based routes (TanStack Router)
- `src/components/` — Shared UI components
- `src/lib/` — Auth context, admin auth, utilities
- `src/integrations/supabase/` — Supabase client (browser + server)
- `supabase/migrations/` — Database schema migrations
- `blues-laravel/` — Legacy Laravel backend (not used by the React app)

## Environment Variables

Already configured in Replit shared environment:
- `SUPABASE_URL` — Supabase project URL
- `SUPABASE_PUBLISHABLE_KEY` — Supabase anon key (safe for browser)
- `VITE_SUPABASE_URL` — Same URL exposed to Vite client
- `VITE_SUPABASE_PUBLISHABLE_KEY` — Same anon key exposed to Vite client

Server-side only (set as secrets):
- `SUPABASE_SERVICE_ROLE_KEY` — Service role key for admin operations (optional, used by `/api/admin/sync-auth`)

## Running

```bash
npx vite dev
```

Runs on port 5000.

## User Preferences

- Keep Supabase for auth and database (deeply integrated)
- Admin panel at `/admin` uses a separate `admins_users` table for authentication
- Nigerian Naira (₦) as currency
- Dark mode by default
