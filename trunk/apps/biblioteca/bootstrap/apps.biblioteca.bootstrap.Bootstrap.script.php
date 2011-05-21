<?php

YuppLoader::load('biblioteca.model', 'Libro');
YuppLoader::load('biblioteca.model', 'Autor');

// ===============================================================
// Creacion de libros

$libro1 = new Libro(
  array(
	"titulo" => "El ingenioso hidalgo don Quixote de la Mancha",
	"genero" => "prosa narrativa",
	"fecha" => "1605-01-01",
	"idioma" => "es",
	"numeroPaginas" => 223
  )
);

$libro2 = new Libro(
  array(
	"titulo" => "Harry Potter y la piedra filosofal",
	"genero" => "novela",
	"fecha" => "1997-06-30",
	"idioma" => "en",
	"numeroPaginas" => 187
  )
);

$libro3 = new Libro(
  array(
	"titulo" => "El código Da Vinci",
	"genero" => "novela",
	"fecha" => "2003-01-01",
	"idioma" => "en",
	"numeroPaginas" => 160
  )
);

// ===============================================================
// Creacion de autores

$autor1 = new Autor(
  array(
	"nombre" => "Miguel de Cervantes Saavedra",
	"fechaNacimiento" => "1547-09-29"
  )
);

$autor2 = new Autor(
  array(
	"nombre" => "J. K. Rowling",
	"fechaNacimiento" => "1547-09-29"
  )
);

$autor3 = new Autor(
  array(
	"nombre" => "Dan Brown",
	"fechaNacimiento" => "1547-09-29"
  )
);



// Asociacion de libros a autores
$libro1->setAutor( $autor1 );
$libro1->addToCoautores( $autor2 );
$libro1->addToCoautores( $autor3 );

print_r( $libro1 );

if (!$libro1->save()) print_r( $libro1->getErrors() );


$libro2->setAutor( $autor2 );
if (!$libro2->save()) print_r( $libro2->getErrors() );

$libro3->setAutor( $autor3 );
if (!$libro3->save()) print_r( $libro3->getErrors() );

?>