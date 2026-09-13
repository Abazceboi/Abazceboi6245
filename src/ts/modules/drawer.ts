/**
 * INNOVATIONX — Mobile Navigation Drawer TypeScript Module
 */

export class MobileDrawer {
    private hamburger: HTMLElement | null;
    private drawer: HTMLElement | null;
    private backdrop: HTMLElement | null;
    private drawerLinks: NodeListOf<HTMLElement>;

    constructor() {
        this.hamburger = document.getElementById('hamburger');
        this.drawer = document.getElementById('mobileDrawer');
        this.backdrop = document.querySelector('.drawer-backdrop');
        this.drawerLinks = document.querySelectorAll('.mobile-drawer a, .mobile-drawer button');

        this.init();
    }

    public init(): void {
        if (this.hamburger) {
            this.hamburger.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggle();
            });
        }

        if (this.backdrop) {
            this.backdrop.addEventListener('click', () => this.close());
        }

        this.drawerLinks.forEach(link => {
            link.addEventListener('click', () => this.close());
        });

        // Click outside to auto-hide
        document.addEventListener('click', (e: MouseEvent) => {
            const target = e.target as HTMLElement;
            if (this.drawer && this.drawer.classList.contains('open')) {
                if (!this.drawer.contains(target) && (!this.hamburger || !this.hamburger.contains(target))) {
                    this.close();
                }
            }
        });

        // ESC Key to auto-hide
        document.addEventListener('keydown', (e: KeyboardEvent) => {
            if (e.key === 'Escape' && this.drawer && this.drawer.classList.contains('open')) {
                this.close();
            }
        });
    }

    public open(): void {
        if (this.drawer) this.drawer.classList.add('open');
        if (this.backdrop) this.backdrop.classList.add('open');
        if (this.hamburger) this.hamburger.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    public close(): void {
        if (this.drawer) this.drawer.classList.remove('open');
        if (this.backdrop) this.backdrop.classList.remove('open');
        if (this.hamburger) this.hamburger.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    public toggle(): void {
        if (this.drawer && this.drawer.classList.contains('open')) {
            this.close();
        } else {
            this.open();
        }
    }
}
