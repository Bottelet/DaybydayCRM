FROM phpmyadmin/phpmyadmin:5.2.2

LABEL maintainer="Bo-Yi Wu <appleboy.tw@gmail.com>"

# Add volume for sessions to allow session persistence
VOLUME /sessions

COPY header.twig /var/www/html/themes/templates/header.twig
# Copy your override
COPY metro-font-override.css /var/www/html/themes/metro/css/custom-fonts.css
COPY fonts/FiraCode-Regular.woff2 /var/www/html/themes/metro/css/fonts/FiraCode-Regular.woff2

# Inject link into theme
RUN echo '<link rel="stylesheet" type="text/css" href="themes/metro/css/custom-fonts.css">' \
     >> /var/www/html/themes/metro/layout.inc.php

# We expose phpMyAdmin on port 80
EXPOSE 80

