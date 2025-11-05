-- WebEngine CMS Slim - Database Schema
-- This file contains all custom tables required by WebEngine CMS

-- ==============================================
-- BANS & SECURITY
-- ==============================================

CREATE TABLE GGC_BANS (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(10) NOT NULL,
    reason NVARCHAR(255),
    banned_by VARCHAR(10),
    banned_at DATETIME DEFAULT GETDATE(),
    expires_at DATETIME NULL,
    FOREIGN KEY (username) REFERENCES MEMB_INFO(memb___id)
);

CREATE TABLE GGC_BAN_LOG (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(10) NOT NULL,
    action VARCHAR(50) NOT NULL,
    reason NVARCHAR(255),
    admin VARCHAR(10),
    created_at DATETIME DEFAULT GETDATE()
);

CREATE TABLE GGC_BLOCKED_IP (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL UNIQUE,
    reason NVARCHAR(255),
    created_at DATETIME DEFAULT GETDATE()
);

-- ==============================================
-- NEWS SYSTEM
-- ==============================================

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

CREATE TABLE GGC_NEWS_TRANSLATIONS (
    id INT IDENTITY(1,1) PRIMARY KEY,
    news_id INT NOT NULL,
    language VARCHAR(5) NOT NULL,
    title NVARCHAR(255),
    content NVARCHAR(MAX),
    FOREIGN KEY (news_id) REFERENCES GGC_NEWS(id) ON DELETE CASCADE
);

CREATE INDEX idx_news_published ON GGC_NEWS(published, created_at);

-- ==============================================
-- CREDITS & DONATIONS
-- ==============================================

CREATE TABLE GGC_CREDITS_CONFIG (
    id INT IDENTITY(1,1) PRIMARY KEY,
    amount INT NOT NULL,
    credits INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    bonus_credits INT DEFAULT 0,
    active BIT DEFAULT 1,
    sort_order INT DEFAULT 0
);

CREATE TABLE GGC_CREDITS_LOGS (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(10) NOT NULL,
    amount INT NOT NULL,
    balance_before INT DEFAULT 0,
    balance_after INT DEFAULT 0,
    reason NVARCHAR(255),
    created_at DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (username) REFERENCES MEMB_INFO(memb___id)
);

CREATE TABLE GGC_PAYPAL_TRANSACTIONS (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(10) NOT NULL,
    transaction_id VARCHAR(100) UNIQUE,
    payment_status VARCHAR(50),
    amount DECIMAL(10,2),
    currency VARCHAR(3) DEFAULT 'USD',
    credits INT,
    payer_email VARCHAR(255),
    created_at DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (username) REFERENCES MEMB_INFO(memb___id)
);

CREATE TABLE GGC_STRIPE_TRANSACTIONS (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(10) NOT NULL,
    session_id VARCHAR(255) UNIQUE,
    payment_intent_id VARCHAR(255),
    payment_status VARCHAR(50),
    amount DECIMAL(10,2),
    currency VARCHAR(3) DEFAULT 'usd',
    credits INT,
    customer_email VARCHAR(255),
    created_at DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (username) REFERENCES MEMB_INFO(memb___id)
);

-- ==============================================
-- VOTING SYSTEM
-- ==============================================

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

CREATE TABLE GGC_VOTES (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(10) NOT NULL,
    site_id INT NOT NULL,
    ip_address VARCHAR(45),
    voted_at DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (username) REFERENCES MEMB_INFO(memb___id),
    FOREIGN KEY (site_id) REFERENCES GGC_VOTE_SITES(id) ON DELETE CASCADE
);

CREATE TABLE GGC_VOTE_LOGS (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(10) NOT NULL,
    site_id INT NOT NULL,
    credits_earned INT,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT GETDATE()
);

CREATE INDEX idx_votes_username ON GGC_VOTES(username, voted_at);
CREATE INDEX idx_votes_site ON GGC_VOTES(site_id, voted_at);

-- ==============================================
-- DOWNLOADS
-- ==============================================

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

-- ==============================================
-- PASSWORD RESET
-- ==============================================

CREATE TABLE GGC_PASSCHANGE_REQUEST (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(10) NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255),
    created_at DATETIME DEFAULT GETDATE(),
    expires_at DATETIME,
    used BIT DEFAULT 0,
    FOREIGN KEY (username) REFERENCES MEMB_INFO(memb___id)
);

CREATE INDEX idx_token ON GGC_PASSCHANGE_REQUEST(token, expires_at, used);

-- ==============================================
-- EMAIL VERIFICATION
-- ==============================================

CREATE TABLE GGC_REGISTER_ACCOUNT (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(10) NOT NULL,
    email VARCHAR(255) NOT NULL,
    verification_token VARCHAR(100) UNIQUE,
    verified BIT DEFAULT 0,
    created_at DATETIME DEFAULT GETDATE(),
    expires_at DATETIME
);

-- ==============================================
-- CRON JOBS
-- ==============================================

CREATE TABLE GGC_CRON (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description NVARCHAR(255),
    schedule VARCHAR(50),
    last_run DATETIME NULL,
    next_run DATETIME NULL,
    status VARCHAR(50) DEFAULT 'idle',
    enabled BIT DEFAULT 1
);

-- ==============================================
-- PLUGINS
-- ==============================================

CREATE TABLE GGC_PLUGINS (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    version VARCHAR(20),
    enabled BIT DEFAULT 0,
    config NVARCHAR(MAX),
    installed_at DATETIME DEFAULT GETDATE()
);

-- ==============================================
-- ACCOUNT COUNTRY TRACKING
-- ==============================================

CREATE TABLE GGC_ACCOUNT_COUNTRY (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(10) NOT NULL UNIQUE,
    country_code VARCHAR(2),
    country_name VARCHAR(100),
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (username) REFERENCES MEMB_INFO(memb___id)
);

-- ==============================================
-- FLA (Foreign Language Accounts)
-- ==============================================

CREATE TABLE GGC_FLA (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(10) NOT NULL UNIQUE,
    language VARCHAR(5) DEFAULT 'en',
    timezone VARCHAR(50),
    FOREIGN KEY (username) REFERENCES MEMB_INFO(memb___id)
);

-- ==============================================
-- ALTER EXISTING TABLES
-- ==============================================

-- Add credits column to MEMB_INFO if not exists
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'MEMB_INFO') AND name = 'credits')
BEGIN
    ALTER TABLE MEMB_INFO ADD credits INT DEFAULT 0;
END

-- Add admin_level column to MEMB_INFO if not exists
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'MEMB_INFO') AND name = 'admin_level')
BEGIN
    ALTER TABLE MEMB_INFO ADD admin_level INT DEFAULT 0;
END

-- Add Resets column to Character if not exists
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'Character') AND name = 'Resets')
BEGIN
    ALTER TABLE Character ADD Resets INT DEFAULT 0;
END

-- Add GrandResets column to Character if not exists
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'Character') AND name = 'GrandResets')
BEGIN
    ALTER TABLE Character ADD GrandResets INT DEFAULT 0;
END

-- Add GensFamily column to Character if not exists
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'Character') AND name = 'GensFamily')
BEGIN
    ALTER TABLE Character ADD GensFamily INT DEFAULT 0;
END

-- Add GensContribution column to Character if not exists
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'Character') AND name = 'GensContribution')
BEGIN
    ALTER TABLE Character ADD GensContribution INT DEFAULT 0;
END

-- ==============================================
-- INITIAL DATA
-- ==============================================

-- Insert default vote sites
INSERT INTO GGC_VOTE_SITES (name, url, credits_reward, active, sort_order) VALUES
('XtremeTop100', 'https://www.xtremetop100.com', 100, 1, 1),
('TopMMOSites', 'https://topmmosites.com', 100, 1, 2),
('PrivateServerList', 'https://privateserverlist.com', 100, 1, 3);

-- Insert default donation packages
INSERT INTO GGC_CREDITS_CONFIG (amount, credits, price, active, sort_order) VALUES
(100, 100, 1.00, 1, 1),
(500, 550, 5.00, 1, 2),
(1000, 1150, 10.00, 1, 3),
(5000, 6000, 50.00, 1, 4),
(10000, 13000, 100.00, 1, 5);

-- Insert default cron jobs
INSERT INTO GGC_CRON (name, description, schedule, enabled) VALUES
('cache_cleanup', 'Clean expired cache files', '0 * * * *', 1),
('ranking_update', 'Update ranking caches', '*/5 * * * *', 1),
('session_cleanup', 'Clean expired sessions', '0 */6 * * *', 1);

-- Insert sample news article
INSERT INTO GGC_NEWS (title, content, author, category, published) VALUES
('Welcome to WebEngine Slim!', 'Welcome to the new WebEngine CMS built with Slim Framework. Enjoy all the features of the original WebEngine with modern PHP standards and improved performance.', 'Admin', 'announcement', 1);

-- ==============================================
-- VIEWS FOR EASIER QUERYING
-- ==============================================

-- View for online accounts
CREATE VIEW vw_online_accounts AS
SELECT
    m.memb___id,
    m.mail_addr,
    m.credits,
    ms.ConnectStat,
    ms.ServerName,
    ms.IP
FROM MEMB_INFO m
INNER JOIN MEMB_STAT ms ON m.memb___id = ms.memb___id
WHERE ms.ConnectStat = 1;

-- View for character rankings
CREATE VIEW vw_character_rankings AS
SELECT
    c.Name,
    c.AccountID,
    c.Class,
    c.cLevel,
    ISNULL(c.Resets, 0) as Resets,
    ISNULL(c.GrandResets, 0) as GrandResets,
    c.PkCount,
    c.PkLevel,
    c.MapNumber,
    ms.ConnectStat as IsOnline
FROM Character c
LEFT JOIN MEMB_STAT ms ON c.AccountID = ms.memb___id
WHERE c.CtlCode = 0;

-- View for guild rankings
CREATE VIEW vw_guild_rankings AS
SELECT
    g.G_Name,
    g.G_Master,
    g.G_Score,
    COUNT(gm.Name) as MemberCount
FROM Guild g
LEFT JOIN GuildMember gm ON g.G_Name = gm.G_Name
GROUP BY g.G_Name, g.G_Master, g.G_Score;

-- ==============================================
-- STORED PROCEDURES
-- ==============================================

-- Procedure to add credits to account
GO
CREATE PROCEDURE sp_AddCredits
    @username VARCHAR(10),
    @amount INT,
    @reason NVARCHAR(255) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @balance_before INT;
    DECLARE @balance_after INT;

    -- Get current balance
    SELECT @balance_before = ISNULL(credits, 0) FROM MEMB_INFO WHERE memb___id = @username;

    -- Update credits
    UPDATE MEMB_INFO SET credits = ISNULL(credits, 0) + @amount WHERE memb___id = @username;

    -- Get new balance
    SELECT @balance_after = credits FROM MEMB_INFO WHERE memb___id = @username;

    -- Log transaction
    INSERT INTO GGC_CREDITS_LOGS (username, amount, balance_before, balance_after, reason)
    VALUES (@username, @amount, @balance_before, @balance_after, @reason);
END
GO

-- Procedure to record vote
GO
CREATE PROCEDURE sp_RecordVote
    @username VARCHAR(10),
    @site_id INT,
    @ip_address VARCHAR(45)
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @credits_reward INT;

    -- Get reward amount
    SELECT @credits_reward = credits_reward FROM GGC_VOTE_SITES WHERE id = @site_id;

    -- Record vote
    INSERT INTO GGC_VOTES (username, site_id, ip_address)
    VALUES (@username, @site_id, @ip_address);

    -- Add credits if reward > 0
    IF @credits_reward > 0
    BEGIN
        EXEC sp_AddCredits @username, @credits_reward, 'Vote reward';
    END

    -- Log vote
    INSERT INTO GGC_VOTE_LOGS (username, site_id, credits_earned, ip_address)
    VALUES (@username, @site_id, @credits_reward, @ip_address);
END
GO

-- Procedure to reset character
GO
CREATE PROCEDURE sp_ResetCharacter
    @character_name VARCHAR(10),
    @reset_reward_zen INT = 10000000
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE Character
    SET
        cLevel = 1,
        Experience = 0,
        LevelUpPoint = 0,
        Resets = ISNULL(Resets, 0) + 1,
        Money = Money + @reset_reward_zen
    WHERE Name = @character_name;
END
GO

PRINT 'WebEngine CMS Slim database schema created successfully!';
