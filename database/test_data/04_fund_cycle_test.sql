-- Start by creating a known funding organization:
-- PASS
DELETE
FROM funding_organization
WHERE name = CONCAT('testfundingorganization',
                    DATE_TRUNC('minute', NOW()));
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
   CONCAT('testfundingorganization',
          DATE_TRUNC('minute', NOW())),
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
    WHERE name = CONCAT('testfundingorganization',
                        DATE_TRUNC('minute', NOW()))),
   NOW(),
   'superman',
   'dummy data',
   '2015-12-31',
   NOW(),
   'superman',
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
   ,modifier,
   modification_time
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
    WHERE name = CONCAT('testfundingorganization',
                        DATE_TRUNC('minute', NOW()))),
   'John Doe',
   NOW()
   ,'modifier',
   NOW()
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
   ,modifier,
   modification_time
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
    WHERE name = CONCAT('testfundingorganization',
                        DATE_TRUNC('minute', NOW()))),
   'John Doe',
   NOW()
   ,'modifier',
   NOW()
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
   ,modifier,
   modification_time
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
    WHERE name = CONCAT('testfundingorganization',
                        DATE_TRUNC('minute', NOW()))),
   NULL,
   NOW()
   ,'modifier',
   NOW()
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
   ,modifier,
   modification_time
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
    WHERE name = CONCAT('testfundingorganization',
                        DATE_TRUNC('minute', NOW()))),
   '',
   NOW()
   ,'modifier',
   NOW()
);
-- END

-- Fail because of NULL modifier:
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
   ,modifier,
   modification_time
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
    WHERE name = CONCAT('testfundingorganization',
                 DATE_TRUNC('minute', NOW()))),
   'batman',
   NOW()
   ,NULL,
   NOW()
);
-- END

-- Fail because of empty modifier:
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
   ,modifier,
   modification_time
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
    WHERE name = CONCAT('testfundingorganization',
                        DATE_TRUNC('minute', NOW()))),
   'batman',
   NOW()
   ,'',
   NOW()
);
-- END

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
   ,modifier,
   modification_time
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
   ,'modifier',
   NOW()
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
   ,modifier,
   modification_time
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
    WHERE name = CONCAT('testfundingorganization',
                        DATE_TRUNC('minute', NOW()))),
   'mmouse',
   NOW()
   ,'modifier',
   NOW()
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
   ,modifier,
   modification_time
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
    WHERE name = CONCAT('testfundingorganization',
                        DATE_TRUNC('minute', NOW()))),
   'mmouse',
   NOW()
   ,'modifier',
   NOW()
);
-- END

-- INSERT with just an end date should pass:
-- PASS
INSERT INTO funding_cycle
(                  
   funding_organization_number,
   name,
   creator,
   end_date,
   modifier
)
VALUES
(
   5,
   'fc3',
   'pluto',
   '2020-12-31',
   'dduck'
);
-- END

-- INSERT with just a start date should pass:
-- PASS
INSERT INTO funding_cycle
(                  
   funding_organization_number,
   name,
   creator,
   start_date,
   modifier
)
VALUES
(
   5,
   'fc4',
   'pluto',
   '2020-12-31',
   'dduck'
);
-- END

-- INSERT where end date preceeds start date should fail:
-- FAIL
INSERT INTO funding_cycle
(                  
   funding_organization_number,
   name,
   creator,
   start_date,
   end_date,
   modifier
)
VALUES
(
   5,
   'fc6',
   'pluto',
   '2020-12-31',
   '1055-11-30',
   'dduck'
);
-- END

-- INSERT WHERE end date not one day creater than start date should fail:
-- FAIL
INSERT INTO funding_cycle
(                  
   funding_organization_number,
   name,
   creator,
   start_date,
   end_date,
   modifier
)
VALUES
(
   5,
   'fc6',
   'pluto',
   '2015-08-31',
   '2015-08-31',
   'dduck'
);
-- END

-- UPDATE that should pass:
-- PASS
UPDATE funding_cycle
SET description = 'update test',
    modifier = CONCAT('new_modifier_',
                      CAST((SELECT *
                            FROM generate_series(1,100000)
                            ORDER BY RANDOM()
                            LIMIT 1) AS TEXT))
WHERE funding_cycle_number =
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'FC1'
       AND funding_organization_number =
          (SELECT funding_organization_number
           FROM funding_organization
           WHERE name = CONCAT('testfundingorganization',
                               DATE_TRUNC('minute', NOW()))));
-- END

-- UPDATE that should pass but update nothing:
-- PASS
UPDATE funding_cycle
SET description = 'update test',
    modifier = CONCAT('new_modifier_',
                      CAST((SELECT *
                            FROM generate_series(1,100000)
                            ORDER BY RANDOM()
                            LIMIT 1) AS TEXT))
WHERE funding_cycle_number =
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'FC1'
       AND funding_organization_number =
          (SELECT funding_organization_number
           FROM funding_organization
           WHERE name = CONCAT('testfundingorganization',
                               DATE_TRUNC('minute', NOW()))));
-- END

-- UPDATE setting a required field to NULL fail:
-- FAIL
UPDATE funding_cycle
SET funding_organization_number = NULL,
    modifier = CONCAT('new_modifier_',
                      CAST((SELECT *
                            FROM generate_series(1,100000)
                            ORDER BY RANDOM()
                            LIMIT 1) AS TEXT))
WHERE funding_cycle_number =
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'FC1'
       AND funding_organization_number =
          (SELECT funding_organization_number
           FROM funding_organization
           WHERE name = CONCAT('testfundingorganization',
                               DATE_TRUNC('minute', NOW()))));
-- END

-- UPDATE setting a required field with a default value to NULL should pass:
-- PASS
UPDATE funding_cycle
SET creation_time = NULL,
    modifier = CONCAT('new_modifier_',
                      CAST((SELECT *
                            FROM generate_series(1,100000)
                            ORDER BY RANDOM()
                            LIMIT 1) AS TEXT))
WHERE funding_cycle_number =
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'FC1'
       AND funding_organization_number =
          (SELECT funding_organization_number
           FROM funding_organization
           WHERE name = CONCAT('testfundingorganization',
                               DATE_TRUNC('minute', NOW()))));
-- END

-- UPDATE setting a required field to NULL should fail:
-- PASS
UPDATE funding_cycle
SET creator = NULL,
    modifier = CONCAT('new_modifier_',
                      CAST((SELECT *
                            FROM generate_series(1,100000)
                            ORDER BY RANDOM()
                            LIMIT 1) AS TEXT))
WHERE funding_cycle_number =
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'FC1'
       AND funding_organization_number =
          (SELECT funding_organization_number
           FROM funding_organization
           WHERE name = CONCAT('testfundingorganization',
                               DATE_TRUNC('minute', NOW()))));
-- END

-- UPDATE setting a required field to NULL should fail:
-- FAIL
UPDATE funding_cycle
SET name = NULL,
    modifier = CONCAT('new_modifier_',
                      CAST((SELECT *
                            FROM generate_series(1,100000)
                            ORDER BY RANDOM()
                            LIMIT 1) AS TEXT))
WHERE funding_cycle_number =
   (SELECT funding_cycle_number
    FROM funding_cycle
    WHERE name = 'FC1'
       AND funding_organization_number =
          (SELECT funding_organization_number
           FROM funding_organization
           WHERE name = CONCAT('testfundingorganization',
                               DATE_TRUNC('minute', NOW()))));
-- END

-- DELETE that should pass:
-- PASS
DELETE
FROM funding_cycle
WHERE name = 'FC_test';
-- END

-- UPDATE start date to equal end date should fail:
-- FAIL
UPDATE funding_cycle
SET start_date = '2020-12-31',
    modifier = CONCAT('new_modifier_',
                      CAST((SELECT *
                            FROM generate_series(1,100000)
                            ORDER BY RANDOM()
                            LIMIT 1) AS TEXT))
WHERE name = 'FC1';
-- END

-- UPDATE start date should pass:
-- PASS
UPDATE funding_cycle
SET start_date = '2015-08-31',
    modifier = CONCAT('new_modifier_',
                      CAST((SELECT *
                            FROM generate_series(1,100000)
                            ORDER BY RANDOM()
                            LIMIT 1) AS TEXT))
WHERE name = 'FC1';
-- END

-- UPDATE end date should fail:
-- FAIL
UPDATE funding_cycle
SET end_date = '2015-08-30',
    modifier = CONCAT('new_modifier_',
                      CAST((SELECT *
                            FROM generate_series(1,100000)
                            ORDER BY RANDOM()
                            LIMIT 1) AS TEXT))
WHERE name = 'FC1';
-- END

-- UPDATE end date should fail:
-- FAIL
UPDATE funding_cycle
SET end_date = '2015-08-31',
    modifier = CONCAT('new_modifier_',
                      CAST((SELECT *
                            FROM generate_series(1,100000)
                            ORDER BY RANDOM()
                            LIMIT 1) AS TEXT))
WHERE name = 'FC1';
-- END

-- UPDATE end date should pass:
-- PASS
UPDATE funding_cycle
SET end_date = '2015-09-01',
    modifier = CONCAT('new_modifier_',
                      CAST((SELECT *
                            FROM generate_series(1,100000)
                            ORDER BY RANDOM()
                            LIMIT 1) AS TEXT))
WHERE name = 'FC1';
-- END
