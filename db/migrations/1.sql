CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fname TEXT(30) NULL,
    lname TEXT(45) NULL,
    email TEXT(256) NOT NULL UNIQUE ON CONFLICT ROLLBACK,
    role TEXT(12) NOT NULL DEFAULT 'normal',
    password_digest TEXT(256) NOT NULL
);

CREATE TABLE IF NOT EXISTS redirects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    users_id INTEGER(10) NOT NULL,
    alias TEXT(99) NOT NULL UNIQUE ON CONFLICT ROLLBACK,
    dest TEXT(800) NOT NULL,
    is_regex INTEGER NOT NULL DEFAULT 0,
    hits INTEGER(10) NOT NULL DEFAULT 0,
    hits_qr INTEGER(10) NOT NULL DEFAULT 0
);

