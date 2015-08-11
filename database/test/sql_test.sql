-- Start by creating a known funding organization:
-- PASS
DELETE
FROM funding_organization
WHERE name = 'testfundingorganization';
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
   'pkrepps',
   '123 Lazy Lane',
   'Test insert should pass',
   E'xDEADBEEF',
   'pkrepps',
   NULL,
   'testfundingorganization',
   '1-800-867-5309',
   '12345-6789',
   'http://www.nosuchpage.example.com',
   'bad_address@example.com'
);
-- END

-- Make sure the funding cycle table exists:
-- PASS
SELECT COUNT(*)
FROM funding_cycle_table;
-- END

-- Insert something into the funding cycle table:
-- PASS
INSERT INTO funding_cycle_table
(
   funding_organization_number,
   funding_cycle_creation_time,
   funding_cycle_creator,
   funding_cycle_description,
   funding_cycle_end_date,
-- MOD    funding_cycle_modification_time,
-- MOD    funding_cycle_modifier,
   funding_cycle_name,
   funding_cycle_start_date,
   funding_cycle_website
)
VALUES
(
   (SELECT funding_organization_number
    FROM funding_organization
    WHERE name = 'testfundingorganization'),
   NOW(),
   'pkrepps',
   'dummy data',
   '2015-12-31',
-- MOD    NOW(),
-- MOD    'superman',
   'FC_test',
   '2015-01-01',
   'http://www.example.com'
);
-- END

-- Make sure the funding cycle view exists:
-- PASS
SELECT COUNT(*)
FROM funding_cycle;
-- END

-- Fail because of NULL FC name:
-- FAIL
INSERT INTO funding_cycle
(
   funding_cycle_number,
   name,
   description,
   start_date,
   end_date,
   website,
   funding_organization_number,
   creator,
   creation_time
-- MOD    ,modifier,
-- MOD    modification_time
)
VALUES
(
   NULL,
   NULL,
   'Funding Cycle 1',
   '1055-11-30',
   '2020-12-31',
   'http://www.nosuchpage.example.com',
   (SELECT funding_organization_number
    FROM funding_organization
    WHERE name = 'testfundingorganization'),
   'John Doe',
   NOW()
-- MOD    ,'modifier',
-- MOD    NOW()
);
-- END

-- Fail because of empty FC name:
-- FAIL
INSERT INTO funding_cycle
(
   funding_cycle_number,
   name,
   description,
   start_date,
   end_date,
   website,
   funding_organization_number,
   creator,
   creation_time
-- MOD    ,modifier,
-- MOD    modification_time
)
VALUES
(
   NULL,
   '',
   'Funding Cycle 1',
   '1055-11-30',
   '2020-12-31',
   'http://www.nosuchpage.example.com',
   (SELECT funding_organization_number
    FROM funding_organization
    WHERE name = 'testfundingorganization'),
   'John Doe',
   NOW()
-- MOD    ,'modifier',
-- MOD    NOW()
);
-- END

-- Fail because of NULL creator:
-- FAIL
INSERT INTO funding_cycle
(
   funding_cycle_number,
   name,
   description,
   start_date,
   end_date,
   website,
   funding_organization_number,
   creator,
   creation_time
-- MOD    ,modifier,
-- MOD    modification_time
)
VALUES
(
   NULL,
   'FC1',
   'Funding Cycle 1',
   '1055-11-30',
   '2020-12-31',
   'http://www.nosuchpage.example.com',
   (SELECT funding_organization_number
    FROM funding_organization
    WHERE name = 'testfundingorganization'),
   NULL,
   NOW()
-- MOD    ,'modifier',
-- MOD    NOW()
);
-- END

-- Fail because of empty creator:
-- FAIL
INSERT INTO funding_cycle
(
   funding_cycle_number,
   name,
   description,
   start_date,
   end_date,
   website,
   funding_organization_number,
   creator,
   creation_time
-- MOD    ,modifier,
-- MOD    modification_time
)
VALUES
(
   NULL,
   'FC1',
   'Funding Cycle 1',
   '1055-11-30',
   '2020-12-31',
   'http://www.nosuchpage.example.com',
   (SELECT funding_organization_number
    FROM funding_organization
    WHERE name = 'testfundingorganization'),
   '',
   NOW()
-- MOD    ,'modifier',
-- MOD    NOW()
);
-- END

-- MOD -- Fail because of NULL modifier:
-- MOD -- FAIL
-- MOD INSERT INTO funding_cycle
-- MOD (
-- MOD    funding_cycle_number,
-- MOD    name,
-- MOD    description,
-- MOD    start_date,
-- MOD    end_date,
-- MOD    website,
-- MOD    funding_organization_number,
-- MOD    creator,
-- MOD    creation_time
-- MOD    ,modifier,
-- MOD    modification_time
-- MOD )
-- MOD VALUES
-- MOD (
-- MOD    NULL,
-- MOD    'FC1',
-- MOD    'Funding Cycle 1',
-- MOD    '1055-11-30',
-- MOD    '2020-12-31',
-- MOD    'http://www.nosuchpage.example.com',
-- MOD    (SELECT funding_organization_number
-- MOD     FROM funding_organization
-- MOD     WHERE name = 'testfundingorganization'),
-- MOD    'batman',
-- MOD    NOW()
-- MOD    ,NULL,
-- MOD    NOW()
-- MOD );
-- MOD -- END

-- MOD -- Fail because of empty modifier:
-- MOD -- FAIL
-- MOD INSERT INTO funding_cycle
-- MOD (
-- MOD    funding_cycle_number,
-- MOD    name,
-- MOD    description,
-- MOD    start_date,
-- MOD    end_date,
-- MOD    website,
-- MOD    funding_organization_number,
-- MOD    creator,
-- MOD    creation_time
-- MOD    ,modifier,
-- MOD    modification_time
-- MOD )
-- MOD VALUES
-- MOD (
-- MOD    NULL,
-- MOD    'FC1',
-- MOD    'Funding Cycle 1',
-- MOD    '1055-11-30',
-- MOD    '2020-12-31',
-- MOD    'http://www.nosuchpage.example.com',
-- MOD    (SELECT funding_organization_number
-- MOD     FROM funding_organization
-- MOD     WHERE name = 'testfundingorganization'),
-- MOD    'batman',
-- MOD    NOW()
-- MOD    ,'',
-- MOD    NOW()
-- MOD );
-- MOD -- END

-- Fail because of NULL FO:
-- FAIL
INSERT INTO funding_cycle
(
   funding_cycle_number,
   name,
   description,
   start_date,
   end_date,
   website,
   funding_organization_number,
   creator,
   creation_time
-- MOD    ,modifier,
-- MOD    modification_time
)
VALUES
(
   NULL,
   'FC1',
   'Funding Cycle 1',
   '1055-11-30',
   '2020-12-31',
   'http://www.nosuchpage.example.com',
   NULL
   'John Doe',
   NOW()
-- MOD    ,'modifier',
-- MOD    NOW()
);
-- END

-- Pass with all info:
-- PASS
INSERT INTO funding_cycle
(
   funding_cycle_number,
   name,
   description,
   start_date,
   end_date,
   website,
   funding_organization_number,
   creator,
   creation_time
-- MOD    ,modifier,
-- MOD    modification_time
)
VALUES
(
   NULL,
   'FC1',
   'Funding Cycle 1',
   '1055-11-30',
   '2020-12-31',
   'http://www.nosuchpage.example.com',
   (SELECT funding_organization_number
    FROM funding_organization
    WHERE name = 'testfundingorganization'),
   'mmouse',
   NOW()
-- MOD    ,'modifier',
-- MOD    NOW()
);
-- END

-- Fail because of duplicate FC / FO entry:
-- FAIL
INSERT INTO funding_cycle
(
   funding_cycle_number,
   name,
   description,
   start_date,
   end_date,
   website,
   funding_organization_number,
   creator,
   creation_time
-- MOD    ,modifier,
-- MOD    modification_time
)
VALUES
(
   NULL,
   'FC1',
   'Funding Cycle 1',
   '1055-11-30',
   '2020-12-31',
   'http://www.nosuchpage.example.com',
   (SELECT funding_organization_number
    FROM funding_organization
    WHERE name = 'testfundingorganization'),
   'mmouse',
   NOW()
-- MOD    ,'modifier',
-- MOD    NOW()
);
-- END

-- UPDATE that should pass:
-- PASS
UPDATE funding_cycle
SET description = 'update test'
WHERE funding_cycle_number =
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'FC1'
       AND funding_organization_number =
          (SELECT funding_organization_number
           FROM funding_organization
           WHERE name = 'testfundingorganization'));
-- END

-- UPDATE that should pass but update nothing:
-- PASS
UPDATE funding_cycle
SET description = 'update test'
WHERE funding_cycle_number =
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'FC1'
       AND funding_organization_number =
          (SELECT funding_organization_number
           FROM funding_organization
           WHERE name = 'testfundingorganization'));
-- END

-- UPDATE setting a required field to NULL that should pass but update nothing
-- because testing has shown that attempting to update a field of a view on a
-- table column that is constrained to NOT NULL just silently doesn't perform
-- the update, but returns an indication that an update occurred. Interesting.
-- PASS
UPDATE funding_cycle
SET funding_organization_number = NULL
WHERE funding_cycle_number =
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'FC1'
       AND funding_organization_number =
          (SELECT funding_organization_number
           FROM funding_organization
           WHERE name = 'testfundingorganization'));
-- END

-- UPDATE setting a required field to NULL should pass but update nothing:
-- PASS
UPDATE funding_cycle
SET creation_time = NULL
WHERE funding_cycle_number =
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'FC1'
       AND funding_organization_number =
          (SELECT funding_organization_number
           FROM funding_organization
           WHERE name = 'testfundingorganization'));
-- END

-- UPDATE setting a required field to NULL should pass but update nothing:
-- PASS
UPDATE funding_cycle
SET creator = NULL
WHERE funding_cycle_number =
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'FC1'
       AND funding_organization_number =
          (SELECT funding_organization_number
           FROM funding_organization
           WHERE name = 'testfundingorganization'));
-- END

-- UPDATE setting a required field to NULL should pass but update nothing:
-- PASS
UPDATE funding_cycle
SET name = NULL
WHERE funding_cycle_number =
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'FC1'
       AND funding_organization_number =
          (SELECT funding_organization_number
           FROM funding_organization
           WHERE name = 'testfundingorganization'));
-- END

-- DELETE that should pass:
-- PASS
DELETE
FROM funding_cycle
WHERE name = 'FC_test';
-- END

-- PNKJR    -- ,modifier,
-- PNKJR    -- modification_time
