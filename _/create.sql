DROP DATABASE ospedale_xia;

CREATE DATABASE ospedale_xia;
USE ospedale_xia;

CREATE TABLE utenze (
   id INT AUTO_INCREMENT PRIMARY KEY,
   usr VARCHAR(100) NOT NULL UNIQUE,
   psw CHAR(60) NOT NULL,
   admin BOOLEAN NOT NULL DEFAULT 0
);

CREATE TABLE reparti (
   id INT AUTO_INCREMENT PRIMARY KEY,
   denominazione VARCHAR(100) NOT NULL
);
INSERT reparti VALUES(null, "Farmacia");

CREATE TABLE posti (
   id INT AUTO_INCREMENT PRIMARY KEY,
   nome VARCHAR(100) NOT NULL,
   cod_reparto INT NOT NULL,
   FOREIGN KEY (cod_reparto) REFERENCES reparti(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE medici (
   id INT AUTO_INCREMENT PRIMARY KEY,
   nome VARCHAR(100) NOT NULL,
   cognome VARCHAR(100) NOT NULL,
   cod_reparto INT NOT NULL,
   cod_utenza INT NOT NULL,
   FOREIGN KEY (cod_utenza) REFERENCES utenze(id) ON DELETE RESTRICT ON UPDATE CASCADE,
   FOREIGN KEY (cod_reparto) REFERENCES reparti(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE pazienti (
   cf CHAR(16) PRIMARY KEY,
   nome VARCHAR(100) NOT NULL,
   cognome VARCHAR(100) NOT NULL,
   ddn DATE NOT NULL,
   sesso CHAR(1) NOT NULL,
   email VARCHAR(100) NOT NULL,
   telefono VARCHAR(20) NOT NULL,
   CHECK(sesso='M' OR sesso='F')
);

CREATE TABLE ricoveri (
   id INT AUTO_INCREMENT PRIMARY KEY,
   data_inizio DATETIME NOT NULL,
   data_fine DATETIME DEFAULT NULL,
   motivo VARCHAR(500),
   cod_medico INT NOT NULL,
   cod_paziente VARCHAR(16) NOT NULL,
   cod_posto INT NOT NULL,
   FOREIGN KEY (cod_medico) REFERENCES medici(id) ON DELETE RESTRICT ON UPDATE CASCADE,
   FOREIGN KEY (cod_paziente) REFERENCES pazienti(cf) ON DELETE RESTRICT ON UPDATE CASCADE,
   FOREIGN KEY (cod_posto) REFERENCES posti(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE farmaci (
   id INT AUTO_INCREMENT PRIMARY KEY,
   denominazione VARCHAR(100) NOT NULL UNIQUE,
   descrizione VARCHAR(500),
   qta INT NOT NULL DEFAULT 0,
   CHECK (qta >= 0)
);

CREATE TABLE prescrizioni (
   id INT AUTO_INCREMENT PRIMARY KEY,
   posologia VARCHAR(500) NOT NULL,
   qta INT NOT NULL,
   qta_ritirata INT NOT NULL DEFAULT 0,
   cod_visita INT NOT NULL,
   cod_farmaco INT NOT NULL,
   FOREIGN KEY (cod_visita) REFERENCES visite(id) ON DELETE RESTRICT ON UPDATE CASCADE,
   FOREIGN KEY (cod_farmaco) REFERENCES farmaci(id) ON DELETE RESTRICT ON UPDATE CASCADE,
   CHECK (qta > 0 AND qta_ritirata <= qta)
);

CREATE TABLE visite (
   id INT AUTO_INCREMENT PRIMARY KEY,
   orario DATETIME NOT NULL,
   pressione FLOAT,
   temperatura FLOAT,
   saturazione FLOAT,
   battito FLOAT,
   note VARCHAR(500),
   cod_ricovero INT NOT NULL,
   cod_medico INT NOT NULL,
   FOREIGN KEY (cod_ricovero) REFERENCES ricoveri(id) ON DELETE RESTRICT ON UPDATE CASCADE,
   FOREIGN KEY (cod_medico) REFERENCES medici(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE INDEX medicinali ON farmaci(denominazione);
