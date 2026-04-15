-- Ejecutado automaticamente en el primer arranque de Postgres.
-- Si el volumen ya existe, recrealo para aplicar estos permisos base.

CREATE SCHEMA IF NOT EXISTS tini_raw;
CREATE SCHEMA IF NOT EXISTS pherce_intel;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'ferreteria_app') THEN
        CREATE ROLE ferreteria_app LOGIN PASSWORD 'ferreteria_local';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'ferreteria_etl') THEN
        CREATE ROLE ferreteria_etl LOGIN PASSWORD 'ferreteria_local';
    END IF;
END
$$;

GRANT CONNECT ON DATABASE ferreteria TO ferreteria_app;
GRANT CONNECT ON DATABASE ferreteria TO ferreteria_etl;

-- App principal: escribe en public / pherce_intel y solo lee tini_raw.
GRANT USAGE, CREATE ON SCHEMA public TO ferreteria_app;
GRANT USAGE, CREATE ON SCHEMA pherce_intel TO ferreteria_app;
GRANT USAGE ON SCHEMA tini_raw TO ferreteria_app;

GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO ferreteria_app;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA pherce_intel TO ferreteria_app;
GRANT SELECT ON ALL TABLES IN SCHEMA tini_raw TO ferreteria_app;

GRANT USAGE, SELECT, UPDATE ON ALL SEQUENCES IN SCHEMA public TO ferreteria_app;
GRANT USAGE, SELECT, UPDATE ON ALL SEQUENCES IN SCHEMA pherce_intel TO ferreteria_app;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA tini_raw TO ferreteria_app;

ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO ferreteria_app;
ALTER DEFAULT PRIVILEGES IN SCHEMA pherce_intel
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO ferreteria_app;
ALTER DEFAULT PRIVILEGES IN SCHEMA tini_raw
    GRANT SELECT ON TABLES TO ferreteria_app;

ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT USAGE, SELECT, UPDATE ON SEQUENCES TO ferreteria_app;
ALTER DEFAULT PRIVILEGES IN SCHEMA pherce_intel
    GRANT USAGE, SELECT, UPDATE ON SEQUENCES TO ferreteria_app;
ALTER DEFAULT PRIVILEGES IN SCHEMA tini_raw
    GRANT USAGE, SELECT ON SEQUENCES TO ferreteria_app;

-- ETL: unico escritor permitido sobre tini_raw.
GRANT USAGE, CREATE ON SCHEMA tini_raw TO ferreteria_etl;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA tini_raw TO ferreteria_etl;
GRANT USAGE, SELECT, UPDATE ON ALL SEQUENCES IN SCHEMA tini_raw TO ferreteria_etl;

ALTER DEFAULT PRIVILEGES IN SCHEMA tini_raw
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO ferreteria_etl;
ALTER DEFAULT PRIVILEGES IN SCHEMA tini_raw
    GRANT USAGE, SELECT, UPDATE ON SEQUENCES TO ferreteria_etl;

ALTER DATABASE ferreteria SET search_path TO public, pherce_intel, tini_raw;

COMMENT ON SCHEMA public IS 'System base: users, roles, branches, auth, cache, jobs';
COMMENT ON SCHEMA tini_raw IS 'Read-only replica of TINI .dat files (ETL-only writes)';
COMMENT ON SCHEMA pherce_intel IS 'Pherce intelligence layer: stock states, thresholds, alerts, confirmations';
