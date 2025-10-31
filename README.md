# Project Setup (Local)

## Requirements
- PHP \>= 8.2
- Composer
- Node.js and npm
- MySQL (server)
- Git
- Windows (instructions use PowerShell / Git Bash)

## Quick checklist
1. Verify PHP version:
   - `php -v` (must be 8.2 or higher)
2. Verify composer:
   - `composer --version`
3. Verify Node/npm:
   - `node -v`
   - `npm -v`

# clone repo and enter project folder
```bash
git clone https://github.com/jibon2016/task.git
cd task
```

# Copy env file
```bash
cp .env.example .env
```

# Edit `.\.env` and set 
```bash
DB_CONNECTION=mysql, 
DB_HOST, 
DB_PORT, 
DB_DATABASE,
DB_USERNAME, 
DB_PASSWORD
```



# Install PHP dependencies
```bash
composer install
php artisan key:generate
```

# Run migrations
```bash
php artisan migrate
```

# Database seed
```bash
php artisan db:seed
```

# install JS dependencies and build assets
```bash
npm install
npm run dev  
```


# start local server
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

# Credentials of seeded user
- Email: test@example.com
- Password: password
