/**
 * INNOVATIONX — Jobbers Unit & Referral Copy TypeScript Module
 */

export class JobbersManager {
    private tabButtons: NodeListOf<HTMLButtonElement>;
    private jobCards: NodeListOf<HTMLElement>;

    constructor() {
        this.tabButtons = document.querySelectorAll('.job-tab-btn');
        this.jobCards = document.querySelectorAll('.job-card');
        this.init();
    }

    public init(): void {
        this.initCategoryFilters();
        this.initClipboardButtons();
    }

    private initCategoryFilters(): void {
        if (this.tabButtons.length === 0 || this.jobCards.length === 0) return;

        this.tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                this.tabButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const selectedCat = btn.getAttribute('data-jobcat') || 'all';

                this.jobCards.forEach(card => {
                    const cardCat = card.getAttribute('data-category');
                    if (selectedCat === 'all' || cardCat === selectedCat) {
                        card.style.display = 'flex';
                        card.style.animation = 'fadeIn 0.35s ease';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }

    private initClipboardButtons(): void {
        const copyButtons = document.querySelectorAll('.btn-copy-ref');
        copyButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const codeWrap = btn.closest('.job-ref-code-wrap');
                const codeEl = codeWrap?.querySelector('.job-ref-code');
                const code = codeEl?.textContent?.trim() || '';

                if (code) {
                    navigator.clipboard.writeText(code).then(() => {
                        const originalText = btn.innerHTML;
                        btn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> <span>Copied!</span>`;
                        (btn as HTMLElement).style.background = 'rgba(16, 185, 129, 0.3)';
                        (btn as HTMLElement).style.borderColor = '#10B981';

                        setTimeout(() => {
                            btn.innerHTML = originalText;
                            (btn as HTMLElement).style.background = '';
                            (btn as HTMLElement).style.borderColor = '';
                        }, 2500);
                    }).catch(err => {
                        console.error('Clipboard copy failed: ', err);
                    });
                }
            });
        });
    }
}
