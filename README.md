LA COMANDA 🍽 Aplicación Slim Framework 4 PHP + MySQL 🚀
==================================================

## Descripción del Proyecto 🎯

Esta API está diseñada para gestionar las operaciones diarias de un restaurante con cuatro sectores bien diferenciados: barra de tragos y vinos, barra de cervezas artesanales, cocina, y candy bar. La aplicación maneja la rotación de empleados, el seguimiento de pedidos, la gestión de estados de mesas y la recopilación de encuestas de satisfacción de los clientes.

## Requerimientos de la Aplicación 📋

### Sectores del Restaurante 🍴

1. **Barra de tragos y vinos** 🍸: Acceso exclusivo de los Bartenders.
2. **Barra de cervezas artesanales** 🍺: Acceso exclusivo de los Cerveceros.
3. **Cocina** 🍲: Acceso exclusivo de los Cocineros.
4. **Candy Bar** 🍭: Acceso exclusivo de los Cocineros.

### Gestión de Empleados 👨‍🍳👩‍🍳

El restaurante cuenta con empleados clasificados en las siguientes categorías:

- Bartender
- Cerveceros
- Cocineros
- Mozos
- Socios (encargados de supervisión y pagos)

### Operativa Principal 📊

1. **Gestión de Pedidos** 📝:
   - Los mozos toman pedidos y asignan un código único alfanumérico (de 5 caracteres) a cada cliente para identificar su pedido.
   - Los pedidos se distribuyen entre los empleados correspondientes según el tipo de producto solicitado (vino, cerveza, comida).
   - Los empleados cambian el estado del pedido a "en preparación" y agregan un tiempo estimado de finalización.
   - Una vez listo, el estado del pedido se actualiza a "listo para servir".

2. **Supervisión de Pedidos** 🔍:
   - Los socios pueden ver en todo momento el estado de todos los pedidos.

3. **Gestión de Mesas** 🪑:
   - Las mesas tienen un código de identificación único (de 5 caracteres).
   - Los estados de las mesas son:
     - "Con cliente esperando pedido" 🕒
     - "Con cliente comiendo" 🍽
     - "Con cliente pagando" 💳
     - "Cerrada" 🚪 (este estado solo puede ser cambiado por los socios)

4. **Encuestas de Satisfacción** 📝:
   - Al terminar de comer, los clientes pueden completar una encuesta calificando:
     - La mesa
     - El restaurante 
     - El mozo
     - El cocinero
   - Las puntuaciones van de 1 a 10 y se permite un breve texto de hasta 66 caracteres describiendo la experiencia.

## Endpoints Principales 🌐

### Pedidos 📦

- **Crear Pedido** 🆕: Permite a los mozos registrar un nuevo pedido.
- **Actualizar Estado del Pedido** 🔄: Empleados pueden cambiar el estado del pedido.
- **Obtener Estado del Pedido** 📅: Los clientes pueden consultar el tiempo restante para su pedido.

### Mesas 🪑

- **Actualizar Estado de la Mesa** 🔄: Cambiar el estado de la mesa según la situación.
- **Obtener Estado de la Mesa** 📅: Consultar el estado actual de una mesa.

### Encuestas 📊

- **Crear Encuesta** 🆕: Los clientes pueden completar una encuesta de satisfacción.
- **Obtener Mejores/Peores Mesas** 📈📉: Consultar las mesas con las mejores y peores puntuaciones.

## Contribuciones 🤝

Las contribuciones son bienvenidas. Por favor, crea un fork del repositorio y envía un pull request con tus mejoras.

## Licencia 📄

Este proyecto está bajo la Licencia MIT. Consulta el archivo LICENSE para más detalles.
