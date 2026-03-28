// Función para inicializar tooltips
$(document).ready(function() {
    // Inicializar tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Inicializar popovers
    $('[data-bs-toggle="popover"]').popover();
    
    // Confirmación antes de acciones importantes
    $('.confirm-action').on('click', function(e) {
        if (!confirm($(this).data('confirm') || '¿Está seguro de realizar esta acción?')) {
            e.preventDefault();
        }
    });
    
    // Auto-ocultar mensajes de alerta después de 5 segundos
    setTimeout(function() {
        $('.alert').fadeTo(500, 0).slideUp(500, function() {
            $(this).remove(); 
        });
    }, 5000);
    
    // Manejar búsquedas en tiempo real
    $('.live-search').on('keyup', function() {
        const search = $(this).val().toLowerCase();
        const target = $(this).data('target');
        
        $(target).each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(search));
        });
    });
});

// Función para cargar contenido dinámico
function loadContent(url, target) {
    $(target).html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>');
    
    $.get(url, function(data) {
        $(target).html(data);
    }).fail(function() {
        $(target).html('<div class="alert alert-danger">Error al cargar el contenido</div>');
    });
}

// Función para formatear números como moneda
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Función para manejar formularios con AJAX
function submitFormAjax(form, successCallback, errorCallback) {
    const formData = new FormData(form);
    
    $.ajax({
        url: $(form).attr('action'),
        type: $(form).attr('method'),
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (typeof successCallback === 'function') {
                successCallback(response);
            }
        },
        error: function(xhr) {
            if (typeof errorCallback === 'function') {
                errorCallback(xhr);
            } else {
                alert('Error al procesar la solicitud');
            }
        }
    });
}