Deployment Checklist:
1. Upload all files to the server.
2. Create the MySQL database and import sql/schema.sql.
3. Update config/database.php with production DB credentials.
4. Ensure the uploads/ directory and its subdirectories have write permissions (chmod 777 or 755 depending on server).
5. If using Apache, make sure mod_rewrite is enabled to use the .htaccess file.
