document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.reaction-button').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();

            const btn = e.currentTarget;
            const chirpId = btn.dataset.chirpId;
            const type = btn.dataset.type;

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
                    const allButtons = document.querySelectorAll(
                        `.reaction-button[data-chirp-id="${chirpId}"]`
                    );

                    const isActive = btn.classList.contains('text-red-600');

                    allButtons.forEach(b => b.classList.remove('text-red-600'));

                    if (!isActive) {
                        btn.classList.add('text-red-600');
                    }
                }

            } catch (err) {
                console.error(err);
            }
        });
    });
});
