Instructions to apply this to the gomri database as user postgres.

Query and save constraint definitions for later re-application.

    * fk_funding_cycle_funding_organization on funding_cycle_table
    * fk_person2funding_organization2role_fo_number on person2funding_organization2role_table

    SQL> select pg_get_constraintdef(pg_constraint.oid) from pg_constraint
         where pg_constraint.conname = 'fk_funding_cycle_funding_organization';

example output:
FOREIGN KEY (funding_organization_number) REFERENCES funding_organization_table(funding_organization_number) ON UPDATE RESTRICT ON DELETE RESTRICT

    SQL> select pg_get_constraintdef(pg_constraint.oid) from pg_constraint
         where pg_constraint.conname = 'fk_person2funding_organization2role_fo_number';

example output:
FOREIGN KEY (funding_organization_number) REFERENCES funding_organization_table(funding_organization_number) ON UPDATE RESTRICT ON DELETE RESTRICT

Drop the constraints.

SQL> ALTER TABLE funding_cycle_table DROP CONSTRAINT fk_funding_cycle_funding_organization;
SQL> ALTER TABLE person2funding_organization2role_table DROP CONSTRAINT fk_person2funding_organization2role_fo_number;

Run each of the 4 table scripts in order, also as postgres/gomri.

$bash> psql -U postgres gomri < database/03_funding_organization/01_make_funding_organization_table.sql
$bash> psql -U postgres gomri < database/03_funding_organization/02_make_email2funding_organization.sql
$bash> psql -U postgres gomri < database/03_funding_organization/03_make_funding_organization_history_table.sql
$bash> psql -U postgres gomri < database/03_funding_organization/04_make_funding_organization_view.sql

Re-apply the constraints using the following.

ALTER TABLE <TABLE NAME> ADD CONSTRAINT <CONSTRAINT NAME> <SAVED TEXT>

---------------- THIS STATEMENT CURRENTLY FAILS -----------------------
example usage:
SQL> ALTER TABLE funding_cycle_table
     ADD CONSTRAINT fk_funding_cycle_funding_organization
     FOREIGN KEY (funding_organization_number)
     REFERENCES funding_organization_table(funding_organization_number)
     ON UPDATE RESTRICT ON DELETE RESTRICT;
-----------------------------------------------------------------------

SQL> ALTER TABLE person2funding_organization2role_table
     ADD CONSTRAINT fk_person2funding_organization2role_fo_number
     FOREIGN KEY (funding_organization_number)
     REFERENCES funding_organization_table(funding_organization_number)
     ON UPDATE RESTRICT ON DELETE RESTRICT;
