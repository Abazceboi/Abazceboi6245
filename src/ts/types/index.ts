/**
 * INNOVATIONX — Core TypeScript Type Definitions
 */

export interface ForecastState {
    dailyTasks: number;
    dailyReferrals: number;
    taskRatePoints: number;
    referralRateCash: number;
    membershipFee: number;
}

export interface WheelSlice {
    label: string;
    value: number;
    type: 'POINTS' | 'CASH' | 'SPIN';
    color: string;
}

export interface JobberItem {
    id: string;
    title: string;
    category: 'mining' | 'web3' | 'web2' | 'affiliate';
    referralCode?: string;
    estEarnings: string;
    downloadUrl?: string;
}

export interface VendorItem {
    id: string;
    name: string;
    location: string;
    rating: number;
    codesSold: string;
    phone: string;
    avatarColor: string;
}

export interface PayoutToastData {
    name: string;
    bank: string;
    amount: string;
}

export interface VerifyCodeResponse {
    success: boolean;
    message: string;
    amount?: number;
    code?: string;
}
