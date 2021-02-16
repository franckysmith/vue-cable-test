<?
// File:       _dbinit.php.php
// Contents:   service script to init/modify the database
// Created:    21.01.2021
// Programmer: Edward A. Shiryaev

require_once 'config.php';
require_once 'db.php';
require_once 'util.php';

if(@$_GET['password'] != 'cables')
  exit('Authorization error');
  
        // Database schema SQL-queries as i-array to add new table(s) to the database.
        // total    - number of pieces that the company totally has
        // reserved - number of pieces kept apart by the company (reserved by the company)
  
$SCHEMA_SCRIPT = 
[
  'CREATE TABLE cable
  (
    cableid       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(25) NOT NULL,
    type          ENUM("electrical", "speaker", "microphone"),
    
    total         INT UNSIGNED NOT NULL DEFAULT 0,
    reserved      INT UNSIGNED NOT NULL DEFAULT 0,
    
    info          VARCHAR(255),
    link          VARCHAR(255),
    
    timestamp     TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
    
    PRIMARY KEY   (cableid),
    UNIQUE KEY    (name)
  ) engine=innoDB collate utf8_general_ci',
  
  
  'CREATE TABLE affair
  (
    affairid      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    tech_id       INT UNSIGNED NOT NULL,
    tech_name     VARCHAR(50) NOT NULL,
    
    name          VARCHAR(50) NOT NULL,
    ref           VARCHAR(50),
    
    prep_date     DATE,
    prep_time     ENUM("morning", "afternoon"),
    receipt_date  DATE NOT NULL,
    receipt_time  ENUM("morning", "afternoon"),
    return_date   DATE NOT NULL,
    return_time   ENUM("morning", "afternoon"),
    
    front         BOOLEAN NOT NULL DEFAULT 0,
    monitor       BOOLEAN NOT NULL DEFAULT 0,
    stage         BOOLEAN NOT NULL DEFAULT 0,
  
    master_note   TEXT,
    tech_note     TEXT,
    
    timestamp     TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
    
    PRIMARY KEY   (affairid),
    UNIQUE KEY    (name, receipt_date),
    UNIQUE KEY    (ref)
  ) engine=innoDB collate utf8_general_ci',
  
  
  // a cable from 'cable' table cannot be deleted if there are orders for this cable
  'CREATE TABLE `order`
  (
    orderid       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    cableid       INT UNSIGNED NOT NULL,
    affairid      INT UNSIGNED NOT NULL,
    tech_id       INT UNSIGNED NOT NULL,

    count         INT UNSIGNED NOT NULL DEFAULT 0,
    done          BOOLEAN NOT NULL DEFAULT 0,
    
    timestamp     TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
  
    PRIMARY KEY   (orderid),
    UNIQUE KEY    (cableid, affairid, tech_id),
    FOREIGN KEY   (cableid) REFERENCES cable (cableid) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY   (affairid) REFERENCES affair(affairid) ON UPDATE CASCADE ON DELETE CASCADE
  ) engine=innoDB collate utf8_general_ci'    
];

foreach($SCHEMA_SCRIPT as $query) {
  echo util::textChunk($query, 50), str_repeat('&nbsp;', 50);
  if(db::query($query, false /* silent */))
    echo 'done', '<br>';
  else
    echo db::error(), '<br>';
}
  
echo '<br>Database inited!';
?>