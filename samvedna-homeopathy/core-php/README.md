# Samvedna Core PHP Inquiry Admin

This folder is a standalone core PHP inquiry system.

## Pages

- Public inquiry form: `index.php`
- Admin inquiry dashboard: `admin/index.php`

## Default Admin Login

- Username: `admin`
- Password: `Samvedna@2026`

Change the password before uploading to a live server.

To create a new password hash:

```bash
php -r "echo password_hash('YourNewPassword', PASSWORD_DEFAULT), PHP_EOL;"
```

Then replace `ADMIN_PASSWORD_HASH` in `config.php`.

## Local Run

From the Next project folder:

```bash
php -S localhost:8080 -t core-php
```

Open:

- `http://localhost:8080/`
- `http://localhost:8080/admin/`

## Storage

Inquiry entries are stored in:

```text
storage/inquiries.jsonl
```

Make sure the `storage` folder is writable on the server. The included
`.htaccess` files block direct browser access to config and stored inquiry data
on Apache hosting.
