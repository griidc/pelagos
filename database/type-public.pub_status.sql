--
-- Name: pub_status; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE pub_status AS ENUM (
    'approved',
    'rejected',
    'unapproved'
);


ALTER TYPE public.pub_status OWNER TO postgres;
