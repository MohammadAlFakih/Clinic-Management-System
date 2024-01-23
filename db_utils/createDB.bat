echo Beginning...

mysql -u root -h localhost -e "CREATE DATABASE IF NOT EXISTS clinic_db;"
mysql -u root -h localhost -D clinic_db < .\db_utils\createTable.sql
mysql -u root -h localhost -D clinic_db < .\db_utils\insertTestData.sql

echo End of batch file