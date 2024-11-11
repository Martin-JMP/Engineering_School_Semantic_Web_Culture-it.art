        // Sélectionner tous les liens de navigation
        const navLinks = document.querySelectorAll('.nav-link');

        // Fonction pour appliquer la classe 'active' en fonction du LocalStorage
        function setActiveLinkFromStorage() {
            const activeLink = localStorage.getItem('activeLink');
            if (activeLink) {
                navLinks.forEach(link => {
                    if (link.dataset.name === activeLink) {
                        link.classList.add('active');
                    }
                });
            }
        }

        // Initialiser la classe active lors du chargement de la page
        setActiveLinkFromStorage();

        // Ajouter un événement de clic à chaque lien
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Retirer la classe active de tous les liens
                navLinks.forEach(link => link.classList.remove('active'));

                // Ajouter la classe active au lien cliqué
                this.classList.add('active');

                // Sauvegarder le lien actif dans le LocalStorage
                localStorage.setItem('activeLink', this.dataset.name);
            });
        });