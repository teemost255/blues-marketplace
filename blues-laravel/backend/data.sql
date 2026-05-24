--
-- PostgreSQL database dump
--

\restrict SYKgri8gSPZG3b87M6VYnQD5F0DEW0Ph7FagdT7HLokGgG649WnasUha8NcbTUv

-- Dumped from database version 16.10
-- Dumped by pg_dump version 16.10

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: admin_audit_log; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.admin_audit_log (
    id bigint NOT NULL,
    admin_id bigint,
    action character varying(255) NOT NULL,
    target_type character varying(255),
    target_id bigint,
    details json,
    ip_address character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.admin_audit_log OWNER TO postgres;

--
-- Name: admin_audit_log_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.admin_audit_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.admin_audit_log_id_seq OWNER TO postgres;

--
-- Name: admin_audit_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.admin_audit_log_id_seq OWNED BY public.admin_audit_log.id;


--
-- Name: admins_users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.admins_users (
    id bigint NOT NULL,
    email character varying(255) NOT NULL,
    password_hash character varying(255) NOT NULL,
    display_name character varying(255),
    avatar_url character varying(255),
    is_active boolean DEFAULT true NOT NULL,
    last_login timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    role character varying(255) DEFAULT 'admin'::character varying NOT NULL
);


ALTER TABLE public.admins_users OWNER TO postgres;

--
-- Name: admins_users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.admins_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.admins_users_id_seq OWNER TO postgres;

--
-- Name: admins_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.admins_users_id_seq OWNED BY public.admins_users.id;


--
-- Name: announcements; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.announcements (
    id bigint NOT NULL,
    title character varying(255) NOT NULL,
    message text NOT NULL,
    type character varying(255) DEFAULT 'info'::character varying NOT NULL,
    sent_by bigint,
    email_sent boolean DEFAULT false NOT NULL,
    recipients_count integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.announcements OWNER TO postgres;

--
-- Name: announcements_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.announcements_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.announcements_id_seq OWNER TO postgres;

--
-- Name: announcements_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.announcements_id_seq OWNED BY public.announcements.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: listing_categories; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.listing_categories (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    slug character varying(255),
    description character varying(255),
    icon character varying(255),
    is_active boolean DEFAULT true NOT NULL
);


ALTER TABLE public.listing_categories OWNER TO postgres;

--
-- Name: listing_categories_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.listing_categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.listing_categories_id_seq OWNER TO postgres;

--
-- Name: listing_categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.listing_categories_id_seq OWNED BY public.listing_categories.id;


--
-- Name: listings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.listings (
    id bigint NOT NULL,
    title character varying(255) NOT NULL,
    description text,
    category character varying(255),
    price numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    stock integer DEFAULT 0 NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    image_url character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    image_path character varying(255),
    featured boolean DEFAULT false NOT NULL,
    login_details text
);


ALTER TABLE public.listings OWNER TO postgres;

--
-- Name: listings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.listings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.listings_id_seq OWNER TO postgres;

--
-- Name: listings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.listings_id_seq OWNED BY public.listings.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: notifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.notifications (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    title character varying(255) NOT NULL,
    message text NOT NULL,
    is_read boolean DEFAULT false NOT NULL,
    type character varying(255) DEFAULT 'info'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.notifications OWNER TO postgres;

--
-- Name: notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.notifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.notifications_id_seq OWNER TO postgres;

--
-- Name: notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.notifications_id_seq OWNED BY public.notifications.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: profiles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.profiles (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    username character varying(255),
    display_name character varying(255),
    avatar_url character varying(255),
    status character varying(255) DEFAULT 'active'::character varying NOT NULL,
    referral_code character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT profiles_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'suspended'::character varying, 'banned'::character varying])::text[])))
);


ALTER TABLE public.profiles OWNER TO postgres;

--
-- Name: profiles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.profiles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.profiles_id_seq OWNER TO postgres;

--
-- Name: profiles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.profiles_id_seq OWNED BY public.profiles.id;


--
-- Name: purchases; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.purchases (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    listing_id bigint NOT NULL,
    amount numeric(10,2) NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    delivery_data text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT purchases_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'completed'::character varying, 'refunded'::character varying, 'disputed'::character varying])::text[])))
);


ALTER TABLE public.purchases OWNER TO postgres;

--
-- Name: purchases_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.purchases_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.purchases_id_seq OWNER TO postgres;

--
-- Name: purchases_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.purchases_id_seq OWNED BY public.purchases.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.settings (
    id bigint NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.settings OWNER TO postgres;

--
-- Name: settings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.settings_id_seq OWNER TO postgres;

--
-- Name: settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.settings_id_seq OWNED BY public.settings.id;


--
-- Name: support_tickets; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.support_tickets (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    subject character varying(255) NOT NULL,
    message text NOT NULL,
    admin_reply text,
    status character varying(255) DEFAULT 'open'::character varying NOT NULL,
    priority character varying(255) DEFAULT 'medium'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT support_tickets_priority_check CHECK (((priority)::text = ANY ((ARRAY['low'::character varying, 'medium'::character varying, 'high'::character varying])::text[]))),
    CONSTRAINT support_tickets_status_check CHECK (((status)::text = ANY ((ARRAY['open'::character varying, 'in_progress'::character varying, 'resolved'::character varying, 'closed'::character varying])::text[])))
);


ALTER TABLE public.support_tickets OWNER TO postgres;

--
-- Name: support_tickets_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.support_tickets_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.support_tickets_id_seq OWNER TO postgres;

--
-- Name: support_tickets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.support_tickets_id_seq OWNED BY public.support_tickets.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    status character varying(255) DEFAULT 'active'::character varying NOT NULL
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: virtual_number_orders; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.virtual_number_orders (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    external_order_id character varying(255),
    service character varying(255) NOT NULL,
    country character varying(255) DEFAULT 'ng'::character varying NOT NULL,
    phone_number character varying(255),
    sms_code character varying(255),
    cost numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    raw_response text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT virtual_number_orders_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'active'::character varying, 'completed'::character varying, 'cancelled'::character varying, 'failed'::character varying])::text[])))
);


ALTER TABLE public.virtual_number_orders OWNER TO postgres;

--
-- Name: virtual_number_orders_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.virtual_number_orders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.virtual_number_orders_id_seq OWNER TO postgres;

--
-- Name: virtual_number_orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.virtual_number_orders_id_seq OWNED BY public.virtual_number_orders.id;


--
-- Name: wallet_transactions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.wallet_transactions (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    amount numeric(10,2) NOT NULL,
    type character varying(255) NOT NULL,
    reference character varying(255),
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT wallet_transactions_type_check CHECK (((type)::text = ANY ((ARRAY['deposit'::character varying, 'withdrawal'::character varying, 'purchase'::character varying, 'refund'::character varying, 'credit'::character varying, 'debit'::character varying])::text[])))
);


ALTER TABLE public.wallet_transactions OWNER TO postgres;

--
-- Name: wallet_transactions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.wallet_transactions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.wallet_transactions_id_seq OWNER TO postgres;

--
-- Name: wallet_transactions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.wallet_transactions_id_seq OWNED BY public.wallet_transactions.id;


--
-- Name: wallets; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.wallets (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    balance numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.wallets OWNER TO postgres;

--
-- Name: wallets_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.wallets_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.wallets_id_seq OWNER TO postgres;

--
-- Name: wallets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.wallets_id_seq OWNED BY public.wallets.id;


--
-- Name: wishlists; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.wishlists (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    listing_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.wishlists OWNER TO postgres;

--
-- Name: wishlists_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.wishlists_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.wishlists_id_seq OWNER TO postgres;

--
-- Name: wishlists_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.wishlists_id_seq OWNED BY public.wishlists.id;


--
-- Name: admin_audit_log id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin_audit_log ALTER COLUMN id SET DEFAULT nextval('public.admin_audit_log_id_seq'::regclass);


--
-- Name: admins_users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admins_users ALTER COLUMN id SET DEFAULT nextval('public.admins_users_id_seq'::regclass);


--
-- Name: announcements id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements ALTER COLUMN id SET DEFAULT nextval('public.announcements_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: listing_categories id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.listing_categories ALTER COLUMN id SET DEFAULT nextval('public.listing_categories_id_seq'::regclass);


--
-- Name: listings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.listings ALTER COLUMN id SET DEFAULT nextval('public.listings_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: notifications id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications ALTER COLUMN id SET DEFAULT nextval('public.notifications_id_seq'::regclass);


--
-- Name: profiles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.profiles ALTER COLUMN id SET DEFAULT nextval('public.profiles_id_seq'::regclass);


--
-- Name: purchases id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchases ALTER COLUMN id SET DEFAULT nextval('public.purchases_id_seq'::regclass);


--
-- Name: settings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings ALTER COLUMN id SET DEFAULT nextval('public.settings_id_seq'::regclass);


--
-- Name: support_tickets id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.support_tickets ALTER COLUMN id SET DEFAULT nextval('public.support_tickets_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: virtual_number_orders id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.virtual_number_orders ALTER COLUMN id SET DEFAULT nextval('public.virtual_number_orders_id_seq'::regclass);


--
-- Name: wallet_transactions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wallet_transactions ALTER COLUMN id SET DEFAULT nextval('public.wallet_transactions_id_seq'::regclass);


--
-- Name: wallets id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wallets ALTER COLUMN id SET DEFAULT nextval('public.wallets_id_seq'::regclass);


--
-- Name: wishlists id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wishlists ALTER COLUMN id SET DEFAULT nextval('public.wishlists_id_seq'::regclass);


--
-- Data for Name: admin_audit_log; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.admin_audit_log (id, admin_id, action, target_type, target_id, details, ip_address, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: admins_users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.admins_users (id, email, password_hash, display_name, avatar_url, is_active, last_login, created_at, updated_at, role) FROM stdin;
2	admin@blues.com	$2y$12$sRCnxa9IjcJ5zFflaQw8qOiODW4HxFXSpz34TpSAv/f2zMNiOhaJS	Super Admin	\N	t	2026-05-24 02:16:08	2026-05-23 21:24:08	2026-05-24 02:16:08	admin
\.


--
-- Data for Name: announcements; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.announcements (id, title, message, type, sent_by, email_sent, recipients_count, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: listing_categories; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.listing_categories (id, name, created_at, updated_at, slug, description, icon, is_active) FROM stdin;
1	Facebook	2026-05-23 21:07:54	2026-05-23 21:07:54	\N	\N	\N	t
2	Instagram	2026-05-23 21:07:54	2026-05-23 21:07:54	\N	\N	\N	t
3	TikTok	2026-05-23 21:07:54	2026-05-23 21:07:54	\N	\N	\N	t
4	Virtual Numbers	2026-05-23 21:07:54	2026-05-23 21:07:54	\N	\N	\N	t
5	Twitter	2026-05-24 02:12:26	2026-05-24 02:12:26	\N	\N	\N	t
6	Telegram	2026-05-24 02:12:26	2026-05-24 02:12:26	\N	\N	\N	t
\.


--
-- Data for Name: listings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.listings (id, title, description, category, price, stock, is_active, image_url, created_at, updated_at, image_path, featured, login_details) FROM stdin;
1	Verified facebook account	\N	\N	3000.00	0	t	\N	2026-05-23 21:40:37	2026-05-24 02:31:29	listings/n4e4PkZpPSVlOrUw74SXPERNWjELn1Mkj3Q5fJm9.jpg	f	Email: test@test.com\r\nPassword: 1234user
2	Fb page	Page profile	\N	3000.00	3	t	\N	2026-05-24 02:34:45	2026-05-24 02:34:45	listings/tCvmzhJPPmmtcoXa4Gxp8QCc8f6ow9HJtPOe2b4s.jpg	f	Email\r\nPassword
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_05_23_170156_create_admins_users_table	1
5	2026_05_23_170157_create_listings_table	1
6	2026_05_23_170157_create_profiles_table	1
7	2026_05_23_170158_create_purchases_table	1
8	2026_05_23_170159_create_wallet_transactions_table	1
9	2026_05_23_170159_create_wallets_table	1
10	2026_05_23_170200_create_support_tickets_table	1
11	2026_05_23_170201_create_notifications_table	1
12	2026_05_23_170202_create_admin_audit_log_table	1
13	2026_05_24_000001_create_wishlists_table	1
14	2026_05_24_000002_create_listing_categories_table	1
15	2026_05_23_211537_add_status_to_users_table	2
16	2026_05_23_211538_create_settings_table	2
17	2026_05_23_211757_add_extra_fields_to_listing_categories_table	2
18	2026_05_23_211758_add_extra_fields_to_listings_table	2
19	2026_05_23_215407_add_role_to_admins_users_table	3
20	2026_05_23_215408_create_password_reset_tokens_table	3
21	2026_05_24_000003_add_extra_fields_to_listing_categories_table	4
22	2026_05_25_000001_update_listing_categories_add_twitter_telegram	5
23	2026_05_25_000002_create_virtual_number_orders_table	5
24	2026_05_25_000003_add_login_details_to_listings_table	5
25	2026_05_24_070448_create_announcements_table	6
\.


--
-- Data for Name: notifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.notifications (id, user_id, title, message, is_read, type, created_at, updated_at) FROM stdin;
1	1	Purchase Successful	Your purchase of "Verified facebook account" was successful. Check your orders for delivery details.	t	success	2026-05-23 22:02:06	2026-05-23 22:02:21
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: profiles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.profiles (id, user_id, username, display_name, avatar_url, status, referral_code, created_at, updated_at) FROM stdin;
1	1	\N	Teemost	\N	active	WZSBKNOL	2026-05-23 21:34:21	2026-05-23 21:34:21
\.


--
-- Data for Name: purchases; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.purchases (id, user_id, listing_id, amount, status, delivery_data, created_at, updated_at) FROM stdin;
1	1	1	3000.00	completed	\N	2026-05-23 22:02:06	2026-05-23 22:02:06
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
tX4uCwiiYbyvdqomFUy1GGlXTcGmZIbqJTPi2qpW	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoialhwZ0JwbnFkd1hhdTY0VkUwTE53S1pQSXBwbGlrYnFvSW03SXB5dSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6ODI6Imh0dHA6Ly84MTZhYzJjNy0xNzI1LTQzNWMtOWFmZC1mM2YyMGMzNGMzZWMtMDAtZnZsdjRnZHVqMjdoLnJpa2VyLnJlcGxpdC5kZXYvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO319	1779589937
64AYRTRIWCjzwz4Bom0RoBatuYKPvikqEKQd84KT	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoib0ZPY1NnWmRNRzRGV0xJZGFuUGJFYkxyYW5PdXBHOU5LcU1kTmk4TiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTIwOiJodHRwOi8vODE2YWMyYzctMTcyNS00MzVjLTlhZmQtZjNmMjBjMzRjM2VjLTAwLWZ2bHY0Z2R1ajI3aC5yaWtlci5yZXBsaXQuZGV2L21hcmtldHBsYWNlP2NhdGVnb3J5PUZhY2Vib29rJnNlYXJjaD0mc29ydD0iO3M6NToicm91dGUiO3M6MTE6Im1hcmtldHBsYWNlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1779589986
z6nZr6EGM5R9XFQAVvBEdmGLLRIIAbiRnMn9xD6M	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiSlJoS1NPUEh2OHhWMk1QalFJeWY2eG1RazhDbENsUWFOREU2TmZSTiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTIwOiJodHRwOi8vODE2YWMyYzctMTcyNS00MzVjLTlhZmQtZjNmMjBjMzRjM2VjLTAwLWZ2bHY0Z2R1ajI3aC5yaWtlci5yZXBsaXQuZGV2L21hcmtldHBsYWNlP2NhdGVnb3J5PUZhY2Vib29rJnNlYXJjaD0mc29ydD0iO3M6NToicm91dGUiO3M6MTE6Im1hcmtldHBsYWNlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1779589986
iXqSleG8T11pYYvuDYc0xa4LFwvX31tHJXTw6ID6	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiRmN5TE85eFpRVmdDWWlER2E0OU1iY3p5ZnVkdXduQkh4cWZKUGo5OCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTEyOiJodHRwOi8vODE2YWMyYzctMTcyNS00MzVjLTlhZmQtZjNmMjBjMzRjM2VjLTAwLWZ2bHY0Z2R1ajI3aC5yaWtlci5yZXBsaXQuZGV2L21hcmtldHBsYWNlP2NhdGVnb3J5PSZzZWFyY2g9JnNvcnQ9IjtzOjU6InJvdXRlIjtzOjExOiJtYXJrZXRwbGFjZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1779590118
oLQE6rayv4liMHOxjhBtKMUeybt68ZgnYokLrFzb	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiMFVvRjJSSDFuNnJaUkllQlBBbFN4WFF5bHVoYVBoU0V4cVREQTJuYSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTIxOiJodHRwOi8vODE2YWMyYzctMTcyNS00MzVjLTlhZmQtZjNmMjBjMzRjM2VjLTAwLWZ2bHY0Z2R1ajI3aC5yaWtlci5yZXBsaXQuZGV2L21hcmtldHBsYWNlP2NhdGVnb3J5PUluc3RhZ3JhbSZzZWFyY2g9JnNvcnQ9IjtzOjU6InJvdXRlIjtzOjExOiJtYXJrZXRwbGFjZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1779590126
0a0A1yexO8k3uoFhm6pxzSQPH2n3ukhYocfrzjtj	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiejdTaGJBOTREREpWZzE3UXhtdElXOTBWSVkxWjNsTjdMeElOZFhZayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTIxOiJodHRwOi8vODE2YWMyYzctMTcyNS00MzVjLTlhZmQtZjNmMjBjMzRjM2VjLTAwLWZ2bHY0Z2R1ajI3aC5yaWtlci5yZXBsaXQuZGV2L21hcmtldHBsYWNlP2NhdGVnb3J5PUluc3RhZ3JhbSZzZWFyY2g9JnNvcnQ9IjtzOjU6InJvdXRlIjtzOjExOiJtYXJrZXRwbGFjZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1779590127
Z1OuYMBbKVd3RP8uQGHw0L2dN5l5CJH7UwquSSEb	\N	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/140.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiRjRRakJPWmxsQUpLcHdIS2pibVhUYTVNUkVEYTIwWEhuWG1NWGw2TSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6NTAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1779606705
nBIK8riwdjJDICpIabfu3e7iZFmBP11MarbEiZuQ	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiVzFWcUhjbGVURjVoMjJXeXRCMXBCeGlPbWs0RGlJeGthSXp3cjJPWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6ODI6Imh0dHA6Ly84MTZhYzJjNy0xNzI1LTQzNWMtOWFmZC1mM2YyMGMzNGMzZWMtMDAtZnZsdjRnZHVqMjdoLnJpa2VyLnJlcGxpdC5kZXYvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO319	1779589937
Ukb6MnNarbftMOgyiVQXVsJqWUmkKAchHcnr3a6Y	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiOGx4SDNpd3JoTzVyQzk1dHlYdnZoTDVtcElmdHdsWmpFelE3b0xjcCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTIwOiJodHRwOi8vODE2YWMyYzctMTcyNS00MzVjLTlhZmQtZjNmMjBjMzRjM2VjLTAwLWZ2bHY0Z2R1ajI3aC5yaWtlci5yZXBsaXQuZGV2L21hcmtldHBsYWNlP2NhdGVnb3J5PUZhY2Vib29rJnNlYXJjaD0mc29ydD0iO3M6NToicm91dGUiO3M6MTE6Im1hcmtldHBsYWNlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1779589986
K9f8sO1hBeYowmDccjBt9X0Lfusvm7ejzdhzTqWU	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoib0hoYXRQTTE4ZEpPcG5hQWpKdU5tMzNVMk1uUU9aWEM5QUNrMUc5dSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTEyOiJodHRwOi8vODE2YWMyYzctMTcyNS00MzVjLTlhZmQtZjNmMjBjMzRjM2VjLTAwLWZ2bHY0Z2R1ajI3aC5yaWtlci5yZXBsaXQuZGV2L21hcmtldHBsYWNlP2NhdGVnb3J5PSZzZWFyY2g9JnNvcnQ9IjtzOjU6InJvdXRlIjtzOjExOiJtYXJrZXRwbGFjZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1779590118
B7e6CNzHlYw4tKiDvfKpW9Zu6eYIQDPRNeh1S2qF	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiaHdKVGhYYjdSek1iYnp0VkhkbzNrWHBKYWxibllKV3NXV0NnNnpiQiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTEyOiJodHRwOi8vODE2YWMyYzctMTcyNS00MzVjLTlhZmQtZjNmMjBjMzRjM2VjLTAwLWZ2bHY0Z2R1ajI3aC5yaWtlci5yZXBsaXQuZGV2L21hcmtldHBsYWNlP2NhdGVnb3J5PSZzZWFyY2g9JnNvcnQ9IjtzOjU6InJvdXRlIjtzOjExOiJtYXJrZXRwbGFjZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1779590119
DytpQsw1xEh7vfKZO25RKY1W2QZ4iN87FnGPwiVe	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiUmhFMTJPUTl1VWdsVzJzUWtzU0g3OHJxU1lJaWV0ZE1RdVRmbHJTeCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTIxOiJodHRwOi8vODE2YWMyYzctMTcyNS00MzVjLTlhZmQtZjNmMjBjMzRjM2VjLTAwLWZ2bHY0Z2R1ajI3aC5yaWtlci5yZXBsaXQuZGV2L21hcmtldHBsYWNlP2NhdGVnb3J5PUluc3RhZ3JhbSZzZWFyY2g9JnNvcnQ9IjtzOjU6InJvdXRlIjtzOjExOiJtYXJrZXRwbGFjZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1779590127
PKjJFAqDoTJfD9i4kZi4PedP1MJIXDOndzpygyad	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiWHVmdnNWdWpzdTN2TjRUeU9URHZ0cHBUZ0JzVVpiUWt2cTE5ZzBWWSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzY6Imh0dHA6Ly84MTZhYzJjNy0xNzI1LTQzNWMtOWFmZC1mM2YyMGMzNGMzZWMtMDAtZnZsdjRnZHVqMjdoLnJpa2VyLnJlcGxpdC5kZXYiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1779606821
romeVCynmyG12BgcfIfdaKb7wUnkKb1XdMcbq3lj	\N	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/140.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiV1d0QnA0RW1MdkJIOW9GRkQ0N05yd2xOREl0WWJIQWdVZjFDS0Q1aCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6NTAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1779588755
LwlOyajUSWkiPqIhaFCaLNukEkKnlLAjHrLZ1czF	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiQkw1dFFLQWlHZU41U2VEMmVRR295bm5xVjlWdERrTjFTbFphZlNKRyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6OTE6Imh0dHA6Ly84MTZhYzJjNy0xNzI1LTQzNWMtOWFmZC1mM2YyMGMzNGMzZWMtMDAtZnZsdjRnZHVqMjdoLnJpa2VyLnJlcGxpdC5kZXYvYWRtaW4vbGlzdGluZ3MiO3M6NToicm91dGUiO3M6MTQ6ImFkbWluLmxpc3RpbmdzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1779589834
KYWNI9TK2OztojIZiKWmQKUMo31wNAB97P5Nti07	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiNDR5dGxhQzBUVDhPaUxCZWI1cVF5Zzd6SUlxT3F3YWVnMDk1Sk1BdCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6ODc6Imh0dHA6Ly84MTZhYzJjNy0xNzI1LTQzNWMtOWFmZC1mM2YyMGMzNGMzZWMtMDAtZnZsdjRnZHVqMjdoLnJpa2VyLnJlcGxpdC5kZXYvYWRtaW5sb2dpbiI7czo1OiJyb3V0ZSI7czoxMToiYWRtaW4ubG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1779589834
EFGctOJuNCy5TyQUfe6k8RxXiwgUrpVuLwQ9CyeB	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiMTRFWlMzMTUxS2RZYmNWUHFTT21KOXM4NWY4RGF2VkhwdkYwUEZ1bSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6ODc6Imh0dHA6Ly84MTZhYzJjNy0xNzI1LTQzNWMtOWFmZC1mM2YyMGMzNGMzZWMtMDAtZnZsdjRnZHVqMjdoLnJpa2VyLnJlcGxpdC5kZXYvYWRtaW5sb2dpbiI7czo1OiJyb3V0ZSI7czoxMToiYWRtaW4ubG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1779589835
77NzmllUjIupSEhcEjKwK1ZF4IbRjlbM5SkA3KzY	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoia0E0SlBqbVk1OTRiVVFUc3B2NmhOcnJyRDJsSnlTQ2E5dlc1Tmt3aSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6ODc6Imh0dHA6Ly84MTZhYzJjNy0xNzI1LTQzNWMtOWFmZC1mM2YyMGMzNGMzZWMtMDAtZnZsdjRnZHVqMjdoLnJpa2VyLnJlcGxpdC5kZXYvYWRtaW5sb2dpbiI7czo1OiJyb3V0ZSI7czoxMToiYWRtaW4ubG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1779589835
H5XAfExGR8jVkypBqOasMLf85f1JbE8PsAnAFfyh	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiRDFYcng1cE9jZEtQNEJWZmdVY1l3QVF4bzJkVTB1RmE3OTB4ZGk2MCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6OTg6Imh0dHA6Ly84MTZhYzJjNy0xNzI1LTQzNWMtOWFmZC1mM2YyMGMzNGMzZWMtMDAtZnZsdjRnZHVqMjdoLnJpa2VyLnJlcGxpdC5kZXYvYWRtaW4vbGlzdGluZ3MvMS9lZGl0IjtzOjU6InJvdXRlIjtzOjE5OiJhZG1pbi5saXN0aW5ncy5lZGl0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1779589837
jSa6gbFOYDDy3jxcVsOyUvBMzTnv8t6ys0L9vjRD	\N	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiSGpjb0o0RUI2RGpTSTl2cWRCbmhvZGl4ZmlrUzc3MFowYk1SRkxyaCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6ODc6Imh0dHA6Ly84MTZhYzJjNy0xNzI1LTQzNWMtOWFmZC1mM2YyMGMzNGMzZWMtMDAtZnZsdjRnZHVqMjdoLnJpa2VyLnJlcGxpdC5kZXYvYWRtaW5sb2dpbiI7czo1OiJyb3V0ZSI7czoxMToiYWRtaW4ubG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1779589837
v1UMku5bqyFBSBU552uoAT2d4XIoShQz6YawVWV3	\N	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/140.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoieWZTOXhaMmwybmc2VmpSMU1aNUt4Y3dvNHFhVFJtempYanZST2dLTyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6NTAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1779589852
OaXHxgxfmggFjCdHIe9XU1ISkXrDD2GAvafXBIHL	1	127.0.0.1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36	YTo3OntzOjY6Il90b2tlbiI7czo0MDoiZ3VFc0J4S2JJMm5iZU8xcFZFWnNoa25FbEZuczBrb2Z0V0hONjNKeSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6OTQ6Imh0dHA6Ly84MTZhYzJjNy0xNzI1LTQzNWMtOWFmZC1mM2YyMGMzNGMzZWMtMDAtZnZsdjRnZHVqMjdoLnJpa2VyLnJlcGxpdC5kZXYvZGFzaGJvYXJkL3N1cHBvcnQiO3M6NToicm91dGUiO3M6MTc6ImRhc2hib2FyZC5zdXBwb3J0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjg6ImFkbWluX2lkIjtpOjI7czoxMToiYWRtaW5fZW1haWwiO3M6MTU6ImFkbWluQGJsdWVzLmNvbSI7czoxMDoiYWRtaW5fbmFtZSI7czoxMToiU3VwZXIgQWRtaW4iO30=	1779590383
\.


--
-- Data for Name: settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.settings (id, key, value, created_at, updated_at) FROM stdin;
1	paystack_public_key	\N	2026-05-24 02:19:33	2026-05-24 02:19:33
2	paystack_secret_key	\N	2026-05-24 02:19:33	2026-05-24 02:19:33
3	paystack_webhook_secret	\N	2026-05-24 02:19:33	2026-05-24 02:19:33
4	site_name	Blues Marketplace	2026-05-24 02:19:33	2026-05-24 02:19:33
5	support_email	\N	2026-05-24 02:19:33	2026-05-24 02:19:33
6	min_deposit	500	2026-05-24 02:19:33	2026-05-24 02:19:33
7	max_deposit	1000000	2026-05-24 02:19:33	2026-05-24 02:19:33
8	logsplug_api_key	sk_live_9282a206e65070fcd9108e1a6eb359534c4714d7814dead8e054d0fd6d52547b	2026-05-24 02:19:33	2026-05-24 02:19:33
9	logsplug_api_url	https://logsplug.com/api	2026-05-24 02:19:33	2026-05-24 02:19:33
10	maintenance_mode	0	2026-05-24 02:19:33	2026-05-24 02:19:33
11	virtual_number_enabled	1	2026-05-24 02:19:33	2026-05-24 02:19:33
12	whatsapp_number	2348012345678	2026-05-24 02:30:47	2026-05-24 02:30:47
\.


--
-- Data for Name: support_tickets; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.support_tickets (id, user_id, subject, message, admin_reply, status, priority, created_at, updated_at) FROM stdin;
1	1	Help	help me	Thanks for reaching outto us	closed	high	2026-05-24 02:38:31	2026-05-24 02:39:32
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, status) FROM stdin;
1	Teemost	agboolasamul09@gmail.com	\N	$2y$12$N83lxo8ul/L7efnUlWj08OsBEydNZarU7/kSQc96.LhA1FEIlsoEi	\N	2026-05-23 21:34:21	2026-05-23 21:34:21	active
\.


--
-- Data for Name: virtual_number_orders; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.virtual_number_orders (id, user_id, external_order_id, service, country, phone_number, sms_code, cost, status, raw_response, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: wallet_transactions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.wallet_transactions (id, user_id, amount, type, reference, description, created_at, updated_at) FROM stdin;
1	1	100.00	deposit	DEP-1A3F988C	Wallet top-up	2026-05-23 21:35:48	2026-05-23 21:35:48
2	1	5000.00	deposit	DEP-25F3097C	Wallet top-up	2026-05-23 22:01:28	2026-05-23 22:01:28
3	1	-3000.00	purchase	PUR-1	Purchase: Verified facebook account	2026-05-23 22:02:06	2026-05-23 22:02:06
\.


--
-- Data for Name: wallets; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.wallets (id, user_id, balance, created_at, updated_at) FROM stdin;
1	1	2100.00	2026-05-23 21:34:21	2026-05-23 22:02:06
\.


--
-- Data for Name: wishlists; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.wishlists (id, user_id, listing_id, created_at, updated_at) FROM stdin;
1	1	1	2026-05-23 22:00:37	2026-05-23 22:00:37
2	1	2	2026-05-24 02:37:36	2026-05-24 02:37:36
\.


--
-- Name: admin_audit_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.admin_audit_log_id_seq', 1, false);


--
-- Name: admins_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.admins_users_id_seq', 2, true);


--
-- Name: announcements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.announcements_id_seq', 1, false);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: listing_categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.listing_categories_id_seq', 6, true);


--
-- Name: listings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.listings_id_seq', 2, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 25, true);


--
-- Name: notifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.notifications_id_seq', 1, true);


--
-- Name: profiles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.profiles_id_seq', 1, true);


--
-- Name: purchases_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.purchases_id_seq', 1, true);


--
-- Name: settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.settings_id_seq', 12, true);


--
-- Name: support_tickets_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.support_tickets_id_seq', 1, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 1, true);


--
-- Name: virtual_number_orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.virtual_number_orders_id_seq', 1, false);


--
-- Name: wallet_transactions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.wallet_transactions_id_seq', 3, true);


--
-- Name: wallets_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.wallets_id_seq', 1, true);


--
-- Name: wishlists_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.wishlists_id_seq', 2, true);


--
-- Name: admin_audit_log admin_audit_log_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin_audit_log
    ADD CONSTRAINT admin_audit_log_pkey PRIMARY KEY (id);


--
-- Name: admins_users admins_users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admins_users
    ADD CONSTRAINT admins_users_email_unique UNIQUE (email);


--
-- Name: admins_users admins_users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admins_users
    ADD CONSTRAINT admins_users_pkey PRIMARY KEY (id);


--
-- Name: announcements announcements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: listing_categories listing_categories_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.listing_categories
    ADD CONSTRAINT listing_categories_name_unique UNIQUE (name);


--
-- Name: listing_categories listing_categories_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.listing_categories
    ADD CONSTRAINT listing_categories_pkey PRIMARY KEY (id);


--
-- Name: listings listings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.listings
    ADD CONSTRAINT listings_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: profiles profiles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.profiles
    ADD CONSTRAINT profiles_pkey PRIMARY KEY (id);


--
-- Name: profiles profiles_referral_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.profiles
    ADD CONSTRAINT profiles_referral_code_unique UNIQUE (referral_code);


--
-- Name: profiles profiles_username_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.profiles
    ADD CONSTRAINT profiles_username_unique UNIQUE (username);


--
-- Name: purchases purchases_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchases
    ADD CONSTRAINT purchases_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: settings settings_key_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_key_unique UNIQUE (key);


--
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (id);


--
-- Name: support_tickets support_tickets_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.support_tickets
    ADD CONSTRAINT support_tickets_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: virtual_number_orders virtual_number_orders_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.virtual_number_orders
    ADD CONSTRAINT virtual_number_orders_pkey PRIMARY KEY (id);


--
-- Name: wallet_transactions wallet_transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wallet_transactions
    ADD CONSTRAINT wallet_transactions_pkey PRIMARY KEY (id);


--
-- Name: wallets wallets_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wallets
    ADD CONSTRAINT wallets_pkey PRIMARY KEY (id);


--
-- Name: wallets wallets_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wallets
    ADD CONSTRAINT wallets_user_id_unique UNIQUE (user_id);


--
-- Name: wishlists wishlists_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wishlists
    ADD CONSTRAINT wishlists_pkey PRIMARY KEY (id);


--
-- Name: wishlists wishlists_user_id_listing_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wishlists
    ADD CONSTRAINT wishlists_user_id_listing_id_unique UNIQUE (user_id, listing_id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: virtual_number_orders_external_order_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX virtual_number_orders_external_order_id_index ON public.virtual_number_orders USING btree (external_order_id);


--
-- Name: admin_audit_log admin_audit_log_admin_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin_audit_log
    ADD CONSTRAINT admin_audit_log_admin_id_foreign FOREIGN KEY (admin_id) REFERENCES public.admins_users(id) ON DELETE SET NULL;


--
-- Name: notifications notifications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: profiles profiles_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.profiles
    ADD CONSTRAINT profiles_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: purchases purchases_listing_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchases
    ADD CONSTRAINT purchases_listing_id_foreign FOREIGN KEY (listing_id) REFERENCES public.listings(id) ON DELETE CASCADE;


--
-- Name: purchases purchases_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchases
    ADD CONSTRAINT purchases_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: support_tickets support_tickets_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.support_tickets
    ADD CONSTRAINT support_tickets_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: virtual_number_orders virtual_number_orders_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.virtual_number_orders
    ADD CONSTRAINT virtual_number_orders_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: wallet_transactions wallet_transactions_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wallet_transactions
    ADD CONSTRAINT wallet_transactions_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: wallets wallets_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wallets
    ADD CONSTRAINT wallets_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: wishlists wishlists_listing_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wishlists
    ADD CONSTRAINT wishlists_listing_id_foreign FOREIGN KEY (listing_id) REFERENCES public.listings(id) ON DELETE CASCADE;


--
-- Name: wishlists wishlists_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wishlists
    ADD CONSTRAINT wishlists_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict SYKgri8gSPZG3b87M6VYnQD5F0DEW0Ph7FagdT7HLokGgG649WnasUha8NcbTUv

