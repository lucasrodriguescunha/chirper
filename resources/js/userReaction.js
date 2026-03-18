document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.reaction-button').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();

            const chirpId = button.dataset.chirpId;
            const type = button.dataset.type;

            try {
                const response = await fetch(`/chirps/${chirpId}/reaction`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    },
                    body: JSON.stringify({type})
                });

                if (response.ok) {
                    const buttons = document.querySelectorAll(
                        `.reaction-button[data-chirp-id="${chirpId}"]`
                    );

                    const isActive = button.classList.contains('text-red-600');

                    buttons.forEach(btn => btn.classList.remove('text-red-600'));

                    if (!isActive) {
                        button.classList.add('text-red-600');
                    }
                }

            } catch (err) {
                console.error(err);
            }
        });
    });
});
