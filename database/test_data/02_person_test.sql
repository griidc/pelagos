-- INSERT should fail due to NULL email address:
-- FAIL
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '2004-10-19 10:23:54+02',
   'postgres',
   'superman',
   NULL,
   'First',
   'Mr.',
   'MI',
   'Jr.',
   'Last'
);
-- END

-- INSERT should fail due to empty email address:
-- FAIL
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '2004-10-19 10:23:54+02',
   'postgres',
   'superman',
   '',
   'First',
   'Mr.',
   'MI',
   'Jr.',
   'Last'
);
-- END

-- INSERT should fail due to NULL given name:
-- FAIL
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '2004-10-19 10:23:54+02',
   'postgres',
   'superman',
   'user@example.com',
   NULL,
   'Mr.',
   'MI',
   'Jr.',
   'Last'
);
-- END

-- INSERT should fail due to empty given name:
-- FAIL
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '2004-10-19 10:23:54+02',
   'postgres',
   'superman',
   'user@example.com',
   '',
   'Mr.',
   'MI',
   'Jr.',
   'Last'
);
-- END

-- INSERT should pass despite NULL creation_time (because the trigger
-- function ignores and passed value and uses the current time as the
-- creation time)::
-- PASS
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   NULL,
   'postgres',
   'superman',
   'user@example.com',
   'First',
   'Mr.',
   'MI',
   'Jr.',
   'Last'
);
-- END

-- INSERT should pass due to empty creation_time:
-- PASS
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '',
   'postgres',
   'superman',
   'user2@example.com',
   'First',
   'Mr.',
   'MI',
   'Jr.',
   'Last'
);
-- END

-- INSERT should fail due to NULL creator:
-- FAIL
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '2004-10-19 10:23:54+02',
   NULL,
   'superman',
   'user3@example.com',
   'First',
   'Mr.',
   'MI',
   'Jr.',
   'Last'
);
-- END

-- INSERT should fail due to empty creator:
-- FAIL
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '2004-10-19 10:23:54+02',
   '',
   'superman',
   'user@example.com',
   'First',
   'Mr.',
   'MI',
   'Jr.',
   'Last'
);
-- END

-- INSERT should fail due to NULL modifier:
-- FAIL
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '2004-10-19 10:23:54+02',
   'superman',
   NULL,
   'user@example.com',
   'First',
   'Mr.',
   'MI',
   'Jr.',
   'Last'
);
-- END

-- INSERT should fail due to empty modifier:
-- FAIL
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '2004-10-19 10:23:54+02',
   'superman',
   '',
   'user@example.com',
   'First',
   'Mr.',
   'MI',
   'Jr.',
   'Last'
);
-- END

-- INSERT should fail due to NULL surname:
-- FAIL
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '2004-10-19 10:23:54+02',
   'postgres',
   'superman',
   'user@example.com',
   'First',
   'Mr.',
   'MI',
   'Jr.',
   NULL
);
-- END

-- INSERT should fail due to empty surname:
-- FAIL
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '2004-10-19 10:23:54+02',
   'postgres',
   'superman',
   'user@example.com',
   'First',
   'Mr.',
   'MI',
   'Jr.',
   ''
);
-- END

-- INSERT should fail due to invalid email address:
-- FAIL
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '2004-10-19 10:23:54+02',
   'postgres',
   'superman',
   'bad_address',
   'First',
   'Mr.',
   'MI',
   'Jr.',
   'Last'
);
-- END

-- INSERT should pass due to invalid creation time:
-- PASS
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '200',
   'postgres',
   'superman',
   'good_address@example.com',
   'First',
   'Mr.',
   'MI',
   'Jr.',
   'Last'
);
-- END

-- INSERT should pass
-- PASS
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '2004-10-19 10:23:54+02',
   'postgres',
   'superman',
   'bad_address@example.com',
   'First',
   'Mr.',
   'MI',
   'Jr.',
   'Last'
);
-- END

-- Exact duplicate INSERT should fail:
-- FAIL
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '2004-10-19 10:23:54+02',
   'postgres',
   'superman',
   'bad_address@example.com',
   'First',
   'Mr.',
   'MI',
   'Jr.',
   'Last'
);
-- END

-- Different person, same email address INSERT should fail:
-- FAIL
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '2004-10-19 10:23:54+02',
   'postgres',
   'superman',
   'bad_address@example.com',
   'Isaac',
   'Sir',
   'MI',
   NULL,
   'Newton'
);
-- END

-- INSERT a new email address into the email table:
-- PASS
INSERT INTO email_table
(
   email_address,
   email_validated
)
VALUES
(
   'isaac.newton@principia.org',
   TRUE
);
-- END

-- Different person using above new email address INSERT should pass:
-- PASS
INSERT INTO person
(
   person_number,
   creation_time,
   creator,
   modifier,
   email_address,
   given_name,
   title,
   middle_name,
   suffix,
   surname
)
VALUES
(
   NULL,
   '2004-10-19 10:23:54+02',
   'postgres',
   'superman',
   'isaac.newton@principia.org',
   'Isaac',
   'Sir',
   'MI',
   NULL,
   'Newton'
);
-- END

-- UPDATE statement without person number or modifier should pass because if a
-- NEW value is not explicitly supplied then the OLD values are used as the NEW
-- values as well (this will update all records as well):
-- PASS
UPDATE person
SET middle_name = 'newMI';
-- END

-- UPDATE statement without person number should pass for same reason as above:
-- PASS
UPDATE person
SET middle_name = 'newMI',
    modifier = 'gleibniz';
-- END

-- UPDATE statement without modifier should pass for same reason as above:
-- PASS
UPDATE person
SET middle_name = 'newMI'
WHERE person_number = (SELECT person_number
                       FROM person
                       WHERE surname = 'Newton'
                       LIMIT 1);
-- END

-- UPDATE person_number to NULL should fail:
-- FAIL
UPDATE person
SET person_number = NULL
WHERE person_number = (SELECT person_number
                       FROM person
                       WHERE surname = 'Newton'
                       LIMIT 1);
-- END

-- UPDATE email_address to NULL should fail:
-- FAIL
UPDATE person
SET email_address = NULL
WHERE person_number = (SELECT person_number
                       FROM person
                       WHERE surname = 'Newton'
                       LIMIT 1);
-- END

-- UPDATE email_address to the empty string should fail:
-- FAIL
UPDATE person
SET email_address = ''
WHERE person_number = (SELECT person_number
                       FROM person
                       WHERE surname = 'Newton'
                       LIMIT 1);
-- END

-- UPDATE given_name to NULL should fail:
-- FAIL
UPDATE person
SET given_name = NULL
WHERE person_number = (SELECT person_number
                       FROM person
                       WHERE surname = 'Newton'
                       LIMIT 1);
-- END

-- UPDATE given_name to the empty string should fail:
-- FAIL
UPDATE person
SET given_name = ''
WHERE person_number = (SELECT person_number
                       FROM person
                       WHERE surname = 'Newton'
                       LIMIT 1);
-- END

-- UPDATE modifier to NULL should fail:
-- FAIL
UPDATE person
SET modifier = NULL
WHERE person_number = (SELECT person_number
                       FROM person
                       WHERE surname = 'Newton'
                       LIMIT 1);
-- END

-- UPDATE modifier to the empty string should fail:
-- FAIL
UPDATE person
SET modifier = ''
WHERE person_number = (SELECT person_number
                       FROM person
                       WHERE surname = 'Newton'
                       LIMIT 1);
-- END

-- UPDATE email_address NULL should fail:
-- FAIL
UPDATE person
SET email_address = NULL
WHERE person_number = (SELECT person_number
                       FROM person
                       WHERE surname = 'Newton'
                       LIMIT 1);
-- END

-- UPDATE email_address to the empty string should fail:
-- FAIL
UPDATE person
SET email_address = ''
WHERE person_number = (SELECT person_number
                       FROM person
                       WHERE surname = 'Newton'
                       LIMIT 1);
-- END

-- UPDATE person to existing information:
-- FAIL
UPDATE person
SET email_address = 'isaac.newton@principia.org',
    given_name = 'Isaac',
    surname = 'Newton'
WHERE person_number = (SELECT person_number
                       FROM person
                       WHERE surname <> 'Newton'
                       ORDER BY RANDOM()
                       LIMIT 1);
-- END

-- UPDATE person with new email address should pass:
-- PASS
UPDATE person
SET email_address = 'another_new_address@example.com'
WHERE person_number =  (SELECT person_number
                       FROM person
                       WHERE surname <> 'Newton'
                       ORDER BY RANDOM()
                       LIMIT 1);
-- END

-- UPDATE person setting a NULLable value to NULL should pass:
-- PASS
UPDATE person
SET middle_name = NULL
WHERE person_number = (SELECT person_number
                       FROM person
                       WHERE surname = 'Newton'
                       LIMIT 1);
-- END
