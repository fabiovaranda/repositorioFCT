create database rep;
use rep;

create table TiposDeUtilizadores(
ID int primary key auto_increment,
Tipo varchar (13) not null);

create table Envios(
ID int primary key auto_increment, 
DataCriacao datetime not null,
NomeRelatorio varchar(100) not null,
TipoFicheiroRelatorio varchar(100) not null,
TamanhoFicheiroRelatorio varchar(100) not null,
ConteudoRelatorio mediumblob not null,
NomeApresentacao varchar(100) not null,
TipoFicheiroApresentacao varchar(100) not null,
TamanhoFicheiroApresentacao varchar(100) not null,
ConteudoApresentacao longblob not null,
AnoLetivo varchar(100) not null);

create table Utilizadores(
ID int primary key auto_increment,
Email varchar (100) not null,
PNome varchar (50) not null,
UNome varchar (50) not null,
Curso varchar (50) not null,
Ciclo varchar (50) not null,
Password varchar (70) not null,
DataCriacao datetime not null,
IDTipo int not null,
foreign key (IDTipo) references TiposDeUtilizadores (ID));

create table EnviosUtilizadores(
ID int primary key auto_increment,
IDUtilizador int not null,
IDEnvio int not null,
foreign key (IDUtilizador) references Utilizadores (ID),
foreign key (IDEnvio) references Envios (ID));

INSERT INTO TiposDeUtilizadores(Tipo) VALUES ('Normal');
INSERT INTO TiposDeUtilizadores(Tipo) VALUES ('Administrador');

INSERT INTO Utilizadores(Email, PNome, UNome, Curso, Ciclo, Password, DataCriacao, IDTipo) VALUES ('admin@default.com', 'Administrador', 'Por Defeito', '', '', '25d55ad283aa400af464c76d713c07ad', NOW(), '2');

/*Administrador por defeito
email: admin@default.com
password: 12345678
*/