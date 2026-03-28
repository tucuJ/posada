// Funciones para el módulo de productos
document.addEventListener('DOMContentLoaded', function() {
    // Confirmar eliminación de producto
    document.querySelectorAll('.btn-eliminar').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productoId = this.getAttribute('data-id');
            const productoNombre = this.getAttribute('data-nombre');
            
            if (confirm(`¿Está seguro de eliminar el producto "${productoNombre}"?`)) {
                window.location.href = `eliminar.php?id=${productoId}`;
            }
        });
    });

    // Buscador de productos
    const buscador = document.getElementById('buscadorProductos');
    if (buscador) {
        buscador.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.producto-row').forEach(row => {
                const nombre = row.getAttribute('data-nombre').toLowerCase();
                const codigo = row.getAttribute('data-codigo').toLowerCase();
                
                if (nombre.includes(searchTerm) || codigo.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Validación de formulario
    const formProducto = document.getElementById('formProducto');
    if (formProducto) {
        formProducto.addEventListener('submit', function(e) {
            const precioVenta = parseFloat(document.getElementById('precioVenta').value);
            const precioCompra = parseFloat(document.getElementById('precioCompra').value);
            
            if (precioVenta <= precioCompra) {
                e.preventDefault();
                alert('El precio de venta debe ser mayor que el precio de compra');
                return false;
            }
            
            const stock = parseInt(document.getElementById('stock').value);
            if (stock < 0) {
                e.preventDefault();
                alert('El stock no puede ser negativo');
                return false;
            }
        });
    }
});