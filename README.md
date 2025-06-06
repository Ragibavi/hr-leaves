## Getting Started

To get this project up and running locally, follow these steps:

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy .env and set your environment variables
cp .env.example .env

# 3. Generate application key
php artisan key:generate

# 4. Install frontend dependencies
npm install

# 5. Compile frontend assets
npm run dev

# 6. Migrate and Seed
php artisan migrate --seed

# 7. Run Laravel
php artisan serve
