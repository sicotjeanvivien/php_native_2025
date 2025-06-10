-- Migration auto-générée
CREATE TABLE IF NOT EXISTS users (
  id serial PRIMARY KEY NOT NULL,
  email varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  createdAt timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
  updatedAt timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL
);
