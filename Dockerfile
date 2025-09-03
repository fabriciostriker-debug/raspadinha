FROM php:apache

# Instala extensões do PHP necessárias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilita o módulo rewrite, se necessário
RUN a2enmod rewrite
