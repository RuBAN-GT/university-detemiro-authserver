CREATE TABLE modules (
    service_id bigint NOT NULL,
    name character varying(60) NOT NULL,
    method character varying(60) DEFAULT 'require'::character varying
);

CREATE TABLE redirects (
    service_id bigint NOT NULL,
    address character varying(255) NOT NULL
);

CREATE TABLE services (
    id bigint NOT NULL,
    code character varying(60) NOT NULL,
    secret text,
    info text
);

CREATE SEQUENCE services_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE services_id_seq OWNED BY services.id;

CREATE TABLE config (
    service_id bigint NOT NULL,
    param character varying(255) NOT NULL,
    value text
);

ALTER TABLE ONLY services ALTER COLUMN id SET DEFAULT nextval('services_id_seq'::regclass);

ALTER TABLE ONLY modules
    ADD CONSTRAINT modules_pk PRIMARY KEY (service_id, name);

ALTER TABLE ONLY redirects
    ADD CONSTRAINT redirects_pk PRIMARY KEY (service_id, address);

ALTER TABLE ONLY services
    ADD CONSTRAINT services_pk PRIMARY KEY (id);

ALTER TABLE ONLY services
    ADD CONSTRAINT services_uq UNIQUE (code);

ALTER TABLE ONLY config
    ADD CONSTRAINT config_pkey PRIMARY KEY (service_id, param);

ALTER TABLE ONLY modules
    ADD CONSTRAINT modules_service FOREIGN KEY (service_id) REFERENCES services(id);

ALTER TABLE ONLY redirects
    ADD CONSTRAINT redirects_service FOREIGN KEY (service_id) REFERENCES services(id);

ALTER TABLE ONLY config
    ADD CONSTRAINT config_service FOREIGN KEY (service_id) REFERENCES services(id);