-- Let's get things set up:
-- PASS
TRUNCATE TABLE email2research_group_table;
-- END

-- PASS
DELETE
FROM research_group_table;
-- END

-- PASS
TRUNCATE TABLE research_group_history_table;
-- END

-- PASS
DELETE
FROM email_table
WHERE email_address = 'newrg_user123@example.com';
-- END

-- PASS
DELETE
FROM funding_cycle
WHERE name = 'fc_pnk';
-- END

-- PASS
DELETE
FROM funding_organization
WHERE name = 'fo_pnk';
-- END

-- PASS
INSERT INTO funding_organization
(
   funding_organization_number,
   administrative_area,
   city,
   country,
   creator,
   delivery_point,
   description,
   logo,
   modifier,
   modification_time,
   name,
   phone_number,
   postal_code,
   website,
   email_address
)
VALUES
(
   NULL,
   'Denial',
   'Any City',
   'USA',
   'superman',
   '123 Lazy Lane',
   'Test insert should pass',
   E'xDEADBEEF',
   'superman',
   NULL,
   'fo_pnk',
   '1-800-867-5309',
   '12345-6789',
   'http://www.nosuchpage.example.com',
   'bad_address@example.com'
);
-- END

-- PASS
INSERT INTO funding_cycle_table
(
   funding_organization_number,
   funding_cycle_creation_time,
   funding_cycle_creator,
   funding_cycle_description,
   funding_cycle_end_date,
   funding_cycle_modification_time,
   funding_cycle_modifier,
   funding_cycle_name,
   funding_cycle_start_date,
   funding_cycle_website
)
VALUES
(
   (SELECT funding_organization_number
    FROM funding_organization
    WHERE name = 'fo_pnk'),
   NOW(),
   'superman',
   'dummy data',
   '2015-12-31',
   NOW(),
   'superman',
   'fc_pnk',
   '2015-01-01',
   'http://www.example.com'
);
-- END

-- Make sure the research group table exists:
-- PASS
SELECT COUNT(*)
FROM research_group;
-- END

-- Make sure the funding cycle view exists:
-- PASS
SELECT COUNT(*)
FROM funding_cycle;
-- END

-- Fail because of NULL creator:
-- FAIL
INSERT INTO research_group
(
   name,
   description,
   creation_time,
   creator,
   funding_cycle_number,
   phone_number,
   email_address,
   website,
   delivery_point,
   city,
   administrative_area,
   country,
   postal_code,
   logo,
   modifier,
   modification_time
)
VALUES
(
   'RG1',
   'Test research group 1',
   '2016-05-20',
   NULL,
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'fc_pnk'),
   '512-555-1212',
   'rg_user123@example.com',
   'www.example.com',
   '123 Lazy Lane',
   'Anytown',
   'Your State',
   'USA',
   '12345-6789',
   E'xDEADBEEF',
   'dadams',
   '2015-08-01'
);
-- END

-- Fail because of empty creator:
-- FAIL
INSERT INTO research_group
(
   name,
   description,
   creation_time,
   creator,
   funding_cycle_number,
   phone_number,
   email_address,
   website,
   delivery_point,
   city,
   administrative_area,
   country,
   postal_code,
   logo,
   modifier,
   modification_time
)
VALUES
(
   'RG1',
   'Test research group 1',
   '2016-05-20',
   '',
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'fc_pnk'),
   '512-555-1212',
   'rg_user123@example.com',
   'www.example.com',
   '123 Lazy Lane',
   'Anytown',
   'Your State',
   'USA',
   '12345-6789',
   E'xDEADBEEF',
   'dadams',
   '2015-08-01'
);
-- END

-- Fail because of NULL funding_cycle:
-- FAIL
INSERT INTO research_group
(
   name,
   description,
   creation_time,
   creator,
   funding_cycle_number,
   phone_number,
   email_address,
   website,
   delivery_point,
   city,
   administrative_area,
   country,
   postal_code,
   logo,
   modifier,
   modification_time
)
VALUES
(
   'RG1',
   'Test research group 1',
   '2016-05-20',
   'Marvin',
   NULL,
   '512-555-1212',
   'rg_user123@example.com',
   'www.example.com',
   '123 Lazy Lane',
   'Anytown',
   'Your State',
   'USA',
   '12345-6789',
   E'xDEADBEEF',
   'dadams',
   '2015-08-01'
);
-- END

-- Fail because of empty funding_cycle:
-- FAIL
INSERT INTO research_group
(
   name,
   description,
   creation_time,
   creator,
   funding_cycle_number,
   phone_number,
   email_address,
   website,
   delivery_point,
   city,
   administrative_area,
   country,
   postal_code,
   logo,
   modifier,
   modification_time
)
VALUES
(
   'RG1',
   'Test research group 1',
   '2016-05-20',
   'Marvin',
   '',
   '512-555-1212',
   'rg_user123@example.com',
   'www.example.com',
   '123 Lazy Lane',
   'Anytown',
   'Your State',
   'USA',
   '12345-6789',
   E'xDEADBEEF',
   'dadams',
   '2015-08-01'
);
-- END

-- Fail because of NULL name:
-- FAIL
INSERT INTO research_group
(
   name,
   description,
   creation_time,
   creator,
   funding_cycle_number,
   phone_number,
   email_address,
   website,
   delivery_point,
   city,
   administrative_area,
   country,
   postal_code,
   logo,
   modifier,
   modification_time
)
VALUES
(
   NULL,
   'Test research group 1',
   '2016-05-20',
   'Marvin',
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'fc_pnk'),
   '512-555-1212',
   'rg_user123@example.com',
   'www.example.com',
   '123 Lazy Lane',
   'Anytown',
   'Your State',
   'USA',
   '12345-6789',
   E'xDEADBEEF',
   'dadams',
   '2015-08-01'
);
-- END

-- Fail because of empty name:
-- FAIL
INSERT INTO research_group
(
   name,
   description,
   creation_time,
   creator,
   funding_cycle_number,
   phone_number,
   email_address,
   website,
   delivery_point,
   city,
   administrative_area,
   country,
   postal_code,
   logo,
   modifier,
   modification_time
)
VALUES
(
   '',
   'Test research group 1',
   '2016-05-20',
   'Marvin',
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'fc_pnk'),
   '512-555-1212',
   'rg_user123@example.com',
   'www.example.com',
   '123 Lazy Lane',
   'Anytown',
   'Your State',
   'USA',
   '12345-6789',
   E'xDEADBEEF',
   'dadams',
   '2015-08-01'
);
-- END

-- Fail because of invalid funding cycle number:
-- FAIL
INSERT INTO research_group
(
   name,
   description,
   creation_time,
   creator,
   funding_cycle_number,
   phone_number,
   email_address,
   website,
   delivery_point,
   city,
   administrative_area,
   country,
   postal_code,
   logo,
   modifier,
   modification_time
)
VALUES
(
   'RG1',
   'Test research group 1',
   '2016-05-20',
   'Marvin',
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'no_such_name_hopefully'),
   '512-555-1212',
   'rg_user123@example.com',
   'www.example.com',
   '123 Lazy Lane',
   'Anytown',
   'Your State',
   'USA',
   '12345-6789',
   E'xDEADBEEF',
   'dadams',
   '2015-08-01'
);
-- END

-- Fail because of invalid email address:
-- FAIL
INSERT INTO research_group
(
   name,
   description,
   creation_time,
   creator,
   funding_cycle_number,
   phone_number,
   email_address,
   website,
   delivery_point,
   city,
   administrative_area,
   country,
   postal_code,
   logo,
   modifier,
   modification_time
)
VALUES
(
   'RG1',
   'Test research group 1',
   '2016-05-20',
   'Marvin',
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'fc_pnk'),
   '512-555-1212',
   'rg_user123',
   'www.example.com',
   '123 Lazy Lane',
   'Anytown',
   'Your State',
   'USA',
   '12345-6789',
   E'xDEADBEEF',
   'dadams',
   '2015-08-01'
);
-- END

-- Pass and create new email address:
-- PASS
INSERT INTO research_group
(
   name,
   description,
   creation_time,
   creator,
   funding_cycle_number,
   phone_number,
   email_address,
   website,
   delivery_point,
   city,
   administrative_area,
   country,
   postal_code,
   logo,
   modifier,
   modification_time
)
VALUES
(
   'RG_A',
   'Test research group 1',
   '2016-05-20',
   'Marvin',
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'fc_pnk'),
   '512-555-1212',
   'newrg_user123@example.com',
   'www.example.com',
   '123 Lazy Lane',
   'Anytown',
   'Your State',
   'USA',
   '12345-6789',
   E'xDEADBEEF',
   'dadams',
   '2015-08-01'
);
-- END

-- Test for that email insertion by trying to delete it. Should fail because of
-- a foreign key violation:
-- FAIL
DELETE
FROM email_table
WHERE email_address = newrg_user123@example.com';
-- END

-- Pass and use existing email address:
-- PASS
INSERT INTO research_group
(
   name,
   description,
   creation_time,
   creator,
   funding_cycle_number,
   phone_number,
   email_address,
   website,
   delivery_point,
   city,
   administrative_area,
   country,
   postal_code,
   logo,
   modifier,
   modification_time
)
VALUES
(
   'RG_B',
   'Test research group 1',
   '2016-05-20',
   'Marvin',
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'fc_pnk'),
   '512-555-1212',
   'newrg_user123@example.com',
   'www.example.com',
   '123 Lazy Lane',
   'Anytown',
   'Your State',
   'USA',
   '12345-6789',
   E'xDEADBEEF',
   'dadams',
   '2015-08-01'
);
-- END

-- Pass using existing name but different FC number:
-- PASS
INSERT INTO research_group
(
   name,
   description,
   creation_time,
   creator,
   funding_cycle_number,
   phone_number,
   email_address,
   website,
   delivery_point,
   city,
   administrative_area,
   country,
   postal_code,
   logo,
   modifier,
   modification_time
)
VALUES
(
   'RG_A',
   'Test research group 1',
   '2016-05-20',
   'Marvin',
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name <> 'fc_pnk'
    ORDER BY RANDOM()
    LIMIT 1),
   '512-555-1212',
   'anothernewrg_user123@example.com',
   'www.example.com',
   '123 Lazy Lane',
   'Anytown',
   'Your State',
   'USA',
   '12345-6789',
   E'xDEADBEEF',
   'dadams',
   '2015-08-01'
);
-- END

-- Pass because of good insert:
-- PASS
INSERT INTO research_group
(
   name,
   description,
   creation_time,
   creator,
   funding_cycle_number,
   phone_number,
   email_address,
   website,
   delivery_point,
   city,
   administrative_area,
   country,
   postal_code,
   logo,
   modifier,
   modification_time
)
VALUES
(
   'RG1',
   'Test research group 1',
   '2016-05-20',
   'Marvin',
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'fc_pnk'),
   '512-555-1212',
   'rg_user123@example.com',
   'www.example.com',
   '123 Lazy Lane',
   'Anytown',
   'Your State',
   'USA',
   '12345-6789',
   E'xDEADBEEF',
   'dadams',
   '2015-08-01'
);
-- END

-- Fail because of duplicate name / funding_cycle_number:
-- FAIL
INSERT INTO research_group
(
   name,
   description,
   creation_time,
   creator,
   funding_cycle_number,
   phone_number,
   email_address,
   website,
   delivery_point,
   city,
   administrative_area,
   country,
   postal_code,
   logo,
   modifier,
   modification_time
)
VALUES
(
   'RG1',
   'Test research group 1',
   '2016-05-20',
   'Marvin',
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'fc_pnk'),
   '512-555-1212',
   'anothernewrg_user123@example.com',
   'www.example.com',
   '123 Lazy Lane',
   'Anytown',
   'Your State',
   'USA',
   '12345-6789',
   E'xDEADBEEF',
   'dadams',
   '2015-08-01'
);
-- END

-- Pass with bare minimum required:
-- PASS
INSERT INTO research_group
(
   name,
   creator,
   funding_cycle_number
)
VALUES
(
   'minimum',
   'Philip Glass',
   (SELECT funding_cycle_number
    FROM funding_cycle
    ORDER BY RANDOM()
    LIMIT 1)
);
-- END

-- UPDATE setting funding cycle to NULL should fail:
-- FAIL
UPDATE research_group
SET funding_cycle_number = NULL
WHERE name = (SELECT research_group_name
              FROM research_group_table
              ORDER BY RANDOM()
              LIMIT 1);
-- END

-- UPDATE setting funding cycle to empty string should fail (actually this one
-- fails because '' is an invalid data type for funding_cycle_number, so this
-- query doesn't even make it to the point where the view's trigger is fired):
-- FAIL
UPDATE research_group
SET funding_cycle_number = ''
WHERE name = (SELECT research_group_name
              FROM research_group_table
              ORDER BY RANDOM()
              LIMIT 1);
-- END

-- UPDATE setting funding cycle to an invalid number should fail:
-- FAIL
UPDATE research_group
SET funding_cycle_number = (SELECT funding_cycle_number
                            FROM funding_cycle
                            WHERE name = 'no_such_name_hopefully')
WHERE name = (SELECT research_group_name
              FROM research_group_table
              ORDER BY RANDOM()
              LIMIT 1);
-- END

-- UPDATE setting modifier to NULL should fail:
-- FAIL
UPDATE research_group
SET modifier = NULL
WHERE name = (SELECT research_group_name
              FROM research_group_table
              ORDER BY RANDOM()
              LIMIT 1);
-- END

-- UPDATE setting modifier to empty string should fail:
-- FAIL
UPDATE research_group
SET modifier = ''
WHERE name = (SELECT research_group_name
              FROM research_group_table
              ORDER BY RANDOM()
              LIMIT 1);
-- END

-- UPDATE name and funding cycle to an existing set should fail:
-- FAIL
UPDATE research_group
SET funding_cycle_number = (SELECT funding_cycle_number
                            FROM research_group
                            WHERE name = 'minimum'),
   name = 'minimum'
WHERE name = 'RG_B';
-- END

-- UPDATE email address to an invalid email address should fail:
-- FAIL
UPDATE research_group
SET email_address = 'bad_address'
WHERE research_group_number = (SELECT research_group_number
                               FROM research_group
                               WHERE name = 'minimum');
-- END

-- UPDATE email address to a new email address should pass:
-- PASS
UPDATE research_group
SET email_address = 'another_good_address_hopefully@example.com'
WHERE research_group_number = (SELECT research_group_number
                               FROM research_group
                               WHERE name = 'minimum');
-- END

-- UPDATE email address to a different email address should pass:
-- PASS
UPDATE research_group
SET email_address = 'yet_another_good_address_hopefully@example.com'
WHERE research_group_number = (SELECT research_group_number
                               FROM research_group
                               WHERE name = 'minimum');
-- END

-- UPDATE email address to an existing email address should pass:
-- PASS
UPDATE research_group
SET email_address = 'another_good_address_hopefully@example.com'
WHERE research_group_number = (SELECT research_group_number
                               FROM research_group
                               WHERE name = 'minimum');
-- END

-- DELETE should pass (not too sure how to make a DELETE fail though):
-- PASS
DELETE
FROM research_group
WHERE name = 'RG1';
-- END
