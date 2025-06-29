# Tiered Storage Setup - Hot to Cold Data Transfer

## Overview
The Polyscope system includes an automated tiered storage daemon that moves old polyzoomer files from local hot storage to research drive cold storage while maintaining user access through symlinks and cache updates.

## Prerequisites
- Docker container running with mounted volumes:
  - Hot storage: `/data/polyzoomer` → `/var/www/polyzoomer`
  - Cold storage: `/rsrch9/home/plm/polyscope/test/storage/polyzoomer` → `/var/www/polyzoomer_cold`
  - Customer data: `/rsrch9/home/plm/polyscope/test/storage/customers` → `/var/www/customers`

## Configuration

### 1. Archive Age Settings
The tiered storage daemon is configured in `/var/www/polyzoomerGlobals.php`. To modify the archive age:

```bash
# Edit the configuration file
docker exec -it polyscope-container nano /var/www/polyzoomerGlobals.php
```

Find the `tieringConfig()` function and modify the `archive_age_days` value:
```php
function tieringConfig() {
    return array(
        'archive_age_days' => 30,  // Change this value (days)
        'check_interval_hours' => 24,
        'hot_path' => polyzoomerHotPath(),
        'cold_path' => polyzoomerColdPath(),
        'log_file' => logFolder() . 'tiered_storage.log'
    );
}
```

Alternatively, use sed to change it directly:
```bash
# Change from 30 days to 1 day (for testing)
docker exec polyscope-container sed -i "s/'archive_age_days' => 30,/'archive_age_days' => 1,/" /var/www/polyzoomerGlobals.php
```

### 2. Manual Testing

#### Test with Dry Run (Recommended First)
```bash
# See what files would be archived without actually moving them
docker exec polyscope-container php /var/www/tiered_storage_daemon.php --dry-run

# Check the log to see results
docker exec polyscope-container cat /var/www/tiered_storage.log
```

#### Execute Actual Transfer
```bash
# Run the actual archival process
docker exec polyscope-container php /var/www/tiered_storage_daemon.php

# Monitor progress in real-time
docker exec polyscope-container tail -f /var/www/tiered_storage.log
```

### 3. Automated Daily Execution

#### Setup Cron Job on Host VM
```bash
# Create log directory on host
sudo mkdir -p /var/log/polyscope
sudo chown yshokrollahi:path_lab_med_div /var/log/polyscope

# Edit crontab
crontab -e

# Add this line to run daily at 2 AM
0 2 * * * docker exec polyscope-container php /var/www/tiered_storage_daemon.php >> /var/log/polyscope/tiered-storage.log 2>&1
```

#### Verify Cron Job
```bash
# List current cron jobs
crontab -l

# Check cron service status
sudo systemctl status crond
```

### 4. Monitoring and Maintenance

#### Check Logs
```bash
# View recent activity
tail -20 /var/log/polyscope/tiered-storage.log

# Monitor in real-time
tail -f /var/log/polyscope/tiered-storage.log

# Check container logs
docker exec polyscope-container tail -10 /var/www/tiered_storage.log
```

#### Verify File Locations
```bash
# Check hot storage contents
docker exec polyscope-container ls -la /var/www/polyzoomer/

# Check cold storage contents
docker exec polyscope-container ls -la /var/www/polyzoomer_cold/

# Check directory sizes
docker exec polyscope-container du -sh /var/www/polyzoomer/
docker exec polyscope-container du -sh /var/www/polyzoomer_cold/
```

### 5. Alternative Cron Frequencies

```bash
# Every 6 hours
0 */6 * * * docker exec polyscope-container php /var/www/tiered_storage_daemon.php >> /var/log/polyscope/tiered-storage.log 2>&1

# Weekly (Sundays at 3 AM)
0 3 * * 0 docker exec polyscope-container php /var/www/tiered_storage_daemon.php >> /var/log/polyscope/tiered-storage.log 2>&1

# Daily at 2 AM (recommended)
0 2 * * * docker exec polyscope-container php /var/www/tiered_storage_daemon.php >> /var/log/polyscope/tiered-storage.log 2>&1
```

## What the Tiered Storage Daemon Does

1. **Identifies Old Files**: Scans hot storage for directories older than the configured age
2. **Archives Data**: Copies files from hot storage (`/data/polyzoomer`) to cold storage (`/rsrch9/home/plm/polyscope/test/storage/polyzoomer`)
3. **Updates User Access**: 
   - Updates user cache files to point to cold storage locations
   - Updates symlinks for both regular and multizoom projects
   - Maintains seamless user access to archived files
4. **Cleanup**: Removes files from hot storage after successful archival
5. **Logging**: Records all operations with timestamps and file details

## Troubleshooting

### Common Issues

#### File Not Found Error
```bash
# If you get "Could not open input file" error, check file location
docker exec polyscope-container find /var/www -name "*tiered*" -type f
```

#### Permission Issues
```bash
# Fix ownership if needed
docker exec polyscope-container chown -R www-data:www-data /var/www/polyzoomer_cold/
docker exec polyscope-container chmod -R 755 /var/www/polyzoomer_cold/
```

#### Cron Not Running
```bash
# Check if cron service is active
sudo systemctl status crond
# If not running, start it
sudo systemctl start crond
sudo systemctl enable crond
```

### Verify Configuration
```bash
# Check current settings
docker exec polyscope-container grep -A 5 "tieringConfig" /var/www/polyzoomerGlobals.php

# Test configuration
docker exec polyscope-container php -l /var/www/tiered_storage_daemon.php
```

## Safety Features

- **Dry Run Mode**: Test what would be archived without moving files
- **Comprehensive Logging**: All operations are logged with timestamps
- **Symlink Management**: Maintains user access to archived files
- **Cache Updates**: Automatically updates user cache files
- **Error Handling**: Continues operation even if individual files fail

---

### 5. Setup MySQL Database
#### Start MySQL Service
```bash
docker exec -it polyscope-container bash
mv /var/lib/mysql /var/lib/mysql_backup
mkdir /var/lib/mysql
chown -R mysql:mysql /var/lib/mysql
mysql_install_db --user=mysql --ldata=/var/lib/mysql
service mysql start
docker exec -it polyscope-container service mysql start
```
#### Access MySQL Shell
```bash
docker exec -it polyscope-container
mysql -u root -p
```
If denied access, reset the root password:
```bash
docker exec -it polyscope-container mysqld_safe --skip-grant-tables &
docker exec -it polyscope-container mysql -u root
```
#### Reset Root Password
Inside the MySQL shell, run:
```sql
USE mysql;
UPDATE user SET plugin='mysql_native_password' WHERE User='root';
FLUSH PRIVILEGES;
SET PASSWORD FOR 'root'@'localhost' = PASSWORD('newpassword');
FLUSH PRIVILEGES;
```
#### Restart MySQL Service
```bash
docker exec -it polyscope-container service mysql restart
```
#### Create Database and Tables
```sql
-- Access MySQL shell
docker exec -it polyscope-container mysql -u root -p
-- Create the new database
CREATE DATABASE polyscope;
USE polyscope;
-- Create the audit_logs table
CREATE TABLE audit_logs (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) DEFAULT NULL,
    action VARCHAR(255) NOT NULL,
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) DEFAULT NULL,
    additional_info TEXT DEFAULT NULL,
    PRIMARY KEY (id)
);
-- Create the users table
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    failed_login_attempts INT(11) DEFAULT 0,
    account_locked TINYINT(1) DEFAULT 0,
    last_login TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id)
);
```
## Troubleshooting
### Container Name Conflict
If you encounter a container name conflict error, stop and remove the existing container:
```bash
docker stop polyscope-container
docker rm polyscope-container
```
Alternatively, use a different container name when running the Docker container:
```bash
docker run -d -p 80:80 -p 3306:3306 --hostname YOUR_HOSTNAME --restart always \
-v /path/to/polyzoomer:/var/www/polyzoomer \
-v /path/to/customers:/var/www/customers \
-v /path/to/media:/media \
-v "/path/to/user_db:/var/lib/mysql" \
--name polyscope-container-new ghcr.io/idso-fa1-pathology/polyscope-development:latest
```
## Additional Information
- For more information about MariaDB, visit [MariaDB.org](http://mariadb.org/).
- Report any issues at [MariaDB JIRA](http://mariadb.org/jira).
Consider joining MariaDB's strong and vibrant community: [MariaDB Community](https://mariadb.org/get-involved/)
## License
This project is licensed under the MIT License.
