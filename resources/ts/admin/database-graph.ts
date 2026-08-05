/**
 * Database graph entry point.
 *
 * This bundle is only ever requested by the admin 데이터베이스 page, and
 * the renderer itself (three.js plus the force layout) is pulled in with
 * a dynamic import so the WebGL payload lives in its own chunk and is
 * fetched only once the page's container is actually on screen.
 */
const boot = (): void => {
    const root = document.querySelector<HTMLElement>('[data-database-graph]');

    if (!root || root.dataset.dbgMounted === 'true') {
        return;
    }

    root.dataset.dbgMounted = 'true';

    void import('./SchemaGraphView')
        .then(({ SchemaGraphView }) => new SchemaGraphView(root))
        .catch((error: unknown) => {
            const fallback = root.querySelector<HTMLElement>('[data-dbg-status]');

            if (fallback) {
                fallback.textContent = '3D 그래프를 불러오지 못했습니다.';
            }

            console.error('database graph failed to load', error);
        });
};

document.addEventListener('livewire:navigated', boot);

boot();
