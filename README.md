# File Share Application

A secure, web-based file-sharing application built with PHP, Apache, and MariaDB, containerized using Docker. The application allows users to browse and download files from a network share, with a modern Bootstrap-based UI, database-driven authentication, and session management. The Docker setup includes an Apache/PHP web server and a MariaDB database, with the network share mounted as a volume.

## Features
- **User Authentication**: Login system with credentials stored in a MariaDB database.
- **Session Management**: Secure session handling with logout functionality.
- **File Browsing**: Lists files from a network share (`/mnt/share`) with search functionality.
- **Modern UI**: Responsive Bootstrap 5 interface with file cards, icons, and hover effects.
- **Clean URLs**: URL rewriting to remove `.php` extensions (e.g., `/login` instead of `/login.php`).
- **Dockerized**: Runs in Docker containers with Apache/PHP and MariaDB services.
- **Network Share Integration**: Mounts a network share as a read-only volume.
- **Configurable Port**: Apache port (80) mapped to a host port via Docker Compose.

## Prerequisites
- **Docker**: Installed on the host system (tested with Docker 20.10+).
- **Docker Compose**: Required for managing multi-container setup (tested with Docker Compose 1.29+).
- **Network Share**: A mounted network share (e.g., NFS/SMB) at `/mnt/share` on the host.
- **Git**: To clone the repository.
- **PHP CLI** (optional): For generating password hashes on the host.

## Project Structure
```
file-share-docker/
├── app/
│   ├── index.php         # Main page with file list and search
│   ├── login.php         # Login page with database authentication
│   ├── logout.php        # Session logout script
│   ├── list_files.php    # PHP script to list files from /mnt/share
│   ├── .htaccess         # Apache URL rewriting rules
├── db-init/
│   ├── init.sql          # SQL script to initialize database and users
├── Dockerfile            # Builds Apache/PHP image
├── apache-config.conf    # Apache virtual host configuration
├── docker-compose.yml    # Defines web and database services
├── README.md             # This file
```

## Setup Instructions

### 1. Clone the Repository
```bash
git clone https://github.com/abemin/file-share-docker.git
cd file-share-docker
```

### 2. Configure the Network Share
Ensure a network share (e.g., NFS/SMB) is mounted at `/mnt/share` on the host:
```bash
ls -l /mnt/share
```
The share should be readable by the `www-data` user (UID 33) inside the container. For NFS, ensure permissions allow access:
```bash
sudo chown -R nobody:nogroup /mnt/share
sudo chmod -R 755 /mnt/share
```

### 3. Configure Database Credentials
Edit `db-init/init.sql` to set the password hash for the default user (`demo_user`):
```sql
INSERT IGNORE INTO users (username, password) VALUES (
    'demo_user',
    '$2y$10$YOUR_HASH_HERE'
);
```
Generate a password hash:
```bash
php -r "echo password_hash('secure_password', PASSWORD_DEFAULT);"
```
Replace `$2y$10$YOUR_HASH_HERE` with the output.

Update `docker-compose.yml` with your database password (optional; defaults to `secure_db_password`):
```yaml
services:
  db:
    environment:
      - MYSQL_PASSWORD=your_secure_db_password
  web:
    environment:
      - DB_PASS=your_secure_db_password
```

### 4. Build and Run Containers
Build the Docker image and start the containers:
```bash
docker-compose build
docker-compose up -d
```

Verify containers are running:
```bash
docker ps
```
You should see `file-share-docker_web_1` and `file-share-docker_db_1`.

### 5. Access the Application
Open a browser and navigate to:
```
http://localhost:8080/login
```
- **Login**: Use `demo_user` and `secure_password` (or your configured credentials).
- **Main Page**: After login, you’ll see the file list at `http://localhost:8080/`.
- **Logout**: Click the "Logout" button to return to the login page.

If using a DNS entry (e.g., `download.lab.demo`), ensure it points to the host and update `apache-config.conf` accordingly.

### 6. Customize the Port
To change the host port (default: 8080), edit `docker-compose.yml`:
```yaml
services:
  web:
    ports:
      - "9090:80"  # Change 9090 to desired port
```
Restart the containers:
```bash
docker-compose down
docker-compose up -d
```

## Usage
- **Login**: Enter credentials at `/login` to access the file list.
- **Browse Files**: View files from the network share with search and download options.
- **Search**: Use the search bar to filter files by name.
- **Logout**: Click the "Logout" button to end the session.
- **Download**: Click "Download" on file cards to retrieve files from `/mnt/share`.

## Configuration Details
- **Web Service**:
  - Image: Custom Apache/PHP 8.1 with `pdo_mysql` and `mod_rewrite`.
  - Port: Maps host port (e.g., 8080) to container port 80.
  - Volume: Mounts `/mnt/share` as `/mnt/share:ro` (read-only).
  - Environment: Sets database connection variables (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`).

- **Database Service**:
  - Image: `mariadb:10.6`.
  - Port: Not exposed to host; accessible internally via `db:3306`.
  - Volume: Persistent `db-data` for database storage; `db-init/` for initialization.
  - Environment: Sets `MYSQL_ROOT_PASSWORD`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`.

- **Network**: Both services use a `bridge` network (`app-network`) for internal communication.

## Troubleshooting
- **Web Container Fails**:
  ```bash
  docker-compose logs web
  ```
  Check Apache configuration:
  ```bash
  docker exec -it file-share-docker_web_1 apache2ctl configtest
  ```

- **Database Connection Fails**:
  ```bash
  docker-compose logs db
  ```
  Verify database initialization:
  ```bash
  docker exec -it file-share-docker_db_1 mysql -u file_share_user -p -e "USE file_share; SHOW TABLES;"
  ```

- **Network Share Inaccessible**:
  Ensure `/mnt/share` is mounted on the host:
  ```bash
  mount | grep /mnt/share
  ```
  Check container access:
  ```bash
  docker exec -it file-share-docker_web_1 ls -l /mnt/share
  ```

- **Login Fails**:
  Verify the password hash in `db-init/init.sql` matches `password_verify()`.
  Check database credentials in `docker-compose.yml`.

- **Port Conflict**:
  Change the host port in `docker-compose.yml` if 8080 is in use.

## Security Considerations
- **Database**: Credentials are set via environment variables. Use Docker secrets for production.
- **Network Share**: Mounted read-only to prevent modifications.
- **Sessions**: `session.cookie_httponly` and `session.use_strict_mode` are enabled in the Docker image.
- **Reverse Proxy**: Configure a reverse proxy (e.g., Nginx) for HTTPS and forward requests to the configured port (e.g., 8080). If using Nginx Proxy Manager, add "add_header Content-Security-Policy "upgrade-insecure-requests" always;" in the custom configuration location. else it will be blocked because of mixed content.
- **CSRF**: Consider adding CSRF protection to `login.php` for production.
- **Password Storage**: Passwords are hashed with bcrypt in the `users` table.


