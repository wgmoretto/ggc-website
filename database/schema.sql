-- WebEngine CMS - Database Schema (Simplified & Robust)
-- No foreign keys to avoid compatibility issues

-- ==============================================
-- ALTER EXISTING TABLES
-- ==============================================

-- Add credits column
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'MEMB_INFO') AND name = 'credits')
BEGIN
    ALTER TABLE MEMB_INFO ADD credits INT DEFAULT 0;
END
GO

-- Add admin_level column
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'MEMB_INFO') AND name = 'admin_level')
BEGIN
    ALTER TABLE MEMB_INFO ADD admin_level INT DEFAULT 0;
END
GO

-- Add Resets column
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'Character') AND name = 'Resets')
BEGIN
    ALTER TABLE Character ADD Resets INT DEFAULT 0;
END
GO

-- Add GrandResets column
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'Character') AND name = 'GrandResets')
BEGIN
    ALTER TABLE Character ADD GrandResets INT DEFAULT 0;
END
GO

-- ==============================================
-- CREATE TABLES (No Foreign Keys)
-- ==============================================

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_BANS')
BEGIN
    CREATE TABLE GGC_BANS (
        id INT IDENTITY(1,1) PRIMARY KEY,
        username VARCHAR(10) NOT NULL,
        reason NVARCHAR(255),
        banned_by VARCHAR(10),
        banned_at DATETIME DEFAULT GETDATE(),
        expires_at DATETIME NULL
    );
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_BAN_LOG')
BEGIN
    CREATE TABLE GGC_BAN_LOG (
        id INT IDENTITY(1,1) PRIMARY KEY,
        username VARCHAR(10) NOT NULL,
        action VARCHAR(50) NOT NULL,
        reason NVARCHAR(255),
        admin VARCHAR(10),
        created_at DATETIME DEFAULT GETDATE()
    );
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_BLOCKED_IP')
BEGIN
    CREATE TABLE GGC_BLOCKED_IP (
        id INT IDENTITY(1,1) PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        reason NVARCHAR(255),
        created_at DATETIME DEFAULT GETDATE()
    );
    CREATE UNIQUE INDEX idx_blocked_ip ON GGC_BLOCKED_IP(ip_address);
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_NEWS')
BEGIN
    CREATE TABLE GGC_NEWS (
        id INT IDENTITY(1,1) PRIMARY KEY,
        title NVARCHAR(255) NOT NULL,
        content NVARCHAR(MAX),
        author VARCHAR(10),
        category VARCHAR(50) DEFAULT 'general',
        published BIT DEFAULT 1,
        created_at DATETIME DEFAULT GETDATE(),
        updated_at DATETIME NULL
    );
    CREATE INDEX idx_news_published ON GGC_NEWS(published, created_at);
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_NEWS_TRANSLATIONS')
BEGIN
    CREATE TABLE GGC_NEWS_TRANSLATIONS (
        id INT IDENTITY(1,1) PRIMARY KEY,
        news_id INT NOT NULL,
        language VARCHAR(5) NOT NULL,
        title NVARCHAR(255),
        content NVARCHAR(MAX)
    );
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_CREDITS_CONFIG')
BEGIN
    CREATE TABLE GGC_CREDITS_CONFIG (
        id INT IDENTITY(1,1) PRIMARY KEY,
        amount INT NOT NULL,
        credits INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        bonus_credits INT DEFAULT 0,
        active BIT DEFAULT 1,
        sort_order INT DEFAULT 0
    );
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_CREDITS_LOGS')
BEGIN
    CREATE TABLE GGC_CREDITS_LOGS (
        id INT IDENTITY(1,1) PRIMARY KEY,
        username VARCHAR(10) NOT NULL,
        amount INT NOT NULL,
        balance_before INT DEFAULT 0,
        balance_after INT DEFAULT 0,
        reason NVARCHAR(255),
        created_at DATETIME DEFAULT GETDATE()
    );
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_PAYPAL_TRANSACTIONS')
BEGIN
    CREATE TABLE GGC_PAYPAL_TRANSACTIONS (
        id INT IDENTITY(1,1) PRIMARY KEY,
        username VARCHAR(10) NOT NULL,
        transaction_id VARCHAR(100),
        payment_status VARCHAR(50),
        amount DECIMAL(10,2),
        currency VARCHAR(3) DEFAULT 'USD',
        credits INT,
        payer_email VARCHAR(255),
        created_at DATETIME DEFAULT GETDATE()
    );
    CREATE UNIQUE INDEX idx_paypal_txn ON GGC_PAYPAL_TRANSACTIONS(transaction_id);
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_STRIPE_TRANSACTIONS')
BEGIN
    CREATE TABLE GGC_STRIPE_TRANSACTIONS (
        id INT IDENTITY(1,1) PRIMARY KEY,
        username VARCHAR(10) NOT NULL,
        session_id VARCHAR(255),
        payment_intent_id VARCHAR(255),
        payment_status VARCHAR(50),
        amount DECIMAL(10,2),
        currency VARCHAR(3) DEFAULT 'usd',
        credits INT,
        customer_email VARCHAR(255),
        created_at DATETIME DEFAULT GETDATE()
    );
    CREATE UNIQUE INDEX idx_stripe_session ON GGC_STRIPE_TRANSACTIONS(session_id);
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_VOTE_SITES')
BEGIN
    CREATE TABLE GGC_VOTE_SITES (
        id INT IDENTITY(1,1) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        url VARCHAR(255),
        api_url VARCHAR(255) NULL,
        credits_reward INT DEFAULT 0,
        active BIT DEFAULT 1,
        sort_order INT DEFAULT 0,
        vote_interval INT DEFAULT 12
    );
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_VOTES')
BEGIN
    CREATE TABLE GGC_VOTES (
        id INT IDENTITY(1,1) PRIMARY KEY,
        username VARCHAR(10) NOT NULL,
        site_id INT NOT NULL,
        ip_address VARCHAR(45),
        voted_at DATETIME DEFAULT GETDATE()
    );
    CREATE INDEX idx_votes_username ON GGC_VOTES(username, voted_at);
    CREATE INDEX idx_votes_site ON GGC_VOTES(site_id, voted_at);
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_VOTE_LOGS')
BEGIN
    CREATE TABLE GGC_VOTE_LOGS (
        id INT IDENTITY(1,1) PRIMARY KEY,
        username VARCHAR(10) NOT NULL,
        site_id INT NOT NULL,
        credits_earned INT,
        ip_address VARCHAR(45),
        created_at DATETIME DEFAULT GETDATE()
    );
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_DOWNLOADS')
BEGIN
    CREATE TABLE GGC_DOWNLOADS (
        id INT IDENTITY(1,1) PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description NVARCHAR(MAX),
        url VARCHAR(255),
        file_size VARCHAR(50),
        version VARCHAR(50) NULL,
        download_count INT DEFAULT 0,
        active BIT DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at DATETIME DEFAULT GETDATE()
    );
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_PASSCHANGE_REQUEST')
BEGIN
    CREATE TABLE GGC_PASSCHANGE_REQUEST (
        id INT IDENTITY(1,1) PRIMARY KEY,
        username VARCHAR(10) NOT NULL,
        token VARCHAR(100) NOT NULL,
        email VARCHAR(255),
        created_at DATETIME DEFAULT GETDATE(),
        expires_at DATETIME,
        used BIT DEFAULT 0
    );
    CREATE UNIQUE INDEX idx_token ON GGC_PASSCHANGE_REQUEST(token);
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_REGISTER_ACCOUNT')
BEGIN
    CREATE TABLE GGC_REGISTER_ACCOUNT (
        id INT IDENTITY(1,1) PRIMARY KEY,
        username VARCHAR(10) NOT NULL,
        email VARCHAR(255) NOT NULL,
        verification_token VARCHAR(100),
        verified BIT DEFAULT 0,
        created_at DATETIME DEFAULT GETDATE(),
        expires_at DATETIME
    );
    CREATE UNIQUE INDEX idx_register_token ON GGC_REGISTER_ACCOUNT(verification_token);
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_CRON')
BEGIN
    CREATE TABLE GGC_CRON (
        id INT IDENTITY(1,1) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description NVARCHAR(255),
        schedule VARCHAR(50),
        last_run DATETIME NULL,
        next_run DATETIME NULL,
        status VARCHAR(50) DEFAULT 'idle',
        enabled BIT DEFAULT 1
    );
    CREATE UNIQUE INDEX idx_cron_name ON GGC_CRON(name);
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_PLUGINS')
BEGIN
    CREATE TABLE GGC_PLUGINS (
        id INT IDENTITY(1,1) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        version VARCHAR(20),
        enabled BIT DEFAULT 0,
        config NVARCHAR(MAX),
        installed_at DATETIME DEFAULT GETDATE()
    );
    CREATE UNIQUE INDEX idx_plugin_name ON GGC_PLUGINS(name);
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_ACCOUNT_COUNTRY')
BEGIN
    CREATE TABLE GGC_ACCOUNT_COUNTRY (
        id INT IDENTITY(1,1) PRIMARY KEY,
        username VARCHAR(10) NOT NULL,
        country_code VARCHAR(2),
        country_name VARCHAR(100),
        ip_address VARCHAR(45),
        created_at DATETIME DEFAULT GETDATE()
    );
    CREATE UNIQUE INDEX idx_account_country ON GGC_ACCOUNT_COUNTRY(username);
END
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'GGC_FLA')
BEGIN
    CREATE TABLE GGC_FLA (
        id INT IDENTITY(1,1) PRIMARY KEY,
        username VARCHAR(10) NOT NULL,
        language VARCHAR(5) DEFAULT 'en',
        timezone VARCHAR(50)
    );
    CREATE UNIQUE INDEX idx_fla_username ON GGC_FLA(username);
END
GO

-- ==============================================
-- INSERT DEFAULT DATA (Only if tables are empty)
-- ==============================================

IF NOT EXISTS (SELECT * FROM GGC_VOTE_SITES)
BEGIN
    INSERT INTO GGC_VOTE_SITES (name, url, credits_reward, active, sort_order) VALUES
    ('XtremeTop100', 'https://www.xtremetop100.com', 100, 1, 1),
    ('TopMMOSites', 'https://topmmosites.com', 100, 1, 2),
    ('PrivateServerList', 'https://privateserverlist.com', 100, 1, 3);
END
GO

IF NOT EXISTS (SELECT * FROM GGC_CREDITS_CONFIG)
BEGIN
    INSERT INTO GGC_CREDITS_CONFIG (amount, credits, price, active, sort_order) VALUES
    (100, 100, 1.00, 1, 1),
    (500, 550, 5.00, 1, 2),
    (1000, 1150, 10.00, 1, 3),
    (5000, 6000, 50.00, 1, 4),
    (10000, 13000, 100.00, 1, 5);
END
GO

IF NOT EXISTS (SELECT * FROM GGC_CRON)
BEGIN
    INSERT INTO GGC_CRON (name, description, schedule, enabled) VALUES
    ('cache_cleanup', 'Clean expired cache files', '0 * * * *', 1),
    ('ranking_update', 'Update ranking caches', '*/5 * * * *', 1),
    ('session_cleanup', 'Clean expired sessions', '0 */6 * * *', 1);
END
GO

IF NOT EXISTS (SELECT * FROM GGC_NEWS)
BEGIN
    INSERT INTO GGC_NEWS (title, content, author, category, published) VALUES
    ('Welcome to WebEngine!', 'Welcome to WebEngine CMS built with Slim Framework. Enjoy all the features with modern PHP standards.', 'Admin', 'announcement', 1);
END
GO

PRINT 'WebEngine CMS database schema installed successfully!';
GO
