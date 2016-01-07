-- -----------------------------------------------------------------------------
-- Name:      fix_hash_data_types.sql
-- Author:    Patrick N. Krepps Jr.
-- Date:      16 December 2015
-- Purpose    This script modifies several domain types constrained to common
--            cryptographic hash strings. Constraint modifications require the
--            domain constraint to be dropped and then redefined, so this script
--            is just a series of ALTER DOMAIN statements.
--            The original constraint regular expressions were not anchored
--            properly at the end. In addition, the sha256 constraint only
--            required a string beginning with 32 hex characters. Those issues
--            are resolved by this script.
-- Inputs:    NONE
-- Outputs:   NONE
-- -----------------------------------------------------------------------------
-- TODO:      
-- -----------------------------------------------------------------------------
-- DONE:
-- -----------------------------------------------------------------------------
ALTER DOMAIN HASH_TYPE
   DROP CONSTRAINT CHK_HASH;

ALTER DOMAIN HASH_TYPE
   ADD CONSTRAINT CHK_HASH
       CHECK(VALUE ~* '^[a-f0-9]{32}$' 
             OR
             VALUE ~* '^[a-f0-9]{40}$'
             OR
             VALUE ~* '^[a-f0-9]{64}$'
             OR
             VALUE ~* '^[a-f0-9]{128}$');

ALTER DOMAIN MD5_HASH_TYPE
   DROP CONSTRAINT CHK_MD5;

ALTER DOMAIN MD5_HASH_TYPE
   ADD CONSTRAINT CHK_MD5
       CHECK(VALUE ~* '^[a-f0-9]{32}$');

ALTER DOMAIN SHA1_HASH_TYPE
   DROP CONSTRAINT CHK_SHA1;

ALTER DOMAIN SHA1_HASH_TYPE
   ADD CONSTRAINT CHK_SHA1
       CHECK(VALUE ~* '^[a-f0-9]{40}$');


ALTER DOMAIN SHA256_HASH_TYPE
   DROP CONSTRAINT CHK_SHA256;

ALTER DOMAIN SHA256_HASH_TYPE
   ADD CONSTRAINT CHK_SHA256
       CHECK(VALUE ~* '^[a-f0-9]{64}$');
