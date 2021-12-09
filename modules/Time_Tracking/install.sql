
/**********************************************************************
 install.sql file
 Required as the module adds programs to other modules
 - Add profile exceptions for the module to appear in the menu
 - Add Email templates
***********************************************************************/

/*******************************************************
 profile_id:
 	- 0: student
 	- 1: admin
 	- 2: teacher
 	- 3: parent
 modname: should match the Menu.php entries
 can_use: 'Y'
 can_edit: 'Y' or null (generally null for non admins)
*******************************************************/
--
-- Data for Name: profile_exceptions; Type: TABLE DATA;
--

CREATE FUNCTION create_language_plpgsql()
RETURNS BOOLEAN AS $$
    CREATE LANGUAGE plpgsql;
    SELECT TRUE;
$$ LANGUAGE SQL;

SELECT CASE WHEN NOT (
    SELECT TRUE AS exists FROM pg_language
    WHERE lanname='plpgsql'
    UNION
    SELECT FALSE AS exists
    ORDER BY exists DESC
    LIMIT 1
) THEN
    create_language_plpgsql()
ELSE
    FALSE
END AS plpgsql_created;

DROP FUNCTION create_language_plpgsql();

-----------------------------------------------------------------------------------------

INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Time_Tracking/TimeSheet.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Time_Tracking/TimeSheet.php'
    AND profile_id=1);

INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 2, 'Time_Tracking/TimeSheet.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Time_Tracking/TimeSheet.php'
    AND profile_id=2);

---------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION create_table_timetracking_timesheets() RETURNS void AS
$func$
BEGIN
    IF EXISTS (SELECT 1 FROM pg_catalog.pg_tables
        WHERE schemaname = CURRENT_SCHEMA()
        AND tablename = 'timetracking_timesheets') THEN
    RAISE NOTICE 'Table "timetracking_timesheets" already exists.';
    ELSE
        CREATE TABLE timetracking_timesheets (
            id serial PRIMARY KEY,
            syear numeric(4,0) NOT NULL,
            school_id integer NOT NULL,
            staff_id integer,
            "time" numeric NOT NULL,
            logged_date date,
            comments text,
            created_at timestamp DEFAULT current_timestamp,
            updated_at timestamp DEFAULT current_timestamp
        );
        CREATE TRIGGER set_updated_at
            BEFORE UPDATE ON timetracking_timesheets
            FOR EACH ROW EXECUTE PROCEDURE set_updated_at();
    END IF;
END
$func$ LANGUAGE plpgsql;

SELECT create_table_timetracking_timesheets();
DROP FUNCTION create_table_timetracking_timesheets();

--------------------------------------------------

CREATE OR REPLACE FUNCTION create_index_timetracking_timesheets_ind1() RETURNS void AS
$func$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_class c
        JOIN pg_namespace n ON n.oid=c.relnamespace
        WHERE c.relname='timetracking_timesheets_ind1'
        AND n.nspname=CURRENT_SCHEMA()
    ) THEN
        CREATE INDEX ON timetracking_timesheets USING btree
    	(staff_id ASC NULLS LAST);
    END IF;
END
$func$ LANGUAGE plpgsql;

SELECT create_index_timetracking_timesheets_ind1();
DROP FUNCTION create_index_timetracking_timesheets_ind1();


--------------------------------------------------

CREATE OR REPLACE FUNCTION create_index_timetracking_timesheets_ind2() RETURNS void AS
$func$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_class c
        JOIN pg_namespace n ON n.oid=c.relnamespace
        WHERE c.relname='timetracking_timesheets_ind2'
        AND n.nspname=CURRENT_SCHEMA()
    ) THEN
        CREATE INDEX ON timetracking_timesheets USING btree
    	("time" ASC NULLS LAST);
    END IF;
END
$func$ LANGUAGE plpgsql;

SELECT create_index_timetracking_timesheets_ind2();
DROP FUNCTION create_index_timetracking_timesheets_ind2();

--------------------------------------------------