import './bootstrap';
import 'bootstrap';
import Choices from "choices.js";
import "choices.js/public/assets/styles/choices.min.css";

document.addEventListener('DOMContentLoaded', function () {
    // Eliminar fotos
    const deleteButtons = document.querySelectorAll('.delete-photo');

    deleteButtons.forEach(button => {

        button.addEventListener('click', function (e) {
            e.preventDefault(); // Prevenir cualquier comportamiento por defecto

            if (confirm('¿Estás seguro de eliminar esta foto?')) {

                const photo = this.dataset.photo; // Obtener el nombre de la foto
                const url = this.dataset.url; // Obtener la URL desde el atributo data-url
                const card = this.closest('.col-md-4'); // Encontrar la tarjeta correspondiente

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ photo: photo })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) { // Suponiendo que la respuesta tenga un campo 'success'
                            card.remove(); // Eliminar la tarjeta

                            // Eliminar el contenedor si ya no quedan fotos
                            const photoContainer = document.getElementById('photo-container');
                            if (photoContainer && photoContainer.querySelectorAll('.col-md-4').length === 0) {
                                photoContainer.remove();
                            }

                            alert('Foto eliminada correctamente');
                        } else {
                            alert('Error al eliminar la foto');
                        }
                    })
                    .catch(error => {
                        alert('Error al eliminar la foto');
                    });
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const element = document.querySelector('#products_used');
    new Choices(element, {
        removeItemButton: true, // Botón para eliminar opciones
        placeholder: true,
        placeholderValue: "Selecciona productos"
    });
});
