
# Polyscope without SSL certificate(local drive optimizations)

This repository contains instructions for pulling and running the Polyscope Docker image on different VM machines...

## Docker Image

To pull the latest Polyscope Docker image from the GitHub Container Registry:

```sh
docker pull ghcr.io/idso-fa1-pathology/polyscope:latest
```

## Running Polyscope on VM Machines

### T6 Machine

To run the Polyscope container on a t6 machine with the hostname `roprlpscope01.mdanderson.edu`:

```sh
docker run -d -p 80:80 -p 3306:3306 --hostname roprlpscope01.mdanderson.edu --restart always \
-v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/polyzoomer:/var/www/polyzoomer \
-v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/customers:/var/www/customers \
-v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/media:/media \
--name polyscope-container ghcr.io/idso-fa1-pathology/polyscope:latest
```


## Running Polyscope-Developer on VM Machines

### VM Machine

To run the Polyscope container on a VM machine with the hostname `roprlpscope01.mdanderson.edu`:

```sh
docker run -d -p 80:80 -p 3306:3306 --hostname roprlpscope01.mdanderson.edu --restart always \
-v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/polyzoomer:/var/www/polyzoomer \
-v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/customers:/var/www/customers \
-v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/media:/media \
-v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/user_db:/var/lib/mysql \
--name polyscope-container ghcr.io/idso-fa1-pathology/polyscope-development:latest
```

 or 

```sh
 docker run -d \
  -p 80:80 \
  -p 443:443 \
  -p 3306:3306 \
  --hostname roprlpscope01.mdanderson.edu \
  --restart always \
  -v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/polyzoomer:/var/www/polyzoomer \
  -v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/customers:/var/www/customers \
  -v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/media:/media \
  -v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/user_db:/var/lib/mysql \
  -v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/ssl-certs:/etc/apache2/ssl \
  --name polyscope-container \
  ghcr.io/idso-fa1-pathology/polyscope-development:latest
```

### VM2 Machine(still under developments)

To run the Polyscope container on a VM machine with the hostname `roprlpscope01.mdanderson.edu`:

```sh
docker run -d -p 80:80 -p 3306:3306 --hostname roprlpscope02.mdanderson.edu --restart always \
-v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/polyzoomer:/var/www/polyzoomer \
-v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/customers:/var/www/customers \
-v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/media:/media \
-v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/user_db:/var/lib/mysql \
--name polyscope-container ghcr.io/idso-fa1-pathology/polyscope-development:latest
```

 or 

```sh
 docker run -d \
  -p 80:80 \
  -p 443:443 \
  -p 3306:3306 \
  --hostname roprlpscope02.mdanderson.edu \
  --restart always \
  -v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/polyzoomer:/var/www/polyzoomer \
  -v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/customers:/var/www/customers \
  -v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/media:/media \
  -v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/user_db:/var/lib/mysql \
  -v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/ssl-certs:/etc/apache2/ssl \
  --name polyscope-container \
  ghcr.io/idso-fa1-pathology/polyscope-development:latest
```


### Alex T6

To run the Polyscope container on a machine with the hostname `r3puptmpg02`:

```sh
docker run -d -p 80:80 -p 3306:3306 --hostname r3puptmpg02 --restart always \
-v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/polyzoomer:/var/www/polyzoomer \
-v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/customers:/var/www/customers \
-v /rsrch6/home/trans_mol_path/yuan_lab/polyscope/media:/media \
--name polyscope-container ghcr.io/idso-fa1-pathology/polyscope:latest
```
### Locally for deevelopments
```sh
docker run -d \
  -p 80:80 \
  -p 443:443 \
  -p 3306:3306 \
  --hostname localhost \
  --restart always \
  -v /Users/yshokrollahi/Documents/GitHub/polyscope/src/polyscope-main:/var/www \
  -v /Users/yshokrollahi/Desktop/MD/Polyscope/polyzoomer:/var/www/polyzoomer \
  -v /Users/yshokrollahi/Desktop/MD/Polyscope/customers:/var/www/customers \
  -v /Users/yshokrollahi/Desktop/MD/Polyscope/media:/media \
  -v /Users/yshokrollahi/Desktop/MD/Polyscope/user_db:/var/lib/mysql \
  -v /Users/yshokrollahi/Desktop/MD/Polyscope/ssl-certs:/etc/apache2/ssl \
  --name polyscope-container \
  polyscope
```

# Polyscope User Database

## Introduction

This project sets up the Polyscope application using Docker. It includes instructions to configure and run MySQL within the Docker container.

## Prerequisites

- Docker and Docker Compose installed on your machine.
- Access to the GitHub Container Registry.
- Git installed on your machine.

## Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/idso-fa1-pathology/polyscope-development.git
cd polyscope-development
```

### 2. Authenticate with GitHub Container Registry

Generate a GitHub Personal Access Token (PAT) with `read:packages` scope. Authenticate Docker with the PAT:

```bash
echo YOUR_GITHUB_PAT | docker login ghcr.io -u YOUR_GITHUB_USERNAME --password-stdin
```

### 3. Pull the Docker Image

```bash
docker pull ghcr.io/idso-fa1-pathology/polyscope-development:latest
```

### 4. Run the Docker Container

```bash
docker run -d -p 80:80 -p 3306:3306 --hostname YOUR_HOSTNAME --restart always \
-v /path/to/polyzoomer:/var/www/polyzoomer \
-v /path/to/customers:/var/www/customers \
-v /path/to/media:/media \
-v "/path/to/user_db:/var/lib/mysql" \
--name polyscope-container ghcr.io/idso-fa1-pathology/polyscope-development:latest
```

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
