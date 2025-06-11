-- Migration auto-générée
CREATE TABLE IF NOT EXISTS users (
  id serial PRIMARY KEY NOT NULL,
  email varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  createdAt timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
  updatedAt timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL
);



CREATE OR REPLACE FUNCTION set_users_updatedAt() RETURNS TRIGGER AS $$
BEGIN
  NEW."updatedAt" = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_users_updatedAt
BEFORE UPDATE ON users
FOR EACH ROW
EXECUTE FUNCTION set_users_updatedAt();;