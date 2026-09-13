/**
 * INNOVATIONX — Verified Vendors Live Search TypeScript Module
 */

export class VendorsSearchManager {
    private searchInput: HTMLInputElement | null;
    private vendorCards: NodeListOf<HTMLElement>;

    constructor() {
        this.searchInput = document.getElementById('vendorSearchInput') as HTMLInputElement;
        this.vendorCards = document.querySelectorAll('.vendor-card');
        this.init();
    }

    public init(): void {
        if (!this.searchInput || this.vendorCards.length === 0) return;

        this.searchInput.addEventListener('input', () => {
            const query = this.searchInput?.value.trim().toLowerCase() || '';

            this.vendorCards.forEach(card => {
                const name = card.querySelector('.vendor-name')?.textContent?.toLowerCase() || '';
                const location = card.querySelector('.vendor-location')?.textContent?.toLowerCase() || '';

                if (query === '' || name.includes(query) || location.includes(query)) {
                    card.style.display = 'flex';
                    card.style.animation = 'fadeIn 0.3s ease';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
}
