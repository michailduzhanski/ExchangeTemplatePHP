<?php
$connStr = "host=localhost port=5432 dbname=liteconstruct_db user=liteconstruct password=Ljcneg119522";

//simple check
$conn = pg_connect($connStr);
pg_query($conn, "delete from site_contact_auth where time < '" . round(microtime(true) * 1000) . "'");

?>