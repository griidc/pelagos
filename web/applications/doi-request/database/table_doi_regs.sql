-- Table: doi_regs

CREATE TABLE doi_regs
(
  id serial NOT NULL,
  url character varying(200),
  creator character varying(100),
  title character varying(120),
  publisher character varying(120),
  dsdate date,
  reqdate date,
  reqby character varying(60),
  reqip character varying(16),
  reqemail character varying(60),
  reqfirstname character varying(120),
  reqlastname character varying(200),
  emailsend boolean,
  approved boolean,
  approvedby character varying,
  approvedon date,
  doi text,
  urlstatus text,
  formhash text,
  CONSTRAINT "Primary ID" PRIMARY KEY (id),
  CONSTRAINT doi_regs_formhash_key UNIQUE (formhash)
);