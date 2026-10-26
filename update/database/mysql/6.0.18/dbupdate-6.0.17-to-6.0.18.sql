-- ezstarrating_data.session_key holds a SHA-256 hash of the session id (64 hex characters)
-- instead of the session id itself. Existing rows are hashed in place, so a session that rated
-- before the update still finds its rating afterwards; rows without a session stay empty.
-- A second run changes nothing: only values of up to 32 characters (the old column width, so raw
-- session ids) are hashed, and a hash has 64.
ALTER TABLE ezstarrating_data MODIFY session_key varchar(64) NOT NULL;
UPDATE ezstarrating_data SET session_key = SHA2(session_key, 256) WHERE session_key <> '' AND CHAR_LENGTH(session_key) <= 32;
