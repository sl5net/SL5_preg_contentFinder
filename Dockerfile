FROM php:8.3-cli
ARG USER_ID
ARG GROUP_ID
ENV APP_USER_NAME=appuser
ENV APP_GROUP_NAME=appgroup
ENV APP_UID=${USER_ID:-1000}
ENV APP_GID=${GROUP_ID:-1000}

ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    zip \
    unzip \
    libzip-dev \
    zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install zip
RUN if ! getent group $APP_GROUP_NAME > /dev/null; then \
        addgroup --gid $APP_GID $APP_GROUP_NAME; \
    else \
        echo "Group $APP_GROUP_NAME already exists"; \
    fi
RUN adduser --uid "$APP_UID" --gid "$APP_GID" --disabled-password --gecos "" "$APP_USER_NAME" || true
RUN if ! getent group $APP_GROUP_NAME > /dev/null; then \
        addgroup --gid $APP_GID $APP_GROUP_NAME; \
    else \
        echo "Group $APP_GROUP_NAME already exists"; \
    fi
RUN if ! id -u $APP_USER_NAME > /dev/null 2>&1; then \
        adduser -u $APP_UID -G $APP_GROUP_NAME -s /bin/sh -D $APP_USER_NAME; \
    else \
        echo "User $APP_USER_NAME already exists"; \
    fi

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

RUN mkdir -p /app/logs

COPY composer.json composer.lock* ./

RUN composer install --no-interaction --prefer-dist --optimize-autoloader -vvv

COPY . .

RUN git config --global --add safe.directory /app
RUN chown -R "$APP_USER_NAME":"$APP_GROUP_NAME" /app
RUN chmod -R ug+rwx /app/logs
USER "$APP_USER_NAME"
CMD ["php", "-v"]
