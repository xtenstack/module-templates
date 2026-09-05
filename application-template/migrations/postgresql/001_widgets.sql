-- Demo entity for the application-template module — deliberately trivial
-- (a name + description and nothing else meaningful); the point is
-- demonstrating the module migration contract, not a real feature.
CREATE TABLE widgets (
    id           SERIAL PRIMARY KEY,
    name         VARCHAR(150) NOT NULL,
    description  TEXT,
    deleted_at   TIMESTAMP,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
