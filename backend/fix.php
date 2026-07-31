<?php
$db = new PDO('sqlite:d:/hub/sosmed-web/backend/database/database.sqlite');
$db->exec("UPDATE posts SET image_path = REPLACE(image_path, 'http://localhost', 'http://127.0.0.1:8000')");
echo "Done!\n";
