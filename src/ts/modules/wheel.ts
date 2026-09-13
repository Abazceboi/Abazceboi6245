/**
 * INNOVATIONX — Lucky Spin Wheel TypeScript Module
 */

import { WheelSlice } from '../types/index.js';

export class LuckySpinWheel {
    private canvas: HTMLCanvasElement | null;
    private ctx: CanvasRenderingContext2D | null;
    private spinBtn: HTMLElement | null;
    private centerBtn: HTMLElement | null;
    private resultToast: HTMLElement | null;

    private slices: WheelSlice[];
    private isSpinning: boolean;
    private currentRotation: number;

    constructor() {
        this.canvas = document.getElementById('wheelCanvas') as HTMLCanvasElement;
        this.ctx = this.canvas ? this.canvas.getContext('2d') : null;
        this.spinBtn = document.getElementById('spinBtn');
        this.centerBtn = document.getElementById('spinCenterBtn');
        this.resultToast = document.getElementById('spinResultToast');

        this.isSpinning = false;
        this.currentRotation = 0;

        this.slices = [
            { label: '500 PTS', value: 500, type: 'POINTS', color: '#9333EA' },
            { label: '₦200 Cash', value: 200, type: 'CASH', color: '#C59B4B' },
            { label: '1,000 PTS', value: 1000, type: 'POINTS', color: '#3B82F6' },
            { label: 'Free Spin', value: 0, type: 'SPIN', color: '#10B981' },
            { label: '250 PTS', value: 250, type: 'POINTS', color: '#7C3AED' },
            { label: '₦500 Cash', value: 500, type: 'CASH', color: '#C59B4B' },
            { label: '750 PTS', value: 750, type: 'POINTS', color: '#2563EB' },
            { label: '100 PTS', value: 100, type: 'POINTS', color: '#6B21A8' }
        ];

        this.init();
    }

    public init(): void {
        if (!this.canvas || !this.ctx) return;
        this.drawWheel();

        const triggerSpin = (e: Event) => {
            e.preventDefault();
            this.handleSpinAttempt();
        };

        if (this.spinBtn) this.spinBtn.addEventListener('click', triggerSpin);
        if (this.centerBtn) this.centerBtn.addEventListener('click', triggerSpin);
    }

    private drawWheel(): void {
        if (!this.canvas || !this.ctx) return;
        const width = this.canvas.width;
        const height = this.canvas.height;
        const center = width / 2;
        const radius = center - 10;
        const numSlices = this.slices.length;
        const sliceAngle = (2 * Math.PI) / numSlices;

        this.ctx.clearRect(0, 0, width, height);

        for (let i = 0; i < numSlices; i++) {
            const angle = this.currentRotation + i * sliceAngle;
            const slice = this.slices[i];

            this.ctx.beginPath();
            this.ctx.moveTo(center, center);
            this.ctx.arc(center, center, radius, angle, angle + sliceAngle);
            this.ctx.closePath();

            this.ctx.fillStyle = slice.color;
            this.ctx.fill();

            this.ctx.lineWidth = 2;
            this.ctx.strokeStyle = 'rgba(255, 255, 255, 0.3)';
            this.ctx.stroke();

            // Label Text
            this.ctx.save();
            this.ctx.translate(center, center);
            this.ctx.rotate(angle + sliceAngle / 2);
            this.ctx.textAlign = 'right';
            this.ctx.fillStyle = '#FFFFFF';
            this.ctx.font = 'bold 14px "Plus Jakarta Sans", sans-serif';
            this.ctx.shadowColor = 'rgba(0, 0, 0, 0.6)';
            this.ctx.shadowBlur = 4;
            this.ctx.fillText(slice.label, radius - 24, 5);
            this.ctx.restore();
        }

        // Outer Ring Accent
        this.ctx.beginPath();
        this.ctx.arc(center, center, radius, 0, 2 * Math.PI);
        this.ctx.lineWidth = 5;
        this.ctx.strokeStyle = 'rgba(255, 255, 255, 0.4)';
        this.ctx.stroke();
    }

    private handleSpinAttempt(): void {
        if (this.isSpinning) return;
        // Direct to registration to claim free spin
        window.location.href = 'register.php';
    }
}
