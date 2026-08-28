# PHP CMS (SQLite Edition)

A lightweight, self-contained content management system built in plain PHP with a SQLite database. No framework, no Composer dependencies — just PHP's built-in `PDO` and `sqlite3` extensions.

## Features

- **Posts** — title, slug, excerpt, HTML content, featured image, category, draft/published status
- **Pages** — static pages (About, Contact, etc.) shown in the site nav when published
- **Categories** — organize posts, with a public category archive page
- **Media Library** — upload images/PDFs, reuse URLs across posts and pages
- **Users & Roles** — `admin` (full access, including user management) and `editor` (posts, pages, categories, media)
- **Auth** — password hashing (`password_hash`/`password_verify`), CSRF tokens on every form, session regeneration on login
- **Public front-end** — home feed with pagination, single post/page views, category archives

## Requirements

- PHP 8.0+
- `pdo_sqlite` extension enabled (bundled with most PHP installs)
- Apache with `mod_rewrite`/`.htaccess` support (or equivalent config on nginx — see below)

## Setup

1. Copy the `cms/` folder to your web server's document root (or a subfolder).
2. Make sure the `database/` and `uploads/` folders are writable by the web server:
   ```bash
   chmod -R 755 database uploads
   ```
3. Visit the site in your browser (e.g. `http://localhost/cms/`). The SQLite database and a default admin account are created automatically on first request.
4. Log in at `/login.php`:
   - **Username:** `admin`
   - **Password:** `ChangeMe123!`
5. **Immediately change the default password** via Admin → Users → edit your account.

## Project Structure

```
cms/
├── admin/                 Admin panel (requires login)
│   ├── includes/          Shared admin header/footer
│   ├── index.php          Dashboard
│   ├── posts.php / post_form.php / post_delete.php
│   ├── pages.php / page_form.php / page_delete.php
│   ├── categories.php / category_form.php / category_delete.php
│   ├── media.php / media_delete.php
│   └── users.php / user_form.php / user_delete.php   (admin role only)
├── assets/                 CSS for the public site and admin panel
├── database/                schema.sql + auto-created cms.sqlite (git-ignored, protected via .htaccess)
├── includes/                 db.php, auth.php, functions.php, site header/footer partials
├── uploads/                   Uploaded media (protected against PHP execution)
├── config.php                 Central bootstrap: constants, session, requires includes
├── index.php / post.php / page.php / category.php / login.php   Public front-end
└── README.md
```

## Security Notes

- All database queries use PDO prepared statements.
- All state-changing forms (create/update/delete) are protected with CSRF tokens.
- Uploaded files are renamed to random hex names, validated by extension **and** real MIME type, and capped at 5MB. The `uploads/` folder blocks PHP execution via `.htaccess`.
- Passwords are hashed with PHP's `password_hash()` (bcrypt by default).
- Only `admin` role users can manage other user accounts.

If you deploy over HTTPS (recommended), uncomment the `session.cookie_secure` line in `config.php`.

## Customization

- **Site name:** change the `SITE_NAME` constant in `config.php`.
- **Upload limits/types:** adjust `MAX_UPLOAD_BYTES` and `ALLOWED_UPLOAD_EXT` in `config.php`.
- **Styling:** edit `assets/style.css` (public site) and `assets/admin.css` (admin panel).
- **Roles:** the schema currently supports `admin` and `editor`. To add more granular roles, extend the `CHECK` constraint in `database/schema.sql` and the role checks in `includes/auth.php`.

## Nginx Note

If deploying on nginx instead of Apache, the `.htaccess` files (which restrict PHP execution in `uploads/` and block direct access to `database/`) won't apply — replicate those rules in your nginx server block, e.g.:

```nginx
location ^~ /uploads/ {
    location ~ \.php$ { deny all; }
}
location ^~ /database/ {
    deny all;
}
```
# Custom-php-cms
