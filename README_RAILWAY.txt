HANATA BATIK - RAILWAY READY

This package is configured for Railway:
- PHP 8.2 CLI (no Apache, so no MPM conflict)
- mysqli installed during Docker build
- Railway MySQL variables supported:
  MYSQLHOST, MYSQLPORT, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE
- localhost fallback remains for local development
- Dockerfile is at repository root
- Railway-safe SQL: db_2355202045_batik_railway.sql

Repository layout MUST be:
index.php
includes/
admin/
assets/
uploads/
Dockerfile
railway.toml

Do not place all files inside an extra "toko-batik-andri" subfolder unless you set
Railway Root Directory accordingly.

Railway steps:
1. Put the contents of this folder at the GitHub repository root.
2. Connect the repo to a Railway Web Service.
3. Add a Railway MySQL service in the same project.
4. Make the Web Service use the MySQL service variables via Reference Variables,
   or expose the MYSQL* variables on the Web Service.
5. Import db_2355202045_batik_railway.sql into the Railway MySQL database.
6. Generate a public domain from Service Settings -> Networking.

Uploaded files use the container filesystem. Railway service storage is ephemeral,
so production image persistence should use object storage/volume.
