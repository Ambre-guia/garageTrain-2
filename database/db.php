<?php

function connectDB(){
  $host = '151.80.32.16:6033';
  $user = 'if0_35606101';
  $password = 'UT57Hj7nKcA';
  $database = 'if0_35606101_garage_train';
  $conn = new mysqli($host, $user, $password, $database);

  if ($conn->connect_error) {
    die("La connexion à la base de données a échoué : " . $conn->connect_error);
  }
  else{
    return $conn;
  }
}

