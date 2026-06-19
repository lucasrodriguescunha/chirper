document.addEventListener('click', (e) => {
    const toggle = e.target.closest('[data-comment-edit-toggle]');
    const cancel = e.target.closest('[data-comment-edit-cancel]');
    if (!toggle && !cancel) return;

    const wrapper = e.target.closest('[data-comment]');
    if (!wrapper) return;

    const body = wrapper.querySelector('[data-comment-body]');
    const form = wrapper.querySelector('[data-comment-edit-form]');
    if (!body || !form) return;

    const editing = !form.classList.contains('hidden');
    if (toggle && !editing) {
        form.classList.remove('hidden');
        body.classList.add('hidden');
        const ta = form.querySelector('textarea');
        if (ta) {
            ta.focus();
            ta.setSelectionRange(ta.value.length, ta.value.length);
        }
    } else {
        form.classList.add('hidden');
        body.classList.remove('hidden');
    }
});
