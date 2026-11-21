# Corner Store POS - TFA7

An extension of the IT0049 TFA6 POS project for Technical Formative Assessment 7, **Forms, Validation, and File Upload**.

The application keeps the Home, About, Customer Accounts, and User Accounts pages. It adds create and edit forms for both account types, server-side validation, and prepared avatar uploads on the user edit page.

## Requirements

- PHP 8.3 or newer with Laravel's required extensions
- Composer 2
- SQLite support: `pdo_sqlite` and `sqlite3`
- PHP `gd` and `exif` extensions for image processing

Intervention Image 4 is included in `composer.json` and `composer.lock`. Composer installs it with the other dependencies. The interface uses Blade and plain CSS; Node.js and npm are not needed.

Check the PHP configuration used by your terminal with `php --ini` and enabled extensions with `php -m`. For uploads, PHP's `upload_max_filesize` must allow at least 2 MB and `post_max_size` must allow the file plus form fields. Settings such as `upload_max_filesize=4M`, `post_max_size=8M`, and `memory_limit=256M` work well for this project.

## Fresh installation

Extract the project into its own folder. Open a terminal in the folder containing `artisan` and `composer.json`, then run:

```sh
composer install
composer run setup
php artisan serve
```

Open **http://127.0.0.1:8000** in your browser.

Setup copies `.env.example` to `.env` when needed, generates an application key, creates `database/database.sqlite`, and runs the migrations and seeders. A fresh installation has five customers and five users. Initial users have placeholder profile pictures.

Run setup once on a fresh installation. It generates a new key and runs the seeders, so it is not the command to use for ordinary application updates.

## Updating an existing TFA6 installation

The original TFA6 migrations are retained. The new migration only adds a nullable `avatar` column to `users`.

Keep your existing `.env`, SQLite database, and uploaded files when updating the application source. Then run:

```sh
composer install
php artisan migrate
php artisan config:clear
php artisan serve
```

Do not use `migrate:fresh` on a database whose records you want to keep. Reseeding is unnecessary when upgrading an existing installation.

## Pages and form routes

| Method | URL | Purpose |
| --- | --- | --- |
| GET | `/` | Landing page |
| GET | `/about` | About the project |
| GET | `/customers` | Customer list with Create and Edit links |
| GET | `/customers/create` | Create Customer form |
| POST | `/customers` | Validate and save a new customer |
| GET | `/customers/{customer}/edit` | Edit Customer form |
| PUT or PATCH | `/customers/{customer}` | Validate and update a customer |
| GET | `/users` | User list with avatars, Create, and Edit links |
| GET | `/users/create` | Create User form |
| POST | `/users` | Validate and save a new user |
| GET | `/users/{user}/edit` | Edit User form, including the avatar input |
| PUT or PATCH | `/users/{user}` | Validate and update a user and optional avatar |
| GET | `/storage/avatars/{filename}` | Display a prepared avatar from the public disk |

## Form behavior

Customer forms require a full name and a valid email address. Phone is optional; an empty phone value is saved as an empty string to keep TFA6's existing non-nullable column compatible.

User forms require a full name and a unique username. During an edit, the current user's username is allowed, while another user's username is rejected.

`CustomerRequest` and `UserRequest` contain the validation rules. Forms include `@csrf`, and edit forms use `@method('PUT')`. When validation fails, Laravel redirects back with errors and old input. The views display both a summary and field messages, and `old()` preserves typed text. File inputs cannot be repopulated by the browser, so an image must be selected again after a failed submission.

Successful submissions redirect to the corresponding account list with a confirmation message. The application includes create, list, and update workflows; deletion and sign-in are outside this activity's listed requirements.

## Avatar preparation and storage

Create a user first, then click **Edit** beside that user to upload a profile picture.

1. The upload is validated as a real image, no larger than 2048 KB (2 MB).
2. Supported input types are JPG, PNG, WebP, GIF, and BMP. SVG uploads are rejected. Images are limited to 4096 pixels on each side.
3. Intervention Image uses the GD driver to orient the image, crop and resize it to **256 x 256 pixels**, and encode it as a JPEG. Animated files use a single frame, and metadata is removed.
4. `Storage::disk('public')->put()` saves the prepared image under `storage/app/public/avatars` with a generated UUID filename.
5. Only the relative path, such as `avatars/uuid.jpg`, is saved in the user's `avatar` column. The original upload is not retained.
6. The User Accounts page displays the prepared image at 48 x 48 pixels. Users without an uploaded image display `public/images/avatar-placeholder.svg`.

An edit without a new image keeps the existing avatar. When replacing an avatar, the old file is removed only after the database update succeeds. If saving the database record fails, the newly written file is cleaned up.

The avatar display route only accepts generated JPEG filenames and reads them from the public disk. This allows avatars to work without requiring a Windows symbolic link. Laravel's usual `php artisan storage:link` may still be used where supported, but it is not required for this project.

## Database schema

| Table | Fields |
| --- | --- |
| `customers` | `id`, `full_name`, `email`, `phone`, timestamps |
| `users` | `id`, `username` (unique), `full_name`, `avatar` (nullable), timestamps |

The migrations and seeders recreate the sample database, so a separate SQL export is unnecessary. `.env`, the generated SQLite database, uploaded avatars, logs, and the `vendor` folder are excluded from the source ZIP and Git.

## Main files

```text
app/Http/Controllers/
  CustomerController.php
  UserController.php
  PageController.php
app/Http/Requests/
  CustomerRequest.php
  UserRequest.php
app/Models/
  Customer.php
  User.php
app/Services/AvatarStorage.php
database/migrations/
  0001_01_01_000000_create_users_table.php
  2026_11_21_000001_create_customers_table.php
  2026_11_21_000002_add_avatar_to_users_table.php
resources/views/
  layouts/app.blade.php
  partials/form-errors.blade.php
  home.blade.php
  about.blade.php
  customers/
    index.blade.php
    create.blade.php
    edit.blade.php
    _form.blade.php
  users/
    index.blade.php
    create.blade.php
    edit.blade.php
    _form.blade.php
public/css/style.css
public/images/avatar-placeholder.svg
routes/web.php
tests/Feature/
```

## Check and test

```sh
php artisan route:list --except-vendor
php artisan migrate:status
php artisan test
```

The tests cover the original pages and data, create and update forms, required fields, email validation, unique usernames, preservation of old text input, prefilled edits, real image preparation, the 2 MB boundary, invalid file rejection, placeholder display, avatar replacement, and cleanup after a failed database update.

Feature tests use an in-memory SQLite database and a fake public disk, with real Intervention Image processing. The included GitHub Actions workflow enables GD and EXIF and runs the tests.

## Screenshots

The worksheet does not explicitly require screenshots. If your professor requests evidence, capture the account lists, both create forms, a validation error with preserved input, prefilled edit forms, and the User Accounts list after uploading an avatar.

This project is prepared for local use. No public deployment is included.

## Upload to GitHub

Create a new empty repository. Upload the **contents of the `corner-store-pos-tfa7` folder**, keeping `artisan`, `composer.json`, and `README.md` at the repository root. Include `.env.example`, `.gitignore`, and `.github/workflows/tests.yml`.

Do not upload only the ZIP, and do not upload your generated `.env`, SQLite database, `vendor`, or runtime uploads.

If using Git, replace the repository URL below with your own:

```sh
git init
git add .
git commit -m "Add validated account forms and avatar uploads"
git branch -M main
git remote add origin https://github.com/YOUR-USERNAME/YOUR-REPOSITORY.git
git push -u origin main
```

## References

- [Laravel validation](https://laravel.com/docs/13.x/validation)
- [Laravel file storage](https://laravel.com/docs/13.x/filesystem)
- [Intervention Image installation](https://image.intervention.io/v4/getting-started/installation)
- [Intervention Image resizing](https://image.intervention.io/v4/modifying-images/resizing)
