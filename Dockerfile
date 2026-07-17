FROM php:8.2-apache

# Instala dependencias del sistema, la extensión pdo_pgsql (para Supabase/Postgres)
# y curl (necesaria para llamar a la API de OpenAI desde el generador de imágenes y el chatbot)
RUN apt-get update && apt-get install -y libpq-dev libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copia todo el proyecto al contenedor
COPY . /var/www/html/

# Tu app usa /public como punto de entrada (public/index.php), así que apuntamos
# el DocumentRoot de Apache ahí en vez de a /var/www/html
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}/!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Permisos para la carpeta de uploads (por si subes imágenes de productos)
RUN mkdir -p /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html/public/uploads
EXPOSE 80
