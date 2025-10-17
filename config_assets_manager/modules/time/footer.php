<?php $module_time = false;
if ($module_time): ?>
    <style>
        .clock {
            z-index: 999;
            position: absolute;
            bottom: 0px;
            padding: 0px 0px 10px 10px;
        }
    </style>
    <script>
        // Exécuter la fonction aprés que la page est complètement chargée
        document.addEventListener('DOMContentLoaded', () => {
            let clock = createClock()
            updateDateTime(clock); // Mise à jour initiale
            let start = setInterval(updateDateTime, 1000, clock); // Mise Ã  jour toutes les secondes
        });

        // Fonction qui met à jour la date et l'heure
        function createClock() {
            let clock = document.createElement('div');
            clock.id = 'clock'
            clock.className = 'clock'
            clock.style.zIndex = '999'
            clock.style.position = 'fixed'
            clock.style.left = '0'
            clock.style.bottom = '0'
            clock.style.padding = '0 0 10px 10px'
            document.body.appendChild(clock);
            return clock;
        }
        // Fonction qui met à jour la date et l'heure
        function updateDateTime(clock) {
            const now = new Date();
            const formattedDate = now.toLocaleDateString('fr-FR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
            const formattedTime = now.toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
            clock.textContent = `${formattedDate}, ${formattedTime}`;
        }
    </script>

<?php endif; ?>