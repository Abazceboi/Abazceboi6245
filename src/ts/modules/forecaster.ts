/**
 * INNOVATIONX — Revenue Forecaster TypeScript Module
 */

import { ForecastState } from '../types/index.js';

export class RevenueForecaster {
    private card: HTMLElement | null;
    private tasksSlider: HTMLInputElement | null;
    private refsSlider: HTMLInputElement | null;

    private taskValDisplay: HTMLElement | null;
    private refValDisplay: HTMLElement | null;

    private dailyTasksEl: HTMLElement | null;
    private dailyCashEl: HTMLElement | null;
    private dailyTotalEl: HTMLElement | null;

    private weeklyTasksEl: HTMLElement | null;
    private weeklyCashEl: HTMLElement | null;
    private weeklyTotalEl: HTMLElement | null;

    private monthlyTasksEl: HTMLElement | null;
    private monthlyCashEl: HTMLElement | null;
    private monthlyTotalEl: HTMLElement | null;

    private state: ForecastState;

    constructor() {
        this.card = document.getElementById('calcCard');
        this.tasksSlider = document.getElementById('calcTasks') as HTMLInputElement;
        this.refsSlider = document.getElementById('calcRefs') as HTMLInputElement;

        this.taskValDisplay = document.getElementById('calcTaskVal');
        this.refValDisplay = document.getElementById('calcRefVal');

        this.dailyTasksEl = document.getElementById('calcDailyTasks');
        this.dailyCashEl = document.getElementById('calcDailyCash');
        this.dailyTotalEl = document.getElementById('calcDailyTotal');

        this.weeklyTasksEl = document.getElementById('calcWeeklyTasks');
        this.weeklyCashEl = document.getElementById('calcWeeklyCash');
        this.weeklyTotalEl = document.getElementById('calcWeeklyTotal');

        this.monthlyTasksEl = document.getElementById('calcMonthlyTasks');
        this.monthlyCashEl = document.getElementById('calcMonthlyCash');
        this.monthlyTotalEl = document.getElementById('calcMonthlyTotal');

        const taskRate = this.card ? parseInt(this.card.dataset.taskRate || '150', 10) : 150;
        const refRate = this.card ? parseInt(this.card.dataset.referralRate || '250', 10) : 250;
        const fee = this.card ? parseInt(this.card.dataset.regFee || '500', 10) : 500;

        this.state = {
            dailyTasks: this.tasksSlider ? parseInt(this.tasksSlider.value, 10) : 10,
            dailyReferrals: this.refsSlider ? parseInt(this.refsSlider.value, 10) : 2,
            taskRatePoints: taskRate,
            referralRateCash: refRate,
            membershipFee: fee
        };

        this.init();
    }

    public init(): void {
        if (!this.tasksSlider || !this.refsSlider) return;

        this.tasksSlider.addEventListener('input', () => {
            this.state.dailyTasks = parseInt(this.tasksSlider?.value || '10', 10);
            this.calculate();
        });

        this.refsSlider.addEventListener('input', () => {
            this.state.dailyReferrals = parseInt(this.refsSlider?.value || '2', 10);
            this.calculate();
        });

        this.calculate();
    }

    private formatNumber(num: number): string {
        return num.toLocaleString('en-US');
    }

    private formatShort(num: number): string {
        if (num >= 1000) {
            return (num / 1000).toFixed(num % 1000 === 0 ? 0 : 1) + 'k';
        }
        return num.toString();
    }

    public calculate(): void {
        const { dailyTasks, dailyReferrals, taskRatePoints, referralRateCash } = this.state;

        // Daily
        const dailyPoints = dailyTasks * taskRatePoints;
        const dailyCash = dailyReferrals * referralRateCash;

        // Weekly (7 Days)
        const weeklyPoints = dailyPoints * 7;
        const weeklyCash = dailyCash * 7;

        // Monthly (30 Days)
        const monthlyPoints = dailyPoints * 30;
        const monthlyCash = dailyCash * 30;

        // Update Slider Labels
        if (this.taskValDisplay) {
            this.taskValDisplay.textContent = `${dailyTasks} Tasks (${this.formatNumber(dailyPoints)} PTS)`;
        }
        if (this.refValDisplay) {
            this.refValDisplay.textContent = `${dailyReferrals} Referrals (₦${this.formatNumber(dailyCash)})`;
        }

        // Update Daily Outputs
        if (this.dailyTasksEl) this.dailyTasksEl.textContent = `${this.formatNumber(dailyPoints)} PTS`;
        if (this.dailyCashEl) this.dailyCashEl.textContent = `₦${this.formatNumber(dailyCash)}`;
        if (this.dailyTotalEl) this.dailyTotalEl.textContent = `${this.formatNumber(dailyPoints)} PTS + ₦${this.formatNumber(dailyCash)}`;

        // Update Weekly Outputs
        if (this.weeklyTasksEl) this.weeklyTasksEl.textContent = `${this.formatNumber(weeklyPoints)} PTS`;
        if (this.weeklyCashEl) this.weeklyCashEl.textContent = `₦${this.formatNumber(weeklyCash)}`;
        if (this.weeklyTotalEl) this.weeklyTotalEl.textContent = `${this.formatShort(weeklyPoints)} PTS + ₦${this.formatNumber(weeklyCash)}`;

        // Update Monthly Outputs
        if (this.monthlyTasksEl) this.monthlyTasksEl.textContent = `${this.formatNumber(monthlyPoints)} PTS`;
        if (this.monthlyCashEl) this.monthlyCashEl.textContent = `₦${this.formatNumber(monthlyCash)}`;
        if (this.monthlyTotalEl) this.monthlyTotalEl.textContent = `${this.formatShort(monthlyPoints)} PTS + ₦${this.formatNumber(monthlyCash)}`;
    }
}
