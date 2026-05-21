# Admin Authentication - Quick Start Guide

## What Was Implemented

A complete separate admin authentication system that:
✅ Creates `admins_users` table for admin-only credentials
✅ Migrates existing admins from `auth.users` 
✅ Blocks admins from regular login (`/login`)
✅ Provides dedicated admin login (`/admin/login`)
✅ Implements proper session management for admins

## Files Created

### Database Migrations
```
supabase/migrations/
├── 20260521020000_create_admins_users_table.sql    # Create admins_users table & functions
└── 20260521020001_migrate_admin_users.sql          # Migrate existing admins
```

### Source Code
```
src/
├── lib/
│   ├── admin-auth.ts                  # Admin authentication utilities (NEW)
│   └── admin-guard.tsx                # Updated with admin session checks
├── routes/
│   ├── login.tsx                      # Updated to handle both auth types
│   └── admin.tsx                      # Updated admin layout for new auth
└── ADMIN_AUTH_IMPLEMENTATION.md       # Full documentation
```

## Key Functions

### Admin Authentication (`src/lib/admin-auth.ts`)

```typescript
// Authenticate admin credentials
authenticateAdmin({ email, password })  // Returns AdminSession or error

// Check if email is admin
checkIsAdminEmail(email)  // Returns boolean

// Session management
storeAdminSession(admin)      // Save to localStorage
getAdminSession()             // Retrieve from localStorage
clearAdminSession()           // Clear on logout
```

### Updated Guards (`src/lib/admin-guard.tsx`)

```typescript
// Guard component - protects admin routes
<AdminGuard>...</AdminGuard>

// Guard component - protects admin login route
<AdminLoginGuard>...</AdminLoginGuard>

// Hook for admin permissions
useAdminPermissions() // Returns { isAdmin, signOutAdmin, ... }
```

## Access Control Flow

### Regular User Login (`/login`)
```
Email + Password
    ↓
Is email in admins_users? 
    ↓ YES → ERROR: "Use admin login"
    ↓ NO → Authenticate with Supabase
         → Success → /dashboard
```

### Admin Login (`/admin/login`)
```
Email + Password
    ↓
Call verify_admin_password() RPC
    ↓ Valid → Store admin session
         → /admin dashboard
    ↓ Invalid → ERROR message
```

## Deployment Steps

1. **Run migrations** (in order):
   ```bash
   supabase migration up
   ```
   - Creates `admins_users` table
   - Migrates existing admin users
   - Removes admin role from `user_roles`

2. **Deploy frontend code** with the updated components

3. **Admin password reset** (important!):
   - Migration sets temp password = email
   - Admins should receive password reset emails
   - Or manually reset via admin panel (if available)

## Testing

### Test Regular User Login
```
1. Go to /login
2. Enter regular user email + password
3. Expected: Login successful → /dashboard
```

### Test Admin Email on Regular Login
```
1. Go to /login
2. Enter admin email + password
3. Expected: Error → "Use admin login page"
```

### Test Admin Login
```
1. Go to /admin/login
2. Enter admin email + password
3. Expected: Login successful → /admin dashboard
```

### Test Regular Email on Admin Login
```
1. Go to /admin/login
2. Enter regular user email + password
3. Expected: Error → "Authentication failed"
```

## Database Queries Reference

### Check if admin exists
```sql
SELECT EXISTS (
  SELECT 1 FROM public.admins_users 
  WHERE lower(email) = 'admin@example.com'
  AND is_active = true
);
```

### View all admins
```sql
SELECT id, email, display_name, is_active, created_at 
FROM public.admins_users;
```

### Update admin password
```sql
UPDATE public.admins_users 
SET password_hash = crypt('new_password', gen_salt('bf', 4)),
    updated_at = now()
WHERE id = 'admin-id';
```

### Deactivate admin
```sql
UPDATE public.admins_users 
SET is_active = false
WHERE email = 'admin@example.com';
```

## Troubleshooting

### Admin can't login
- [ ] Check if admin email exists in `admins_users`
- [ ] Verify password is correct (check if temp password still set)
- [ ] Check `is_active = true`
- [ ] Verify `verify_admin_password` function works:
  ```sql
  SELECT * FROM public.verify_admin_password('admin@example.com', 'password');
  ```

### Regular user sees "Use admin login page" error
- [ ] Check if email was mistakenly in `admins_users`
- [ ] Verify `is_admin_email()` RPC returns correct value
- [ ] Clear browser cache/localStorage

### Admin session not persisting
- [ ] Check browser localStorage for `admin_session` key
- [ ] Verify localStorage quota not exceeded
- [ ] Check browser console for errors
- [ ] Try incognito/private mode

## Architecture Notes

### Dual Authentication Support
The system supports both:
1. **Legacy admins**: Via Supabase auth + `user_roles` table (role='admin')
2. **New admins**: Via `admins_users` table with localStorage sessions

This allows gradual migration without breaking existing admin accounts.

### Why Separate Tables?
- ✅ Cleaner separation of concerns
- ✅ Different authentication mechanisms (password vs OAuth)
- ✅ Better security (isolated credentials)
- ✅ Easier password management for admins
- ✅ Supports future 2FA for admins only

### Why localStorage for Sessions?
- Simpler implementation without additional database tables
- Can be enhanced with JWT expiration
- Matches pattern used for other client-side state
- Easy to clear on logout

## Next Steps

1. Deploy migrations to production database
2. Test both authentication flows
3. Send admin password reset emails
4. Monitor admin login attempts
5. Consider adding:
   - Admin session audit logging
   - JWT tokens with expiration
   - 2FA support
   - Admin activity tracking

## Support

For issues or questions:
1. Check `ADMIN_AUTH_IMPLEMENTATION.md` for detailed docs
2. Review test checklist in implementation doc
3. Check database logs for migration status
4. Verify RLS policies on `admins_users` table

---
**Last Updated**: 2026-05-21
**Implementation Version**: 1.0
