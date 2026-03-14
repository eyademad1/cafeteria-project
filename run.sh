mariadb -u root -p -e "CREATE DATABASE IF NOT EXISTS cafeteria;"
mariadb -u root -p cafeteria < database/cafeteria.sql

mariadb -u root -p -e "ALTER USER 'root'@'localhost' IDENTIFIED BY ''; FLUSH PRIVILEGES;"

php -S localhost:8080 -t .
