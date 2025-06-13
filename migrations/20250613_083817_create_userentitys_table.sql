-- Migration auto-générée
CREATE TABLE IF NOT EXISTS users (
  id serial PRIMARY KEY NOT NULL,
  email varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL
);



CREATE OR REPLACE FUNCTION set_users_updated_at() RETURNS TRIGGER AS $$
BEGIN
  NEW."updated_at" = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_set_users_updated_at
BEFORE UPDATE ON users
FOR EACH ROW
EXECUTE FUNCTION set_users_updated_at();;