# mariadb -u root -p -e "CREATE DATABASE IF NOT EXISTS cafeteria;"
# mariadb -u root -p cafeteria < database/cafeteria.sql

# mariadb -u root -p -e "ALTER USER 'root'@'localhost' IDENTIFIED BY ''; FLUSH PRIVILEGES;"

# php -S localhost:8080 -t .

# mariadb -u root -e "DROP DATABASE IF EXISTS cafeteria; CREATE DATABASE cafeteria;"
# mariadb -u root cafeteria < database/cafeteria.sql
pkill -f "php -S localhost:8080" || true
php -S localhost:8080 -t .

