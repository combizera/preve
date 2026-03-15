SELECT 'CREATE DATABASE preve_testing'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'preve_testing')\gexec
