-- 02-sync-existing-volume.sql
--
-- Uso: aplicar cuando el volumen de Postgres ya existia antes de la topologia
-- ferreteria_app / ferreteria_etl, o cuando sospeches que los grants se
-- salieron de sincronia (por ejemplo, despues de correr migraciones con un
-- usuario distinto a ferreteria_app).
--
-- Este script es 100% idempotente: usa IF NOT EXISTS y GRANT (que no falla
-- si el privilegio ya esta otorgado). Es seguro correrlo multiples veces.
--
-- Ejemplo de ejecucion manual:
--     psql -U postgres -d ferreteria -f docker/postgres/init/02-sync-existing-volume.sql
--
-- IMPORTANTE: los ALTER DEFAULT PRIVILEGES de 01-create-schemas.sql solo
-- cubren tablas FUTURAS creadas por el rol que los declara. Si hay tablas
-- existentes con otro owner, los GRANT ... ON ALL TABLES de abajo son los
-- que corrigen el estado actual.

BEGIN;

-- 1. Schemas ------------------------------------------------------------

CREATE SCHEMA IF NOT EXISTS tini_raw;
CREATE SCHEMA IF NOT EXISTS pherce_intel;

COMMENT ON SCHEMA public IS 'System base: users, roles, branches, auth, cache, jobs';
COMMENT ON SCHEMA tini_raw IS 'Read-only replica of TINI .dat files (ETL-only writes)';
COMMENT ON SCHEMA pherce_intel IS 'Pherce intelligence layer: stock states, thresholds, alerts, confirmations';

-- 2. Roles (idempotentes) -----------------------------------------------

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

-- 3. Conexion a la base -------------------------------------------------

GRANT CONNECT ON DATABASE ferreteria TO ferreteria_app;
GRANT CONNECT ON DATABASE ferreteria TO ferreteria_etl;

-- 4. ferreteria_app: RW en public/pherce_intel, solo SELECT en tini_raw -

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

-- 5. ferreteria_etl: unico escritor permitido sobre tini_raw ------------

GRANT USAGE, CREATE ON SCHEMA tini_raw TO ferreteria_etl;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA tini_raw TO ferreteria_etl;
GRANT USAGE, SELECT, UPDATE ON ALL SEQUENCES IN SCHEMA tini_raw TO ferreteria_etl;

ALTER DEFAULT PRIVILEGES IN SCHEMA tini_raw
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO ferreteria_etl;
ALTER DEFAULT PRIVILEGES IN SCHEMA tini_raw
    GRANT USAGE, SELECT, UPDATE ON SEQUENCES TO ferreteria_etl;

-- 6. search_path a nivel de base ----------------------------------------

ALTER DATABASE ferreteria SET search_path TO public, pherce_intel, tini_raw;

COMMIT;

-- -------------------------------------------------------------------------
-- Opcional (no ejecutado por defecto): si las tablas de public / pherce_intel
-- se crearon originalmente con otro owner (por ejemplo 'postgres') y quieres
-- que ferreteria_app sea el dueno para que los futuros ALTER DEFAULT
-- PRIVILEGES cubran todo automaticamente, descomenta los bloques siguientes.
-- Esto reasigna ownership en bulk. Revisalo antes de ejecutarlo.
--
-- REASSIGN OWNED BY postgres TO ferreteria_app;
--
-- Alternativa mas quirurgica (recomendada si no controlas al resto del cluster):
--
-- DO $$
-- DECLARE r record;
-- BEGIN
--     FOR r IN SELECT tablename FROM pg_tables WHERE schemaname IN ('public','pherce_intel')
--     LOOP
--         EXECUTE format('ALTER TABLE %I.%I OWNER TO ferreteria_app', r.schemaname, r.tablename);
--     END LOOP;
-- END
-- $$;
