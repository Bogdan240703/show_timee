import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const url = new URL(window.location.href);
        const hasSearch = url.searchParams.has('search');

        if (hasSearch) {
            // Use requestAnimationFrame to ensure DOM is ready
            requestAnimationFrame(() => {
                const section = document.getElementById('festival-section');
                if (section) {
                    section.scrollIntoView({behavior: 'smooth'});
                }
            });
        }
    }
}
