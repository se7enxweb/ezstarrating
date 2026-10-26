-- ezstarrating_data.session_key holds a SHA-256 hash of the session id (64 hex characters)
-- instead of the session id itself. Existing rows are hashed in place, so a session that rated
-- before the update still finds its rating afterwards; rows without a session stay empty.
-- sha256() needs PostgreSQL 11 or later.
-- A second run changes nothing: only values of up to 32 characters (the old column width, so raw
-- session ids) are hashed, and a hash has 64.
ALTER TABLE ezstarrating_data ALTER COLUMN session_key TYPE character varying(64);
UPDATE ezstarrating_data SET session_key = encode(sha256(convert_to(session_key, 'UTF8')), 'hex') WHERE session_key <> '' AND length(session_key) <= 32;
