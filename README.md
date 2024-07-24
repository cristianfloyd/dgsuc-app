# Pasos del proceso':'

1. Subir el archivo txt de relaciones laborales activas
2. Subir el archivo txt exportade desde mapuche sicoss
3. Importar el archivo de relaciones laborales activas en la tabla de relaciones laborales activas
4. Importar el archivo de mapuche sicoss en la tabla de mapuche sicoss
5.  
    1. Ejecutar la consulta sql de comparacion entre las tablas, y extraer los cuils que no estan en relaciones activas.
    2. Insertar los cuils para alta en la tabla de cuils temporales.
6. Ejecutar la consulta sql almacenada que pobla la tabla mapuche mi simplificacion.
7. Obtener diferencia entre cuils de mapuche mi simplificacion y cuils para rel activas.
8. exportar tabla resultado en un archivo txt.
