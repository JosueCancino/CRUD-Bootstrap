# Usa una imagen oficial de PHP con Apache
FROM php:8.2-apache

# Habilita extensiones necesarias (como PostgreSQL)
RUN docker-php-ext-install pgsql pdo pdo_pgsql

# Copia tu aplicación al contenedor
COPY . /var/www/html/

# Expone el puerto 80
EXPOSE 80