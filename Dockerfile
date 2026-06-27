FROM php:8.2-cli

# Instalar extensión PDO MySQL que requiere tu aplicación
RUN docker-php-ext-install pdo pdo_mysql

# Configurar el directorio de trabajo
WORKDIR /app

# Copiar el código de la aplicación
COPY . /app

# Comando de inicio usando nuestro router seguro y apuntando a public
CMD php -S 0.0.0.0:$PORT -t public router.php
