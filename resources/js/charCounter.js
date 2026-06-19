function init(el) {
    if (el.dataset.counterReady) return;
    el.dataset.counterReady = '1';

    const max = parseInt(el.getAttribute('maxlength'), 10);
    if (!max) return;

    const counter = document.createElement('div');
    counter.className = 'label justify-end';
    const span = document.createElement('span');
    span.className = 'label-text-alt text-base-content/60';
    counter.appendChild(span);

    const host = el.closest('.form-control') || el.parentElement;
    host.appendChild(counter);

    const update = () => {
        const len = el.value.length;
        span.textContent = `${len}/${max}`;
        const ratio = len / max;
        span.classList.toggle('text-warning', ratio >= 0.8 && ratio < 1);
        span.classList.toggle('text-error', ratio >= 1);
        counter.classList.toggle('hidden', len === 0);
    };

    el.addEventListener('input', update);
    update();
}

function scan(root) {
    root.querySelectorAll('[data-counter]').forEach(init);
}

document.addEventListener('DOMContentLoaded', () => scan(document));
