# Dockerfile für PHP 7.4 Umgebung

# FROM php:7.4-cli
FROM php:8.1-cli

# Setze die Zeitzone (optional, um Warnungen zu vermeiden)
ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Arbeitsverzeichnis (kann auch später gesetzt werden)
WORKDIR /app

# Systemabhängigkeiten installieren
# Wichtig: Prüfen, ob Debian Buster (Basis von php:7.4-cli) noch Anpassungen für apt-Quellen braucht
# Wahrscheinlich nicht mehr so kritisch wie bei Stretch (PHP 5.6), aber ggf. prüfen.
# Für den Moment gehen wir davon aus, dass die Standardquellen funktionieren.
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    zip \
    unzip \
    libzip-dev \
    zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP Extensions installieren (Beispiel für zip, falls benötigt)
RUN docker-php-ext-install zip

# Composer installieren
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock* ./

# Abhängigkeiten installieren (inklusive dev-Abhängigkeiten für Tests)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader -vvv

# Den Rest des Codes kopieren
COPY . .

# Optional: Berechtigungen setzen, falls nötig
# RUN chown -R www-data:www-data /app

# Standardbefehl (optional)
CMD ["php", "-v"]
