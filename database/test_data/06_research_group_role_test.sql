-- PASS
TRUNCATE TABLE research_group_role_table
RESTART IDENTITY;
-- END

-- NULL creator should fail:
-- FAIL
INSERT INTO research_group_role
(
   research_group_role_number,
   creation_time,
   creator,
   modification_time,
   modifier,
   name,
   weight
)
VALUES
(
   DEFAULT,
   '1066-11-30 11:11:11',
   NULL,
   '1958-10-02 12:34:56',
   'Wascally Wabbit',
   'test_role',
   '200'
);
-- END

-- Empty creator should fail:
-- FAIL
INSERT INTO research_group_role
(
   research_group_role_number,
   creation_time,
   creator,
   modification_time,
   modifier,
   name,
   weight
)
VALUES
(
   DEFAULT,
   '1066-11-30 11:11:11',
   '',
   '1958-10-02 12:34:56',
   'Wascally Wabbit',
   'test_role',
   '200'
);
-- END

-- NULL name should fail:
-- FAIL
INSERT INTO research_group_role
(
   research_group_role_number,
   creation_time,
   creator,
   modification_time,
   modifier,
   name,
   weight
)
VALUES
(
   DEFAULT,
   '1066-11-30 11:11:11',
   'Yosimite Sam',
   '1958-10-02 12:34:56',
   'Wascally Wabbit',
   '',
   '200'
);
-- END

-- Empty name should fail:
-- FAIL
INSERT INTO research_group_role
(
   research_group_role_number,
   creation_time,
   creator,
   modification_time,
   modifier,
   name,
   weight
)
VALUES
(
   DEFAULT,
   '1066-11-30 11:11:11',
   'Yosimite Sam',
   '1958-10-02 12:34:56',
   'Wascally Wabbit',
   '',
   '200'
);
-- END

-- NULL research_group_role_number should fail:
-- FAIL
INSERT INTO research_group_role
(
   research_group_role_number,
   creation_time,
   creator,
   modification_time,
   modifier,
   name,
   weight
)
VALUES
(
   DEFAULT,
   '1066-11-30 11:11:11',
   'Yosimite Sam',
   '1958-10-02 12:34:56',
   'Wascally Wabbit',
   '',
   '200'
);
-- END

-- Valid INSERT should pass:
-- PASS
INSERT INTO research_group_role
(
   research_group_role_number,
   creation_time,
   creator,
   modification_time,
   modifier,
   name,
   weight
)
VALUES
(
   DEFAULT,
   '1066-11-30 11:11:11',
   'Yosimite Sam',
   '1958-10-02 12:34:56',
   'Wascally Wabbit',
   'Big Kahuna',
   '200'
);
-- END

-- Duplicate INSERT should fail:
-- FAIL
INSERT INTO research_group_role
(
   research_group_role_number,
   creation_time,
   creator,
   modification_time,
   modifier,
   name,
   weight
)
VALUES
(
   DEFAULT,
   '1066-11-30 11:11:11',
   'Yosimite Sam',
   '1958-10-02 12:34:56',
   'Wascally Wabbit',
   'Big Kahuna',
   '200'
);
-- END

-- Second INSERT should pass:
-- PASS
INSERT INTO research_group_role
(
   research_group_role_number,
   creation_time,
   creator,
   modification_time,
   modifier,
   name,
   weight
)
VALUES
(
   DEFAULT,
   '1066-11-30 11:11:11',
   'Yosimite Sam',
   '1958-10-02 12:34:56',
   'Wascally Wabbit',
   'Other Kahuna',
   '20'
);
-- END

-- UPDATE creator to NULL should pass since UPDATE ignores creator:
-- PASS
UPDATE research_group_role
SET creator = NULL
WHERE name = 'Big Kahuna';
-- END

-- UPDATE creator to the empty string should also pass:
-- PASS
UPDATE research_group_role
SET creator = NULL
WHERE name = 'Big Kahuna';
-- END

-- UPDATE research_group_role_number to NULL should fail:
-- FAIL
UPDATE research_group_role
SET research_group_role_number = NULL
WHERE name = 'Big Kahuna';
-- END

-- UPDATE modifier to NULL should fail:
-- FAIL
UPDATE research_group_role
SET modifier = NULL
WHERE name = 'Big Kahuna';
-- END

-- PNK -- UPDATE modifier to the empty string should fail:
-- PNK -- FAIL
-- PNK UPDATE research_group_role
-- PNK SET modifier = ''
-- PNK WHERE name = 'Big Kahuna';
-- PNK -- END

-- PNK -- UPDATE modifier should pass:
-- PNK -- PASS
-- PNK UPDATE research_group_role
-- PNK SET modifier = 'E. Fudd'
-- PNK WHERE name = 'Big Kahuna';
-- PNK -- END

-- PNK -- DELETE should pass:
-- PNK -- PASS
-- PNK DELETE
-- PNK FROM research_group_role
-- PNK WHERE research_group_role_number = 1;
-- PNK -- END
