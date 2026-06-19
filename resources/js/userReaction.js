document.addEventListener('DOMContentLoaded', () => {

    // ── Reações em Chirps ──────────────────────────────────────
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
                    body: JSON.stringify({ type })
                });

                if (response.ok) {
                    const data = await response.json();

                    const allButtons = document.querySelectorAll(
                        `.reaction-button[data-chirp-id="${chirpId}"]`
                    );

                    allButtons.forEach(b => b.classList.remove('text-red-600'));

                    if (data.currentUserReaction) {
                        const activeBtn = document.querySelector(
                            `.reaction-button[data-chirp-id="${chirpId}"][data-type="${data.currentUserReaction}"]`
                        );
                        activeBtn?.classList.add('text-red-600');
                    }

                    allButtons.forEach(b => {
                        const span = b.querySelector('span');
                        if (b.dataset.type === 'like') span.textContent = data.likes;
                        if (b.dataset.type === 'dislike') span.textContent = data.dislikes;
                    });
                }
            } catch (err) {
                console.error(err);
            }
        });
    });

    // ── Reações em Comments ────────────────────────────────────
    document.querySelectorAll('.comment-reaction-button').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();

            const btn = e.currentTarget;
            const commentId = btn.dataset.commentId;
            const type = btn.dataset.type;

            try {
                const response = await fetch(`/comments/${commentId}/reaction`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    },
                    body: JSON.stringify({ type })
                });

                if (response.ok) {
                    const data = await response.json();

                    const allButtons = document.querySelectorAll(
                        `.comment-reaction-button[data-comment-id="${commentId}"]`
                    );

                    allButtons.forEach(b => b.classList.remove('text-red-600'));

                    if (data.currentUserReaction) {
                        const activeBtn = document.querySelector(
                            `.comment-reaction-button[data-comment-id="${commentId}"][data-type="${data.currentUserReaction}"]`
                        );
                        activeBtn?.classList.add('text-red-600');
                    }

                    allButtons.forEach(b => {
                        const span = b.querySelector('span');
                        if (b.dataset.type === 'like') span.textContent = data.likes;
                        if (b.dataset.type === 'dislike') span.textContent = data.dislikes;
                    });
                }
            } catch (err) {
                console.error(err);
            }
        });
    });

});
