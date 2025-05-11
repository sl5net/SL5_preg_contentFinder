FROM php:8.3-cli

# Argumente für Benutzer-ID und Gruppen-ID (vom docker build Befehl)
ARG USER_ID
ARG GROUP_ID

# Umgebungsvariablen für Namen und Standard-IDs, falls ARGs nicht gesetzt sind
ENV APP_USER_NAME=appuser
ENV APP_GROUP_NAME=appgroup
ENV APP_UID=${USER_ID:-1000}
# Default UID 1000, wenn USER_ID nicht übergeben wird
ENV APP_GID=${GROUP_ID:-1000}
# Default GID 1000, wenn GROUP_ID nicht übergeben wird

# Zeitzone setzen
ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Systemabhängigkeiten installieren
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    zip \
    unzip \
    libzip-dev \
    zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP Extensions installieren
RUN docker-php-ext-install zip

# Gruppe und Benutzer erstellen, BEVOR sie für Berechtigungen benötigt werden
# Verwende die ENV-Variablen, die die Defaults oder die ARGs enthalten
# RUN addgroup --gid "$APP_GID" "$APP_GROUP_NAME" || true
# || true, falls Gruppe mit dieser GID/Namen schon existiert

RUN if ! getent group $APP_GROUP_NAME > /dev/null; then \
        addgroup --gid $APP_GID $APP_GROUP_NAME; \
    else \
        echo "Group $APP_GROUP_NAME already exists"; \
    fi


RUN adduser --uid "$APP_UID" --gid "$APP_GID" --disabled-password --gecos "" "$APP_USER_NAME" || true
# || true, falls User mit dieser UID/Namen schon existiert

# Gruppe erstellen, falls sie nicht existiert (mit der dynamischen GID)
RUN if ! getent group $APP_GROUP_NAME > /dev/null; then \
        addgroup --gid $APP_GID $APP_GROUP_NAME; \
    else \
        # Wenn Gruppe existiert, aber mit anderer GID, könnte man sie hier anpassen (komplexer)
        # Fürs Erste: Gruppe existiert, wir nehmen an, es passt oder der User wird ihr später hinzugefügt
        echo "Group $APP_GROUP_NAME already exists"; \
    fi

# Benutzer erstellen, falls er nicht existiert (mit der dynamischen UID und GID)
RUN if ! id -u $APP_USER_NAME > /dev/null 2>&1; then \
        adduser -u $APP_UID -G $APP_GROUP_NAME -s /bin/sh -D $APP_USER_NAME; \
    else \
        echo "User $APP_USER_NAME already exists"; \
    fi



# Composer installieren
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Arbeitsverzeichnis setzen
WORKDIR /app

# Log-Verzeichnis erstellen (Besitzer wird später gesetzt)
RUN mkdir -p /app/logs

# Zuerst Composer-Dateien kopieren, um Docker-Layer-Caching zu nutzen
COPY composer.json composer.lock* ./
# Berechtigungen für Composer-Cache und vendor-Ordner (falls Composer als non-root läuft)
# Wenn composer install als root läuft (wie hier implizit), ist das nicht zwingend nötig,
# aber der vendor-Ordner muss später dem APP_USER_NAME gehören.
# Optional: RUN mkdir -p /home/$APP_USER_NAME/.composer && chown -R $APP_USER_NAME:$APP_GROUP_NAME /home/$APP_USER_NAME/.composer

# Composer-Abhängigkeiten installieren (läuft als root)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader -vvv

# Den Rest des Anwendungscodes kopieren (läuft als root)
COPY . .

# Git safe directory Konfiguration (als root, da global)
RUN git config --global --add safe.directory /app

# Setze Berechtigungen für die gesamte Anwendung und das Log-Verzeichnis
# Dies geschieht, nachdem alles kopiert und installiert wurde.
RUN chown -R "$APP_USER_NAME":"$APP_GROUP_NAME" /app
# Spezifische Rechte für Logs, falls abweichend benötigt, aber chown auf /app deckt /app/logs schon ab.
# chmod hier, um sicherzustellen, dass der Benutzer auch schreiben kann.
RUN chmod -R ug+rwx /app/logs

# Wechsle zum Anwendungsbenutzer für die Ausführung des Containers
USER "$APP_USER_NAME"

# Standardbefehl
CMD ["php", "-v"]
