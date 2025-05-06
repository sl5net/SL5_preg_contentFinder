# Verwende ein offizielles PHP 5.6 CLI Image
FROM php:5.6-cli

# PASSE QUELLEN FÜR ALTES DEBIAN (Stretch) AN
# Ersetze die Standard-Quellen durch die Archiv-Quellen, da Stretch EOL ist.
RUN sed -i \
    -e 's/deb.debian.org/archive.debian.org/g' \
    -e 's|security.debian.org/debian-security|archive.debian.org/debian-security|g' \
    -e '/stretch-updates/d' \
    /etc/apt/sources.list

# Installiere Werkzeuge: wget zum Herunterladen
# Das apt-get update sollte jetzt funktionieren, da es auf das Archiv zeigt.
RUN apt-get update && apt-get install -y --no-install-recommends \
    wget \
    && rm -rf /var/lib/apt/lists/*

# Setze das Arbeitsverzeichnis im Container
WORKDIR /app

# Kopiere den Projektcode in den Container (wird beim Bauen gemacht)
COPY . /app

# Lade eine kompatible PHPUnit 3.7 PHAR-Datei herunter
RUN wget https://phar.phpunit.de/phpunit-3.7.38.phar -O /usr/local/bin/phpunit && \
    chmod +x /usr/local/bin/phpunit

# Standardbefehl (optional)
CMD ["php", "--version"]
