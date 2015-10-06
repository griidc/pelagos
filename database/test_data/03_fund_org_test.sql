-- Fail because of no FO name:
-- FAIL
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
   'Test insert should fail',
   E'\\xDEADBEEF',
   'superman',
   NULL,
   '1-800-867-5309',
   '12345-6789',
   'http://www.nosuchpage.example.com',
   'bad_address'
);
-- END

-- Fail because of no creator:
-- FAIL
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
   NULL,
   '123 Lazy Lane',
   'Test insert should fail',
   E'\\xDEADBEEF',
   'superman',
   NULL,
   '1-800-867-5309',
   '12345-6789',
   'http://www.nosuchpage.example.com',
   'bad_address'
);
-- END

-- Fail because of invalid email address:
-- FAIL
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
   'Test insert should fail',
   E'\\xDEADBEEF',
   'superman',
   NULL,
   'BP Fund Recipient',
   '1-800-867-5309',
   '12345-6789',
   'http://www.nosuchpage.example.com',
   'bad_address'
);
-- END

-- This a test comment.
-- Same data as above, but with a valid email address:
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
   'BP Fund Recipient',
   '1-800-867-5309',
   '12345-6789',
   'http://www.nosuchpage.example.com',
   'bad_address@example.com'
);
-- END

-- Test with no email address:
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
   website
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
   'Another BP Fund Recipient',
   '1-800-867-5309',
   '12345-6789',
   'http://www.nosuchpage.example.com'
);
-- END

-- Insert with NULL modifier, should default to creator:
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
   'jdoe',
   '123 Lazy Lane',
   'Test insert should pass',
   E'xDEADBEEF',
   NULL,
   NULL,
   'trust Me',
   '1-800-867-5309',
   '12345-6789',
   'http://www.nosuchpage.example.com',
   'bad_address@example.com'
);
-- END

-- Insert a funding organization to delete:
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
   'Delete Me',
   '1-800-867-5309',
   '12345-6789',
   'http://www.nosuchpage.example.com',
   'bad_address@example.com'
);
-- END

-- Test for successful update:
-- PASS
UPDATE funding_organization
SET description = 'Test UPDATE should pass',
    email_address = 'atotallynewaddress@example.net'
WHERE name = 'BP Fund Recipient';
-- END

-- Test for successful addition of an email address where one does not exist:
-- PASS
UPDATE funding_organization
SET description = 'Test UPDATE inserting email, should pass',
    email_address = 'atotallynewaddress2@example.net'
WHERE name = 'Another BP Fund Recipient';
-- END

-- Test for successful update of email removal:
-- PASS
UPDATE funding_organization
SET description = 'Test UPDATE should pass',
    email_address = NULL
WHERE name = 'Another BP Fund Recipient';
-- END

-- -- Test for successful update of adding an email back to above:
-- -- PASS
-- UPDATE funding_organization
-- SET description = 'Test UPDATE should pass',
--     email_address = 'anothernewaddress@example.net'
-- WHERE name = 'Another BP Fund Recipient';
-- -- END

-- PASS
SELECT FALSE
FROM email2funding_organization_table
WHERE email_address = 'atotallynewaddress@example.net';
-- END

-- -- Test for successful deletion:
-- -- PASS
-- DELETE
-- FROM funding_organization
-- WHERE name = 'Delete Me';
-- -- END
