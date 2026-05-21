# Admin Authentication System Implementation

## Overview

This document outlines the implementation of a separate admin authentication system that:
- Creates a dedicated `admins_users` table for admin credentials
- Migrates existing admin users out of the regular `auth.users` table
- Implements strict access control between `/login` (regular users) and `/admin/login` (admin users)
- Blocks admins from using regular user login
- Handles both traditional admins (legacy) and new admin authentication

## Database Changes

### 1. New `admins_users` Table
**Location**: `supabase/migrations/20260521020000_create_admins_users_table.sql`

The new table includes:
- `id` (UUID, Primary Key)
- `email` (TEXT, UNIQUE, NOT NULL)
- `password_hash` (TEXT, NOT NULL) - bcrypt hashed passwords
- `display_name` (TEXT)
- `avatar_url` (TEXT)
- `is_active` (BOOLEAN, DEFAULT true)
- `last_login` (TIMESTAMPTZ)
- `created_at` / `updated_at` (Audit timestamps)

**Security Features**:
- Row Level Security (RLS) enabled
- Policies restrict access to service_role only
- Indexes on email and is_active for performance
- Auto-update trigger for `updated_at`

### 2. Helper Functions
Created PostgreSQL functions:
- `verify_admin_password(email, password)` - Authenticates admin credentials
- `is_admin_email(email)` - Checks if an email is registered as admin
- Both functions are executable by anon/authenticated roles for client-side checks

### 3. Admin Migration
**Location**: `supabase/migrations/20260521020001_migrate_admin_users.sql`

Migrates existing admins:
- Extracts admins from `auth.users` (identified via `user_roles` where role='admin')
- Inserts into `admins_users` with bcrypt-hashed passwords
- Removes `admin` role from `user_roles` table
- Creates index on `user_roles(user_id, role)` for faster lookups

## Authentication Flow

### Regular User Login Flow (`/login`)
1. User enters email/password
2. System checks if email exists in `admins_users` via `is_admin_email()`
3. If admin email: **ERROR** - "Admin accounts cannot use this login"
4. If regular email: Standard Supabase password authentication
5. Success: User redirected to `/dashboard`

### Admin Login Flow (`/admin/login`)
1. Admin enters email/password
2. System calls `authenticateAdmin()` which:
   - Queries `verify_admin_password()` RPC function
   - Validates credentials using bcrypt comparison
3. On success: Admin session stored in localStorage
4. Admin redirected to `/admin` dashboard

## Code Changes

### 1. New Files Created

#### `src/lib/admin-auth.ts`
Core admin authentication utilities:
- `authenticateAdmin()` - Verify admin credentials
- `checkIsAdminEmail()` - Check if email is admin
- `storeAdminSession()` - Persist admin session
- `getAdminSession()` - Retrieve admin session
- `clearAdminSession()` - Clear admin session
- Type definitions: `AdminCredentials`, `AdminSession`, `AdminLoginResponse`

### 2. Modified Files

#### `src/routes/login.tsx`
**Changes**:
- Added admin authentication imports
- Updated `handleSubmit()` to:
  - Check if admin login (`adminOnly` flag)
  - Use `authenticateAdmin()` for admin logins
  - Use `checkIsAdminEmail()` to block admins from regular login
  - Show appropriate error messages

#### `src/lib/admin-guard.tsx`
**Changes**:
- Updated `AdminGuard` to check both:
  - Traditional admin role (from `user_roles`)
  - New admin session (from `admins_users`)
- Updated `AdminLoginGuard` to recognize existing admins
- Added `useAdminPermissions()` hook with:
  - `signOutAdmin()` - Clear admin session and redirect
  - `isAdmin` - Check admin status (either type)
  - Fallback to moderator checks for legacy systems

#### `src/routes/admin.tsx`
**Changes**:
- Import `useAdminPermissions` and `getAdminSession`
- Updated `AdminLayout()` to:
  - Detect new vs traditional admin sessions
  - Use appropriate sign-out handler
  - Display correct admin email/info for both auth types

## Security Considerations

### 1. Password Storage
- Passwords are bcrypt-hashed using PostgreSQL's `pgcrypto` extension
- Original admin passwords hashed on migration (set to email as temporary)
- **Action Required**: Admins should reset passwords after migration

### 2. Session Management
- Admin sessions stored in localStorage with session object
- Automatically cleared on sign-out
- Checked on admin routes via `AdminGuard`
- Can be enhanced with expiration tokens

### 3. Access Control
- Admins cannot access `/login` endpoint
- Admins can only access `/admin/login`
- Regular users cannot authenticate against `admins_users` table
- RLS policies enforce database-level security

### 4. Migration Considerations
- Existing admin records preserved in `auth.users` for reference
- Admin role removed from `user_roles` to separate auth systems
- Migration is idempotent (uses `ON CONFLICT DO NOTHING`)

## Testing Checklist

- [ ] Run migrations: `supabase db push`
- [ ] Verify `admins_users` table created correctly
- [ ] Verify admin users migrated from `auth.users`
- [ ] Test regular user login (should work)
- [ ] Test admin email on regular login (should fail with specific error)
- [ ] Test admin login with correct credentials (should work)
- [ ] Test admin login with wrong credentials (should fail)
- [ ] Test regular user email on admin login (should fail)
- [ ] Test admin sign-out and session clearing
- [ ] Verify admin can access `/admin` routes
- [ ] Verify regular users cannot access admin routes

## Database Recovery/Cleanup

If needed to revert:
```sql
-- Add admins back to user_roles (if needed)
INSERT INTO public.user_roles (user_id, role)
SELECT (SELECT id FROM auth.users WHERE email = au.email), 'admin'
FROM public.admins_users au
WHERE NOT EXISTS (
  SELECT 1 FROM public.user_roles 
  WHERE user_id = (SELECT id FROM auth.users WHERE email = au.email)
  AND role = 'admin'
);

-- Drop admins_users table
DROP TABLE IF EXISTS public.admins_users CASCADE;
```

## Future Enhancements

1. Add session expiration with JWT tokens
2. Implement session audit logging
3. Add 2FA for admin accounts
4. Create admin password reset flow
5. Add admin activity logging
6. Implement role-based permissions per admin
7. Add super-admin vs regular admin distinction

## Environment Variables

No new environment variables required. Uses existing Supabase configuration.

## Deployment Notes

1. Run migrations in order:
   - `20260521020000_create_admins_users_table.sql`
   - `20260521020001_migrate_admin_users.sql`
2. Deploy updated frontend code
3. Admins should reset passwords via recovery email or manual reset
4. Monitor admin logs for authentication attempts

## Support Contact

For issues with admin authentication, check:
1. Admin session in browser localStorage
2. Supabase migration logs
3. RLS policies on `admins_users` table
4. `verify_admin_password` function execution permissions
