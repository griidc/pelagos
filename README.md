# Pelagos [![PHP Unit Test](https://github.com/griidc/pelagos/actions/workflows/phpunit.yml/badge.svg)](https://github.com/griidc/pelagos/actions/workflows/phpunit.yml) [![PHP Code Sniffer](https://github.com/griidc/pelagos/actions/workflows/phpcs.yml/badge.svg)](https://github.com/griidc/pelagos/actions/workflows/phpcs.yml) [![ESLint for Javascript](https://github.com/griidc/pelagos/actions/workflows/eslint.yml/badge.svg)](https://github.com/griidc/pelagos/actions/workflows/eslint.yml)

Pelagos is a system for maintaining a repository of scientific research data.
Developed and maintained by GRIIDC.
URL: https://griidc.org
## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

* [Redhat Linux 8](https://www.redhat.com/) - Redhat Linux or compatible
* [ClamAV](https://www.clamav.net) - ClamAV® open source antivirus engine
* [PHP 8.1+](http://php.net/docs.php) - General-purpose scripting language
* [PostgreSQL 13+](https://www.postgresql.org) - ORDBMS
* [Elasticsearch 7.17+](https://www.elastic.co/products/elasticsearch) - ElasticSearch Document Indexer
* [Composer 2.5.1+](https://getcomposer.org/) - Dependency Manager for PHP
* [Yarn 1.22.19+](https://yarnpkg.com/en/) - Package Manager

### Installation

Pelagos is a [Symfony 5.4+](https://symfony.com) project, please follow the normal configuration regarding setting up your webserver for a Symfony project.

To install fullfill prerequisites then run:
* `composer install`
* `yarn install`
* `yarn build`

## Contributors

See the list of [contributors](https://github.com/griidc/pelagos/contributors) who participated in this project.

## License
Copyright © 2024 Texas A&M University-Corpus Christi

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this
list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright notice,
this list of conditions and the following disclaimer in the documentation and/or
other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
