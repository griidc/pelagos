--
-- Name: journal; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE journal (
    journal_issn issn_type NOT NULL,
    journal_name text NOT NULL,
    journal_publisher text NOT NULL,
    journal_status pub_status DEFAULT 'unapproved'::pub_status NOT NULL
);


ALTER TABLE public.journal OWNER TO postgres;
