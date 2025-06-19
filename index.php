<?php

$server = "localhost";
$user = "root";
$pass = "";
$db = "bdwhatsnext";

$conexion = new mysqli($server, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

if (empty($busqueda)) {
    echo "No se ha especificado ningún término de búsqueda.";
    exit;
}

$busqueda = $conexion->real_escape_string($busqueda);

$juegoBuscado = "
SELECT v.NombreGame, v.Descripción, AVG(r.Rating) AS puntuacion_media
FROM videojuegos v
LEFT JOIN rating r ON v.NombreGame = r.NombreGame
WHERE v.NombreGame LIKE '%$busqueda%'
GROUP BY v.NombreGame, v.Descripción
ORDER BY puntuacion_media DESC";

$resultado = $conexion->query($juegoBuscado);
?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="description" content="Buscador">
    <meta name="author" content="Ian Seijas">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Buscador de videojuegos</title>
</head>
<body>
    <section>
        <div class="buscador">
            <form method="get" id="buscador_games" action="busqueda_game.php">
                <fieldset>
                    <input type="text" id="buscador-input" name="buscar" placeholder="¿Qué estas buscando?..." />
                    <input class="boton-busqueda" type="submit" value="Buscar" />
                    <i class="icono-buscador"></i>
                </fieldset>
            </form>
        </div>
    </section>
    <main>
        <h1>Resultado para "<?php echo htmlspecialchars($busqueda); ?>"</h1>

        <div class="lista_juegos">
            <?php
            if ($resultado && $resultado->num_rows > 0) {
                while ($juego = $resultado->fetch_assoc()) { ?>
                <div class="juego-tarjeta">
		<div class="juego-cabecera">
                    <h3>
			<a href="pagina_juego.php?nombre=<?php echo urlencode($juego['NombreGame']); ?>">
				<?php echo htmlspecialchars($juego['NombreGame']); ?>
			</a>
			</h3>
                    <div class="estrellas">
                        <?php
                        $rating = $juego['puntuacion_media'];
                        if ($rating === null) {
                            echo "¡Sé el primero en puntuarlo!";
                        } else {
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= round($rating)) {
                                    echo '<i class="bi bi-star-fill"></i>';
                                } else {
                                    echo '<i class="bi bi-star"></i>';
                                }
                            }
                            echo " (" . round($rating, 1) . "/5)";
                        }
                        ?>
                    </div>
		</div>
                    <p class="descripcion"><?php echo substr($juego['Descripción'], 0, 100) . "..."; ?></p>
                </div>
            <?php
                }
            } else {
                echo "<p>¡Vaya! No se encontraron juegos que coincidan con tu búsqueda. ¡Puedes agregarlo tú mismo si quieres!</p>";
            }
            ?>
        </div>
    </main>
</body>
        </html>
<?php $conexion->close(); ?>
