FROM rockylinux:8-minimal

# Install and enable the REMI, postgresql repositories and necessary packages
RUN microdnf install -y dnf \
    && dnf install -y dnf-utils \
    && dnf install -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm \
    && crb enable \
    && dnf install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm \
    && dnf install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-8-x86_64/pgdg-redhat-repo-latest.noarch.rpm \
    && dnf -qy module disable postgresql \
    && dnf module reset php \
    && dnf module install php:remi-8.3 -y \
    && dnf install -y tar php php-cli php-common php-mbstring php-pdo php-sodium php-tidy php-xml php-ldap php-intl php-geos php-gd php-pgsql git wget openldap bzip2 unzip zip vim postgresql17

# Install Symfony, Node.js, npm, Yarn, and Composer
RUN wget https://get.symfony.com/cli/installer -O - | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony \
    && curl -sL https://rpm.nodesource.com/setup_lts.x | bash - \
    && dnf install -y nodejs \
    && npm install -g yarn@1.22.17 \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
