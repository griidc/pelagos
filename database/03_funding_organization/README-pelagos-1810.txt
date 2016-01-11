Instructions to apply this to the gomri database as user postgres.

Disable constraints that reference the funding_organization_table, which will
be dropped and re-created.

SQL> ALTER TABLE funding_cycle_table NOCHECK CONSTRAINT fk_funding_cycle_funding_organization;
SQL> ALTER TABLE person2funding_organization2role_table NOCHECK CONSTRAINT fk_person2funding_organization2role_fo_number;

Run each of the 4 table scripts in order, also as postgres/gomri.

$bash> psql -U postgres gomri < database/03_funding_organization/01_make_funding_organization_table.sql
$bash> psql -U postgres gomri < database/03_funding_organization/02_make_email2funding_organization.sql
$bash> psql -U postgres gomri < database/03_funding_organization/03_make_funding_organization_history_table.sql
$bash> psql -U postgres gomri < database/03_funding_organization/04_make_funding_organization_view.sql

Re-enable constraints previously disabled.

SQL> ALTER TABLE funding_cycle_table CHECK CONSTRAINT fk_funding_cycle_funding_organization;
SQL> ALTER TABLE person2funding_organization2role_table CHECK CONSTRAINT fk_person2funding_organization2role_fo_number;
