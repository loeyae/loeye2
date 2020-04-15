
DirectoryIndex index.html Dispatcher.php index.htm
Options -Indexes

SetEnv routerDir "general"
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule .* Dispatcher.php [QSA,L]