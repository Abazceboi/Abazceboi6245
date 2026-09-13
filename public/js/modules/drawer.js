/**
 * INNOVATIONX — Mobile Navigation Drawer TypeScript Module
 */
export class MobileDrawer {
    constructor() {
        this.hamburger = document.getElementById('hamburger');
        this.drawer = document.getElementById('mobileDrawer');
        this.backdrop = document.querySelector('.drawer-backdrop');
        this.drawerLinks = document.querySelectorAll('.mobile-drawer a, .mobile-drawer button');
        this.init();
    }
    init() {
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
        document.addEventListener('click', (e) => {
            const target = e.target;
            if (this.drawer && this.drawer.classList.contains('open')) {
                if (!this.drawer.contains(target) && (!this.hamburger || !this.hamburger.contains(target))) {
                    this.close();
                }
            }
        });
        // ESC Key to auto-hide
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.drawer && this.drawer.classList.contains('open')) {
                this.close();
            }
        });
    }
    open() {
        if (this.drawer)
            this.drawer.classList.add('open');
        if (this.backdrop)
            this.backdrop.classList.add('open');
        if (this.hamburger)
            this.hamburger.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }
    close() {
        if (this.drawer)
            this.drawer.classList.remove('open');
        if (this.backdrop)
            this.backdrop.classList.remove('open');
        if (this.hamburger)
            this.hamburger.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }
    toggle() {
        if (this.drawer && this.drawer.classList.contains('open')) {
            this.close();
        }
        else {
            this.open();
        }
    }
}
