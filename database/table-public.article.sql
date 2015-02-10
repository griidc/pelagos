--
-- Name: article; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE article (
    article_id integer DEFAULT nextval('seq_article_id'::regclass) NOT NULL,
    journal_issn issn_type NOT NULL,
    article_doi doi_type DEFAULT NULL::text,
    article_journal_issue text,
    article_journal_volume text,
    article_publication_date date,
    article_status pub_status DEFAULT 'unapproved'::pub_status NOT NULL,
    article_title text NOT NULL
);


ALTER TABLE public.article OWNER TO postgres;
