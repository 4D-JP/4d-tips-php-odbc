<?php




$connect = odbc_connect("4D_V15_32", "Designer", "");
// $connect = odbc_connect("DRIVER={4D v15 ODBC Driver 32-bit};Server=127.0.0.1;Port=19812;", "Designer", "");

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
