<?php include('view/header.php'); ?>
<?php include('view/sidebar.php'); ?>

    <div class="main-panel">

	<?php
		include('view/menu-top.php');
	?>

        <div class="content">
            <div class="container-fluid">
                <div class="row">
<pre>
<?php

$content = new FCWOrders();
print_r($content->getData());

/*
$host = '192.168.15.16:C:\Users\User\Downloads\DADOSNEW\SABOLLAN.GD';
$username = 'SYSDBA';
$password = 'masterkey';


$fb = new PDO("firebird:dbname=$host", $username, $password);

print_r($fb);

$dbh = ibase_connect($host, $username, $password);
$stmt = 'select first 10 * from os order by DATACRIA desc, DATAALT desc';
$sth = ibase_query($dbh, $stmt);
while ($row = ibase_fetch_object($sth)) {
    print($row->OS) . "<br>";
}
ibase_free_result($sth);
ibase_close($dbh);
*/

$pdo = new PDO("mysql:host=192.168.15.10;port=3386;dbname=fcw_pool", "fcw", "masterkey"); 

//$pdo->query("CREATE TABLE users ( id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,username VARCHAR(30) NOT NULL,email VARCHAR(50),created TIMESTAMP,updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

foreach($pdo->query('Show Tables') as $row) {
    print_r($row);
}



?>
</pre>


                </div>
            </div>
        </div>

	<?php
		include('view/footer.php');
	?>
