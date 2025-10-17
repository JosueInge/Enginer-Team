// Cambiar todas las referencias de 'password' a 'contraseña'
$stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, contraseña = ? WHERE id = ?");