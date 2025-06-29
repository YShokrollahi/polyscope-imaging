

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
