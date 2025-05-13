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
    && dnf install -y tar php php-cli php-common php-mbstring php-pdo php-sodium php-tidy php-xml php-ldap php-intl php-geos php-gd php-pgsql git wget openldap bzip2 unzip zip vim postgresql17\
    && dnf update -y \
    && dnf clean all -y

ENV GOSU_VERSION 1.17
RUN set -eux; \
	\
	rpmArch="$(rpm --query --queryformat='%{ARCH}' rpm)"; \
	case "$rpmArch" in \
		aarch64) dpkgArch='arm64' ;; \
		armv[67]*) dpkgArch='armhf' ;; \
		i[3456]86) dpkgArch='i386' ;; \
		ppc64le) dpkgArch='ppc64el' ;; \
		riscv64 | s390x) dpkgArch="$rpmArch" ;; \
		x86_64) dpkgArch='amd64' ;; \
		*) echo >&2 "error: unknown/unsupported architecture '$rpmArch'"; exit 1 ;; \
	esac; \
	wget -O /usr/local/bin/gosu "https://github.com/tianon/gosu/releases/download/$GOSU_VERSION/gosu-$dpkgArch"; \
	wget -O /usr/local/bin/gosu.asc "https://github.com/tianon/gosu/releases/download/$GOSU_VERSION/gosu-$dpkgArch.asc"; \
	\
# verify the signature
	export GNUPGHOME="$(mktemp -d)"; \
	gpg --batch --keyserver hkps://keys.openpgp.org --recv-keys B42F6819007F00F88E364FD4036A9C25BF357DD4; \
	gpg --batch --verify /usr/local/bin/gosu.asc /usr/local/bin/gosu; \
	gpgconf --kill all; \
	rm -rf "$GNUPGHOME" /usr/local/bin/gosu.asc; \
	\
	chmod +x /usr/local/bin/gosu; \
# verify that the binary works
	gosu --version; \
	gosu nobody true

# Install Symfony, Node.js, npm, Yarn, Composer, and configure php.ini
RUN wget https://get.symfony.com/cli/installer -O - | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony \
    && curl -sL https://rpm.nodesource.com/setup_lts.x | bash - \
    && dnf install -y nodejs \
    && npm install -g yarn@1.22.17 \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && sed -i 's/memory_limit = 128M/memory_limit = 2048M/g' /etc/php.ini \
    && sed -i 's/max_input_time = 60/max_input_time = 1800/g' /etc/php.ini \
    && sed -i 's/max_execution_time = 30/max_execution_time = 1800/g' /etc/php.ini \
    && sed -i 's/post_max_size = 8M/post_max_size = 100M/g' /etc/php.ini \
    && sed -i 's/max_file_uploads = 20/max_file_uploads = 1000/g' /etc/php.ini \
    && sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 11M/g' /etc/php.ini \
    && sed -i 's/;date.timezone =/date.timezone = America\/Chicago/g' /etc/php.ini \
    && mkdir -p /san/data/store /san/home/upload/chunks /san/home/upload/files /san/home/http/download \
    && chmod 777 /san/data/store /san/home/upload/chunks /san/home/upload/files /san/data/store \
    && groupadd -r devuser \
    && useradd -r -g devuser -d /opt/pelagos -s /bin/bash devuser

