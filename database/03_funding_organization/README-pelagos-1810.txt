Instructions to apply this to the gomri database as user postgres.

Run each of the 4 table scripts in order, also as postgres/gomri.

$bash> psql -U postgres gomri < database/03_funding_organization/01_make_funding_organization_table.sql
$bash> psql -U postgres gomri < database/03_funding_organization/02_make_email2funding_organization.sql
$bash> psql -U postgres gomri < database/03_funding_organization/03_make_funding_organization_history_table.sql
$bash> psql -U postgres gomri < database/03_funding_organization/04_make_funding_organization_view.sql
