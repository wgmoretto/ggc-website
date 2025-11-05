# WebEngine CMS - Slim PHP Edition

A modern, lightweight MU Online CMS built with Slim Framework 4, replicating all features from the original WebEngine CMS.

## Features

✅ **Complete Feature Set** - All original WebEngine CMS functionality replicated
- User authentication and registration
- Account management
- Character management
- News system with translations
- Rankings (Level, Resets, Guilds, PK, Votes, etc.)
- User Control Panel with all functions:
  - Add Stats
  - Character Reset
  - Reset Stats
  - Buy Zen
  - Clear PK
  - Clear Skill Tree
  - Unstick Character
  - Vote System
- Castle Siege management
- Donation/Shop system with PayPal integration
- Admin Control Panel
- API endpoints
- Profile pages (Player & Guild)
- Email notifications
- Caching system
- CSRF protection
- Session management

## Requirements

- PHP 8.1 or higher
- MSSQL Server
- PDO with sqlsrv/dblib driver
- Composer
- Apache/Nginx with mod_rewrite

### PHP Extensions Required

- pdo_sqlsrv (or pdo_dblib/pdo_odbc)
- curl
- openssl
- mbstring
- json

## Installation

> 🚀 **NEW!** WebEngine CMS now includes a web-based installer for easy setup!

### Quick Install (Recommended)

The easiest way to install WebEngine CMS is using our interactive web installer:

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Access the installer:**
   - Point your web server to the `/public` directory
   - Navigate to: `http://yourserver.com/install`
   - Follow the 7-step installation wizard

3. **Secure your installation:**
   ```bash
   rm -rf install/  # Remove installer after completion
   ```

For detailed installation instructions, see [INSTALL.md](INSTALL.md)

---

### Manual Installation

If you prefer manual installation or need to automate the process:

### 1. Clone the Repository

```bash
git clone <repository-url>
cd web-engine/slim
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

Copy `.env.example` to `.env` and configure your settings:

```bash
cp .env.example .env
```

Edit `.env` with your database credentials and settings:

```env
# Database
DB_CONNECTION=sqlsrv
DB_HOST=localhost
DB_PORT=1433
DB_DATABASE=MuOnline
DB_USERNAME=sa
DB_PASSWORD=your_password

# Application
APP_NAME="Your Server Name"
APP_URL=http://yourserver.com

# Email (for notifications)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_password

# PayPal (for donations)
PAYPAL_CLIENT_ID=your_client_id
PAYPAL_CLIENT_SECRET=your_secret
```

### 4. Create Required Directories

```bash
mkdir -p storage/cache storage/logs
chmod -R 775 storage
```

### 5. Create Database Tables

Run the following SQL scripts to create WebEngine tables:

```sql
-- GGC_BANS
CREATE TABLE GGC_BANS (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(10) NOT NULL,
    reason NVARCHAR(255),
    banned_by VARCHAR(10),
    banned_at DATETIME,
    expires_at DATETIME NULL
);

-- GGC_NEWS
CREATE TABLE GGC_NEWS (
    id INT IDENTITY(1,1) PRIMARY KEY,
    title NVARCHAR(255) NOT NULL,
    content NVARCHAR(MAX),
    author VARCHAR(10),
    category VARCHAR(50),
    published BIT DEFAULT 1,
    created_at DATETIME,
    updated_at DATETIME NULL
);

-- GGC_NEWS_TRANSLATIONS
CREATE TABLE GGC_NEWS_TRANSLATIONS (
    id INT IDENTITY(1,1) PRIMARY KEY,
    news_id INT NOT NULL,
    language VARCHAR(5) NOT NULL,
    title NVARCHAR(255),
    content NVARCHAR(MAX)
);

-- GGC_CREDITS_LOGS
CREATE TABLE GGC_CREDITS_LOGS (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(10) NOT NULL,
    amount INT NOT NULL,
    reason NVARCHAR(255),
    created_at DATETIME
);

-- GGC_PAYPAL_TRANSACTIONS
CREATE TABLE GGC_PAYPAL_TRANSACTIONS (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(10) NOT NULL,
    transaction_id VARCHAR(100),
    payment_status VARCHAR(50),
    amount DECIMAL(10,2),
    currency VARCHAR(3),
    credits INT,
    created_at DATETIME
);

-- GGC_VOTES
CREATE TABLE GGC_VOTES (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(10) NOT NULL,
    site_id INT NOT NULL,
    ip_address VARCHAR(45),
    voted_at DATETIME
);

-- GGC_VOTE_SITES
CREATE TABLE GGC_VOTE_SITES (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    url VARCHAR(255),
    credits_reward INT DEFAULT 0,
    active BIT DEFAULT 1,
    sort_order INT DEFAULT 0
);

-- GGC_DOWNLOADS
CREATE TABLE GGC_DOWNLOADS (
    id INT IDENTITY(1,1) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description NVARCHAR(MAX),
    url VARCHAR(255),
    file_size VARCHAR(50),
    active BIT DEFAULT 1,
    sort_order INT DEFAULT 0
);

-- GGC_PASSCHANGE_REQUEST
CREATE TABLE GGC_PASSCHANGE_REQUEST (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(10) NOT NULL,
    token VARCHAR(100) NOT NULL,
    created_at DATETIME,
    expires_at DATETIME,
    used BIT DEFAULT 0
);

-- GGC_CRON
CREATE TABLE GGC_CRON (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    last_run DATETIME,
    status VARCHAR(50)
);

-- GGC_BLOCKED_IP
CREATE TABLE GGC_BLOCKED_IP (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    reason NVARCHAR(255),
    created_at DATETIME
);

-- GGC_CREDITS_CONFIG
CREATE TABLE GGC_CREDITS_CONFIG (
    id INT IDENTITY(1,1) PRIMARY KEY,
    amount INT NOT NULL,
    credits INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    active BIT DEFAULT 1
);

-- Add credits column to MEMB_INFO if not exists
ALTER TABLE MEMB_INFO ADD credits INT DEFAULT 0;
ALTER TABLE MEMB_INFO ADD admin_level INT DEFAULT 0;
```

### 6. Configure Web Server

#### Apache

Ensure `.htaccess` in `/public` directory exists and mod_rewrite is enabled:

```apache
<VirtualHost *:80>
    ServerName yourserver.com
    DocumentRoot /path/to/slim/public

    <Directory /path/to/slim/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name yourserver.com;
    root /path/to/slim/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 7. Access Your Site

Visit `http://yourserver.com` in your browser.

### 8. Create Administrator Account

After installation, create an administrator to access the admin panel:

1. Register a new account on your website
2. Execute the following SQL to grant admin privileges:
   ```sql
   UPDATE MEMB_INFO
   SET admin_level = 1
   WHERE memb___id = 'your_username';
   ```
3. Access the admin panel at: `http://yourserver.com/admin`

**Admin Levels:**
- `0` = Regular user
- `1` = Administrator (full access)

## Project Structure

```
slim/
├── config/              # Configuration files
│   ├── container.php    # Dependency injection container
│   └── routes.php       # Application routes
├── public/              # Web root
│   ├── index.php        # Entry point
│   └── .htaccess        # Apache rewrite rules
├── src/                 # Application source code
│   ├── Controllers/     # Request handlers
│   ├── Models/          # Data models
│   ├── Services/        # Business logic services
│   ├── Middleware/      # Request middleware
│   └── Database/        # Database layer
├── storage/             # Storage directory
│   ├── cache/           # Cache files
│   └── logs/            # Log files
├── templates/           # Twig templates
├── composer.json        # PHP dependencies
├── .env.example         # Environment configuration example
└── README.md            # This file
```

## Architecture

### MVC Pattern

- **Models** (`src/Models/`): Data access and business logic
  - `Account.php` - User account operations
  - `Character.php` - Character management
  - `News.php` - News articles
  - `Rankings.php` - Ranking calculations
  - `Guild.php` - Guild management
  - `Vote.php` - Voting system
  - `CastleSiege.php` - Castle Siege events
  - `Donation.php` - Shop/donation system

- **Controllers** (`src/Controllers/`): Handle HTTP requests
  - `HomeController.php` - Homepage
  - `AuthController.php` - Authentication
  - `NewsController.php` - News display
  - `RankingsController.php` - Rankings pages
  - `ProfileController.php` - Player/Guild profiles
  - `UserCpController.php` - User Control Panel
  - `DonationController.php` - Shop/donations
  - `AdminController.php` - Admin Panel
  - `ApiController.php` - API endpoints

- **Views** (`templates/`): Twig templates for UI

### Services Layer

- **AuthService**: Authentication and authorization
- **CacheService**: File-based caching
- **EmailService**: Email notifications with PHPMailer

### Middleware

- **SessionMiddleware**: Session management
- **AuthMiddleware**: Protected route authentication
- **AdminMiddleware**: Admin-only access control
- **CsrfMiddleware**: CSRF token validation

## API Endpoints

All API endpoints are under `/api/`:

- `GET /api/servertime` - Get server time
- `GET /api/events` - Get event schedule
- `GET /api/castlesiege` - Get Castle Siege info
- `GET /api/guildmark/{guild}` - Get guild emblem
- `GET /api/version` - Get CMS version
- `POST /api/cron` - Execute cron jobs
- `POST /api/paypal/ipn` - PayPal IPN handler

## Development

### Running in Development

```bash
cd public
php -S localhost:8000
```

Visit `http://localhost:8000`

### Enabling Debug Mode

In `.env`:

```env
APP_ENV=development
APP_DEBUG=true
```

## Security

- CSRF protection on all forms
- Password hashing (MD5, SHA256, WebZen MD5)
- SQL injection protection via PDO prepared statements
- Session security (HttpOnly cookies, SameSite)
- Admin access control
- IP blocking system

## Performance

- File-based caching for rankings and news
- Database query optimization
- Lazy loading of dependencies
- Twig template caching in production

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

MIT License - See LICENSE file for details

## Support

For issues and support, please open an issue on GitHub.

## Credits

Based on the original WebEngine CMS by GGCode.
Rewritten with Slim Framework 4 for modern PHP standards.

---

**Version**: 2.0.0
**Built with**: Slim 4, Twig, PHPMailer, Bootstrap 3
