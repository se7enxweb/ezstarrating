-- ezstarrating_data.session_key holds a SHA-256 hash of the session id (64 hex characters)
-- instead of the session id itself. Existing rows are hashed in place, so a session that rated
-- before the update still finds its rating afterwards.
-- STANDARD_HASH needs Oracle 12c or later.
-- A second run changes nothing: only values of up to 32 characters (the old column width, so raw
-- session ids) are hashed, and a hash has 64.
ALTER TABLE ezstarrating_data MODIFY ( session_key varchar2(64) );
UPDATE ezstarrating_data SET session_key = LOWER(RAWTOHEX(STANDARD_HASH(session_key, 'SHA256'))) WHERE session_key IS NOT NULL AND LENGTH(session_key) <= 32;
