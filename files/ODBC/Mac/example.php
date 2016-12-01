<?php

putenv("ODBCINSTINI=/Library/ODBC/odbcinst.ini");
putenv("ODBCINI=/Library/ODBC/odbc.ini");

$connect = odbc_connect("4D_v15_64", "Designer", "");
// $connect = odbc_connect("Driver={4D v15 ODBC Driver 64-bit};Server=127.0.0.1;Port=19812;", "Designer", "");

$insert = "INSERT INTO Table_1 (Field_2) VALUES ('あいうえお'), ('かきくけこ'), ('さしすせそ')";
$result = odbc_do($connect, $insert);

$select = "SELECT Field_2 FROM Table_1";
$result = odbc_do($connect, $select);
while(odbc_fetch_row($result)){
        for($i=1;$i<=odbc_num_fields($result);$i++){
        echo odbc_result($result,$i)."\n";
    }
}

odbc_close($connect);
