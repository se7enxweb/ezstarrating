-- ezstarrating_data.session_key holds a SHA-256 hash of the session id (64 hex characters)
-- instead of the session id itself. SQLite does not enforce the column length, so the table
-- keeps its definition. SQLite has no built-in SHA-256 either: the stored session ids are
-- cleared instead of hashed. The ratings and their averages stay; anonymous visitors who rated
-- before the update may rate the same content once more in their current session.
-- Hashes the code stored already (64 characters) stay, so a second run changes nothing more.
UPDATE ezstarrating_data SET session_key = '' WHERE session_key <> '' AND length(session_key) <> 64;
