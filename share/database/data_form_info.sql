--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- Name: form_info_comments_seq; Type: SEQUENCE SET; Schema: public; Owner: gomri_user
--

SELECT pg_catalog.setval('form_info_comments_seq', 1, true);


--
-- Name: form_info_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gomri_user
--

SELECT pg_catalog.setval('form_info_id_seq', 22, true);


--
-- Name: form_info_var_name_seq; Type: SEQUENCE SET; Schema: public; Owner: gomri_user
--

SELECT pg_catalog.setval('form_info_var_name_seq', 20, true);


--
-- Data for Name: form_info; Type: TABLE DATA; Schema: public; Owner: gomri_user
--

INSERT INTO form_info VALUES (6, '<p>A dataset can be submitted in many different forms. Check the appropriate boxes that best describes the dataset that is expected to be submitted. If the type is not on the list, please provide a short description in the Others field.</p>
', 'datatype', 'dif');
INSERT INTO form_info VALUES (3, '<p>This is the person responsible for answering questions associated with this dataset. Choose from the dropdown list, if name does not appear please contact GRIIDC (<a href=mail://gomri.help@gomri.org>gomri.help@gomri.org</a>).</p>
', 'ppoc', 'dif');
INSERT INTO form_info VALUES (8, '<p>Please provide an approximation as to the size of the dataset expected to be created. This field will help GRIIDC formulate strategies for the access or collection of the dataset.</p>
', 'size', 'dif');
INSERT INTO form_info VALUES (5, '<p>This field should describe the rationale of collecting the dataset, procedure/process how this dataset will be created, period o
f data collection and what it will contain. Note that some of the fields that follow in this form are or may be components of this field.</p><br /><p>4000 Characters Max</p>', 'abstract', 'dif');
INSERT INTO form_info VALUES (7, '<p>If your dataset contains video, what video attributes (e.g. compression and decompression and file type) that will allow others to open the file.</p><p>Example: MPEG Codec:H.264</p><p>Example: QuickTime H.264</p>
', 'video', 'dif');
INSERT INTO form_info VALUES (4, '<p>By default, the consortia director, if applicable, is the Secondary Point of Contact (sPOC). The sPOC will be contacted if the pPOC cannot be reached or is unable to respond to queries regarding the dataset. As with the pPOC, a list is provided. However, if the name of the person is not on the list, contact GRIIDC (griidc.help@gomri.org) for assistance.</p>
', 'spoc', 'dif');
INSERT INTO form_info VALUES (17, '<p>As part of the contractual obligation of all projects of GoMRI, this dataset needs to be submitted to a recognized national data archival center. GRIIDC is recognized as an acceptable substitute to national centers if one can not be identified. If another center can be identified that is not on the list, identify the center and its URL in the Others field.</p>
', 'national', 'dif');
INSERT INTO form_info VALUES (16, '<p>It is very important that GRIIDC is aware of how the data will be made accessible to the world (when it is ready). This field answers the question, ‘How will you make these data available?’ Options are provided based on the proposal submitted. Check all that apply. If the data transfer protocol is not on the list, please identify in the Others field.</p>
', 'access', 'dif');
INSERT INTO form_info VALUES (15, '<p>This is an optional field but if the metadata standard to use has been identified, check all that apply. If an unlisted format/framework will be used, please identify in the Others field.</p>', 'standards', 'dif');
INSERT INTO form_info VALUES (14, '<p>All datasets that will be submitted will require metadata file(s). List the metadata editor(s) that will be use or planned to be used for the creation of the metadata file(s). If the project has not determined the editor use, enter TBD in the field.</p><p>Example: ESRI ArcCatalog, NCDDC MERMAid</p>', 'ed', 'dif');
INSERT INTO form_info VALUES (18, '<p>Some of the data that will be collected may have ethical and/or privacy issues preventing it to be shared or distributed. If such conditions exist, check the ‘yes’ check box. If uncertain, check the ‘uncertain’ box, and provide a short description of the issue.</p>
', 'privacy', 'dif');
INSERT INTO form_info VALUES (12, '<p>This is the relative geographical zone that the data will be collected or will be generated for. Although this form will allow descriptive inputs, the use of a point location in latitude and longitude (in decimal degrees) pair or a series to create a closed polygon are preferred. You may use online interactive maps for these inputs (e.g. http://www.birdtheme.org/useful/googletool.html) to get these coordinates.</p><p>Example of a descriptive input: Coastal waters close to New Orleans</p><p>Example of a polygon: -91.252441,30.600094,-88.417969,30.675715,-88.308105,29.745302,89.626465,28.574874,-91.538086,29.554345<p>', 'geoloc', 'dif');
INSERT INTO form_info VALUES (11, '<p>This is the approximate period that the data is expected to be collected as per the proposal. If these dates need to be modified, email GRIIDC (griidc.help@gomri.org) to unlock the file for modification after this form has been submitted.</p>
', 'date', 'dif');
INSERT INTO form_info VALUES (10, '<p>This field should contain the procedure or method used to collect or generate the data in the dataset. Depending on the dataset being created, list all of the procedures expected to be used in acquiring the data. If not on the list, provide inputs in the Others field.</p>', 'approach', 'dif');
INSERT INTO form_info VALUES (13, '<p>If applicable, all referenced data should be listed using standard bibliographic referencing format. Using a Digital Object Identifier (DOI) is preferred but not required. The hyperlinks (URL) to the online data source (if applicable) are acceptable inputs.</p><p>Example: (from EPA page): <a href=http://www.epa.gov/bpspill/water-dtl.html>http://www.epa.gov/bpspill/water-dtl.html</a>, (from Socrata): <a href=https://opendata.socrata.com/Government/EPA-Dispersant-in-Sediment-Constituent-Analyses-fr/5e5d-45aw>https://opendata.socrata.com/Government/EPA-Dispersant-in-Sediment-Constituent-Analyses-fr/5e5d-45aw</a></p>', 'historical', 'dif');
INSERT INTO form_info VALUES (9, '<p>This is a free-text field and you may list as much as possible all the variables or phenomena that will be measured, collected or generated. Separate list items using commas.</p><p>Example: wind direction, wind speed, sea surface temperature</p>', 'observation', 'dif');
INSERT INTO form_info VALUES (1, '<p>This is a required field. The task titles will be provided as a drop-down list from where you choose from. If your task is not listed, please email GRIIDC (gomri.help@gomri.org) for assistance.</p>', 'task', 'dif');
INSERT INTO form_info VALUES (19, 'Provide general remarks that will help GRIIDC prepare itself for the downloading (if needed), use or distribution of the dataset being identified.', 'remarks', 'dif');
INSERT INTO form_info VALUES (2, '<p>It is in the discretion of the researcher to define the level of data aggregation to define a dataset. If this level of data aggregation has not been identified, it is recommended to start by answering the ‘what, how, when, where’. It is also not recommended to aggregate data too much that the data attributes can no longer be segregated and discoverable.</p><p>Example Input: Hydrodynamics: ADCP Data for June – July 2012 in Station 42001</p>
<p>200 Characters Max</p>', 'title', 'dif');
INSERT INTO form_info VALUES (23, ' <strong>Title:</strong> A name or title by which the data or publication is known.<br /><em>(e.g. Multibeam bathymetry data for east Flower Garden Bank)</em>', 'title', 'doi');
INSERT INTO form_info VALUES (21, '<strong>Location (URL):</strong> Please fill with the persistent location (URL) of the identified object.<br><em>(e.g. <a href=http://harteresearchinstitute.org/ target=_blank>http://harteresearchinstitute.org/></a>)</em>', 'url', 'doi');
INSERT INTO form_info VALUES (22, '<strong>Creator:</strong> The main researcher involved in producing the data, or the authors of the publication in priority order. Each name may be a corporate, institutional, or personal name, in personal names list family name before given name, as in Darwin, Charles. Non-roman names should be transliterated according to the ALA-LC schemes .<br /> <em>(e.g. <a href="http://www.loc.gov/catdir/cpso/roman.html" target="_blank">http://www.loc.gov/catdir/cpso/roman.html</a>)</em>', 'creator', 'doi');
INSERT INTO form_info VALUES (24, '<strong>Publisher:</strong> A holder of the data  or the institution which submitted the work. In the case of datasets, the publisher is the entity primarily responsible for making the data available to the research community.<br /><em>(e.g., GRIIDC)</em>', 'publisher', 'doi');
INSERT INTO form_info VALUES (25, '<strong>Date:</strong> A valid ISO 8601 date.<br \><em>e.g. (2012-12-23)</em>', 'date', 'doi');
INSERT INTO form_info VALUES (20, '<p>Datasets are often classified or created for a field of science. Choices are provided and check all boxes that best classify the dataset to be generated. If the field is not presented, please provide an input the Others field.</p>', 'datafor', 'dif');
INSERT INTO form_info VALUES (26, '<p>This is a unique identifier for this dataset. It is generated automatically. Please use this UDI when reporting issues related to this dataset.</p>', 'udi', 'dif');
INSERT INTO form_info VALUES (27, '<p>This user interactive map is to assist in defining coordinates of the geographical or study area. The control panel on the upper-left corner allows for zooming and panning. The drop-down on the upper-right is to toggle map types to use and the panel below the map contains tools to generate the coordinates. The first button is used to create polygon, the second is to get the coordinates of a point in the map and the third button is to delete inputs and start over.</p><p>Click the ''Submit'' button to complete the process</p>', 'geographic', 'dif');


--
-- PostgreSQL database dump complete
--

