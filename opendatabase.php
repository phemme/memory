<?php

// parametres d'access
$bdd='websitecugautonh';
$user='websitecugautonh';
$passwd='PH123wscpro';
$host='websitecugautonh.mysql.db';

$conn = new mysqli($host, $user,$passwd,$bdd );

// Check connection
if ($conn->connect_error) {
    die("Echec Connection: " . $conn->connect_error);
} 
