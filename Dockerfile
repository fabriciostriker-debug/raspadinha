FROM php:apache

# Instala extensões do PHP necessárias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilita o módulo rewrite, se necessário
RUN a2enmod rewrite

# Define o diretório de trabalho
WORKDIR /var/www/html

# Dá permissão total para todo o projeto
RUN chmod -R 777 /var/www/html

# Ajusta o usuário do Apache para evitar conflitos de permissão
RUN chown -R www-data:www-data /var/www/html

# Criando pasta de logs
RUN mkdir -p /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html/logs \
    && chmod -R 755 /var/www/html/logs
