--
-- Name: issn_type; Type: DOMAIN; Schema: public; Owner: postgres
--

CREATE DOMAIN issn_type AS text
	CONSTRAINT chk_issn_type CHECK ((VALUE ~* '^\d{4}-\d{3}[\dX]$'::text));


ALTER DOMAIN public.issn_type OWNER TO postgres;
